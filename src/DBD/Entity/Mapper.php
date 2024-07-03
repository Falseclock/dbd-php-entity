<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2020] [Nurlan Mukhanov <nurike@gmail.com>]                      *
 *                                                                              *
 *   Licensed under the Apache License, Version 2.0 (the "License");            *
 *   you may not use this file except in compliance with the License.           *
 *   You may obtain a copy of the License at                                    *
 *                                                                              *
 *       http://www.apache.org/licenses/LICENSE-2.0                             *
 *                                                                              *
 *   Unless required by applicable law or agreed to in writing, software        *
 *   distributed under the License is distributed on an "AS IS" BASIS,          *
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.   *
 *   See the License for the specific language governing permissions and        *
 *   limitations under the License.                                             *
 *                                                                              *
 ********************************************************************************/

declare(strict_types=1);

namespace DBD\Entity;

use DBD\Common\Instantiatable;
use DBD\Common\Singleton;
use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Common\Utils;
use DBD\Entity\Interfaces\EntityMapper;
use ReflectionException;

/**
 * Class Mapper
 * @todo Check child classes for methods
 * @todo check for private vars
 *
 * @package DBD\Entity
 */
abstract class Mapper extends Singleton implements EntityMapper
{
    use MapperTrait;

    const ANNOTATION = "abstract";
    const POSTFIX = "Map";

    /**
     * Used for quick access to the mapper without instantiating it and have only one instance
     *
     * @return Mapper|static
     * @throws EntityException|ReflectionException
     */
    public static function me(): Instantiatable
    {
        return self::instantiate();
    }

    /**
     * @param bool $callEnforcer
     * @return Mapper|static
     * @throws Common\EntityException
     * @throws EntityException
     * @throws ReflectionException
     */
    private static function instantiate(bool $callEnforcer = true): Mapper
    {
        /** @var static $self */
        $self = parent::me();

        $class = get_class($self);

        if (!isset(MapperCache::me()->fullyInstantiated[$class])) {

            // Check we set ANNOTATION properly in Mapper instance
            if ($callEnforcer) {
                Enforcer::__add(__CLASS__, $class);
            }
            $self->getAllVariables();

            MapperCache::me()->fullyInstantiated[$class] = true;
        }
        return $self;
    }

