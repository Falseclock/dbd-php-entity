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

namespace DBD\Entity;

use DBD\Common\Singleton;
use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\MapperException;
use DBD\Entity\Common\Utils;
use Exception;
use ReflectionException;

/**
 * Class Mapper
 *
 * @package DBD\Entity
 */
abstract class Mapper extends Singleton
{
    const ANNOTATION = "abstract";
    const POSTFIX = "Map";

    /**
     * Used for quick access to the mapper without instantiating it and have only one instance
     *
     * @return Mapper|static
     * @throws Common\EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public static function me(): Mapper
    {
        return self::instantiate();
    }

    /**
     * @param bool $callEnforcer
     * @return Mapper|static
     * @throws Common\EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    private static function instantiate(bool $callEnforcer = true): Mapper
    {
        /** @var static $self */
        $self = parent::me();

        $class = get_class($self);

        if (!isset(MapperCache::me()->fullyInstantiated[$class])) {

            // Check we set ANNOTATION properly in Mapper instance
            if ($callEnforcer)
                Enforcer::__add(__CLASS__, $class);

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
     * @throws MapperException
     */
    public function getAllVariables(): MapperVariables
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->allVariables[$thisName])) {

            /**
             * All available variables
             * Columns and Complex are always PUBLIC
             * Constraints and Embedded are always PROTECTED
             */
            $allVars = get_object_vars($this);
            $publicVars = Utils::getObjectVars($this);
            $protectedVars = Utils::arrayDiff($allVars, $publicVars);

            $constraints = [];
            $embedded = [];
            $complex = [];
            $columns = [];

            foreach ($publicVars as $varName => $varValue) {
                $this->checkProperty($varValue, $varName);
                $columns[$varName] = $varValue;
            }

            foreach ($protectedVars as $varName => $varValue) {
                $this->checkProperty($varValue, $varName);

                if (isset($varValue[Constraint::LOCAL_COLUMN])) {
                    $constraints[$varName] = $varValue;
                } else {
                    if (isset($varValue[Embedded::NAME]))
                        $embedded[$varName] = $varValue;
                    else if (isset($varValue[Complex::TYPE]))
                        $complex[$varName] = $varValue;
                }
            }

            /** ----------------------COMPLEX------------------------ */
            foreach ($complex as $complexName => $complexValue) {
                $this->$complexName = new Complex($complexValue);
                MapperCache::me()->complex[$thisName][$complexName] = $this->$complexName;
            }
            // У нас может не быть комплексов
            if (!isset(MapperCache::me()->complex[$thisName]))
                MapperCache::me()->complex[$thisName] = [];

            /** ----------------------EMBEDDED------------------------ */
            foreach ($embedded as $embeddedName => $embeddedValue) {
                $this->$embeddedName = new Embedded($embeddedValue);
                MapperCache::me()->embedded[$thisName][$embeddedName] = $this->$embeddedName;
            }
            // У нас может не быть эмбедов
            if (!isset(MapperCache::me()->embedded[$thisName]))
                MapperCache::me()->embedded[$thisName] = [];

            /** ----------------------COLUMNS------------------------ */
            if (!isset(MapperCache::me()->columns[$thisName])) {
                foreach ($columns as $columnName => $columnValue) {
                    $this->$columnName = new Column($columnValue);
                    MapperCache::me()->baseColumns[$thisName][$columnName] = $this->$columnName;
                    MapperCache::me()->columns[$thisName][$columnName] = $this->$columnName;
                }
            }
            // У нас может не быть колонок
            if (!isset(MapperCache::me()->columns[$thisName]))
                MapperCache::me()->columns[$thisName] = [];

            if (!isset(MapperCache::me()->baseColumns[$thisName]))
                MapperCache::me()->baseColumns[$thisName] = [];

            /** ----------------------CONSTRAINTS------------------------ */
            $temporaryConstraints = [];
            if (!isset(MapperCache::me()->constraints[$thisName])) {
                $entityClass = get_parent_class($this->getEntityClass());

                foreach ($constraints as $constraintName => $constraintValue) {
                    $temporaryConstraint = new Constraint($constraintValue);
                    // we asking provide self instance while table still not ready
                    //$temporaryConstraint->localTable = $this->getTable();

                    // If we use View - we do not always need to define constraint fields
                    if ($entityClass !== View::class)
                        $temporaryConstraint->localColumn = $this->findColumnByOriginName($temporaryConstraint->localColumn);

                    $temporaryConstraints[$constraintName] = $temporaryConstraint;
                }
            }

            // У нас может не быть констрейнтов
            if (!isset(MapperCache::me()->constraints[$thisName]))
                MapperCache::me()->constraints[$thisName] = [];

            MapperCache::me()->allVariables[$thisName] = new MapperVariables($columns, $constraints, $embedded, $complex);

            // Now fill constraint as map is ready
            foreach ($temporaryConstraints as $constraintName => $temporaryConstraint) {
                $temporaryConstraint->localTable = $this->getTable();
                $this->$constraintName = $temporaryConstraint;
                MapperCache::me()->constraints[$thisName][$constraintName] = $this->$constraintName;
            }
        }

        return MapperCache::me()->allVariables[$thisName];
    }

    /**
     * Get simple Mapper class name without namespace
     */
    public function name()
    {
        $name = get_class($this);

        return substr($name, strrpos($name, '\\') + 1);
    }

    /**
     * @param $varValue
     * @param string $varName
     * @throws MapperException
     */
    private function checkProperty($varValue, string $varName): void
    {
        if (is_null($varValue))
            throw new MapperException(sprintf("property '\$%s' of %s is null", $varName, get_class($this)));

        if (!is_array($varValue))
            throw new MapperException(sprintf("property '\$%s' of %s is not array", $varName, get_class($this)));

        if (count($varValue) == 0)
            throw new MapperException(sprintf("property '\$%s' of %s does not have definitions", $varName, get_class($this)));
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

    /**
     * @param string $originName
     *
     * @return Column
     * @throws MapperException
     */
    public function findColumnByOriginName(string $originName): Column
    {
        foreach ($this->getColumns() as $column) {
            if ($column->name == $originName) {
                return $column;
            }
        }
        throw new MapperException(sprintf("Can't find origin column '%s' in %s", $originName, get_class($this)));
    }

    /**
     * @return Column[]
     * @throws Exception
     */
    public function getColumns(): array
    {
        return MapperCache::me()->columns[$this->name()];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getTable()
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->table[$thisName])) {
            $parentClass = $this->getEntityClass();
            $table = new Table();
            /** @var Entity $parentClass */
            $table->name = $parentClass::TABLE;
            $table->scheme = $parentClass::SCHEME;
            $table->columns = $this->getBaseColumns();
            $table->constraints = $this->getConstraints();
            $table->keys = $this->getPrimaryKey();
            $table->annotation = $this->getAnnotation();

            MapperCache::me()->table[$thisName] = $table;
        }

        return MapperCache::me()->table[$thisName];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getBaseColumns()
    {
        return MapperCache::me()->baseColumns[$this->name()];
    }

    /**
     * @return Constraint[]
     * @throws Exception
     */
    public function getConstraints(): array
    {
        return MapperCache::me()->constraints[$this->name()];
    }

    /**
     * @return Column[] that is associative array where key is property name
     * @throws Exception
     */
    public function getPrimaryKey(): array
    {
        $keys = [];
        foreach (MapperCache::me()->columns[$this->name()] as $columnName => $column) {
            if (isset($column->key) and $column->key === true)
                $keys[$columnName] = $column;
        }

        return $keys;
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
     * @throws Common\EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public static function meWithoutEnforcer(): Mapper
    {
        return self::instantiate(false);
    }

    /**
     * Special getter to access protected and private properties
     * @param $name
     *
     * @return mixed
     * @throws MapperException
     */
    public function __get($name)
    {
        if (!property_exists($this, $name))
            throw new MapperException(sprintf("Can't find property '\$%s' of '%s'", $name, get_class($this)));

        return $this->$name;
    }

    /**
     * @return Complex[]
     * @throws Exception
     */
    public function getComplex(): array
    {
        return MapperCache::me()->complex[$this->name()];
    }

    /**
     * @return Embedded[]
     * @throws Exception
     */
    public function getEmbedded(): array
    {
        return MapperCache::me()->embedded[$this->name()];
    }

    /**
     * @param Column $column
     *
     * @return mixed
     * @throws MapperException
     */
    public function getVarNameByColumn(Column $column)
    {
        foreach ($this->getOriginFieldNames() as $varName => $originFieldName) {
            if ($originFieldName == $column->name)
                return $varName;
        }

        throw new MapperException(sprintf("Seems column '%s' does not belong to this mapper", $column->name));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getOriginFieldNames(): array
    {
        $thisName = $this->name();
        if (!isset(MapperCache::me()->originFieldNames[$thisName])) {
            if (count($this->getColumns()))
                foreach ($this->getColumns() as $columnName => $column)
                    MapperCache::me()->originFieldNames[$thisName][$columnName] = $column->name;
            else
                MapperCache::me()->originFieldNames[$thisName] = [];

        }

        return MapperCache::me()->originFieldNames[$thisName];
    }
}