    /**
     * Read all public, private and protected variable names and their values.
     * Used when we need convert Mapper to Table instance
     *
     * @return MapperVariables
     * @throws EntityException
     * @throws ReflectionException
     */
    public function getAllVariables(): MapperVariables
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->allVariables[$thisName])) {

            /**
             * All available variables
             * Columns are always PUBLIC
             * Complex, Constraints and Embedded are always PROTECTED
             */
            $allVars = get_object_vars($this);
            $publicVars = Utils::getObjectVars($this);
            $protectedVars = Utils::arrayDiff($allVars, $publicVars);

            $constraints = $embedded = $complex = $columns = [];

            foreach ($publicVars as $varName => $varValue) {
                $this->checkProperty($varValue, $varName);
                $columns[$varName] = $varValue;
            }

            foreach ($protectedVars as $varName => $varValue) {
                $this->checkProperty($varValue, $varName);

                if (isset($varValue[Constraint::LOCAL_COLUMN])) {
                    $constraints[$varName] = $varValue;
                } else {
                    if (isset($varValue[Embedded::NAME])) {
                        $embedded[$varName] = $varValue;
                    } else if (isset($varValue[Complex::TYPE])) {
                        $complex[$varName] = $varValue;
                    }
                }
            }

            $this->processComplexes($complex);

            $this->processEmbedded($embedded);

            $this->processColumns($columns);

            $this->processConstraints($constraints, $columns, $embedded, $complex);

        }

        return MapperCache::me()->allVariables[$thisName];
    }

    /**
     * Get simple Mapper class name without namespace
     */
    public function name(): string
    {
        $name = get_class($this);

        return substr($name, strrpos($name, '\\') + 1);
    }

    /**
     * Returns Entity class name which uses this Mapper
     *
     * @return string
     */
    public function getEntityClass(): string
    {
        return substr(get_class($this), 0, strlen(self::POSTFIX) * -1);
    }

    public function getScheme(): string
    {
        return $this->getEntityClass()::SCHEME;
    }

    public function getTableName(): string
    {
        return $this->getEntityClass()::TABLE;
    }

    /**
     * @param $varValue
     * @param string $varName
     * @throws EntityException
     */
    private function checkProperty($varValue, string $varName): void
    {
        if (is_null($varValue)) {
            throw new EntityException(sprintf("property '\$%s' of %s is null", $varName, get_class($this)));
        }
        if (!is_array($varValue)) {
            throw new EntityException(sprintf("property '\$%s' of %s is not array", $varName, get_class($this)));
        }
        if (count($varValue) == 0) {
            throw new EntityException(sprintf("property '\$%s' of %s does not have definitions", $varName, get_class($this)));
        }
    }

    /**
     * @param array $complex
     * @throws EntityException
     */
    private function processComplexes(array $complex): void
    {
        $thisName = $this->name();

        /** ----------------------COMPLEX------------------------ */
        foreach ($complex as $complexName => $complexValue) {
            $this->$complexName = new Complex($complexValue);
            MapperCache::me()->complex[$thisName][$complexName] = $this->$complexName;
        }
        // У нас может не быть комплексов
        if (!isset(MapperCache::me()->complex[$thisName])) {
            MapperCache::me()->complex[$thisName] = [];
        }
    }

    /**
     * @param array $embedded
     * @throws EntityException
     */
    private function processEmbedded(array $embedded): void
    {
        $thisName = $this->name();
        /** ----------------------EMBEDDED------------------------ */
        foreach ($embedded as $embeddedName => $embeddedValue) {
            $this->$embeddedName = new Embedded($embeddedValue);
            MapperCache::me()->embedded[$thisName][$embeddedName] = $this->$embeddedName;
        }
        // У нас может не быть эмбедов
        if (!isset(MapperCache::me()->embedded[$thisName])) {
            MapperCache::me()->embedded[$thisName] = [];
        }
    }

    /**
     * @param array $columns
     * @throws EntityException
     */
    private function processColumns(array $columns): void
    {
        $thisName = $this->name();

        /** ----------------------COLUMNS------------------------ */
        if (!isset(MapperCache::me()->columns[$thisName])) {
            foreach ($columns as $columnName => $columnValue) {
                $this->$columnName = new Column($columnValue);
                MapperCache::me()->columns[$thisName][$columnName] = $this->$columnName;
            }
        }
        // У нас может не быть колонок
        if (!isset(MapperCache::me()->columns[$thisName])) {
            MapperCache::me()->columns[$thisName] = [];
        }
    }

    /**
     * @param array $constraints
     * @param array $columns
     * @param array $embedded
     * @param array $complex
     * @throws EntityException|ReflectionException
     */
    private function processConstraints(array $constraints, array $columns, array $embedded, array $complex): void
    {
        $thisName = $this->name();

        /** ----------------------CONSTRAINTS------------------------ */
        $temporaryConstraints = [];
        if (!isset(MapperCache::me()->constraints[$thisName])) {
            $entityClass = get_parent_class($this);

            foreach ($constraints as $constraintName => $constraintValue) {
                $temporaryConstraint = new Constraint($constraintValue);
                // we asking provide self instance while table still not ready
                //$temporaryConstraint->localTable = $this->getTable();

                // If we use View - we do not always need to define constraint fields
                if ($entityClass !== View::class && is_string($temporaryConstraint->localColumn)) {
                    $temporaryConstraint->localColumn = $this->findColumnByOriginName($temporaryConstraint->localColumn);
                }
                $temporaryConstraints[$constraintName] = $temporaryConstraint;
            }
        }

        // У нас может не быть ограничений
        if (!isset(MapperCache::me()->constraints[$thisName])) {
            MapperCache::me()->constraints[$thisName] = [];
        }
        MapperCache::me()->allVariables[$thisName] = new MapperVariables($columns, $constraints, $embedded, $complex);

        // Now fill constraint as map is ready
        foreach ($temporaryConstraints as $constraintName => $temporaryConstraint) {
            $temporaryConstraint->localTable = $this->getTable();
            $this->$constraintName = $temporaryConstraint;
            MapperCache::me()->constraints[$thisName][$constraintName] = $this->$constraintName;
        }
    }

    /**
     * Returns table comment
     *
     * @return string
     */
    public function getAnnotation(): string
    {
        return $this::ANNOTATION;
    }

    /**
     * @return Mapper|static
     * @throws EntityException
     * @throws ReflectionException
     */
    public static function meWithoutEnforcer(): Mapper
    {
        return self::instantiate(false);
    }

    /**
     * Special getter to access protected and private properties
     *
     * @param string $property
     * @return mixed
     * @throws EntityException
     */
    public function __get(string $property)
    {
        if (!property_exists($this, $property)) {
            throw new EntityException(sprintf("Can't find property '\$%s' of '%s'", $property, get_class($this)));
        }

        return $this->$property;
    }
}
