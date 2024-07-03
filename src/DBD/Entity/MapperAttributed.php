<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2024] [Nick Ispandiarov <nikolay.i@maddevs.io>]                      *
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

use DBD\Entity\Common\EntityException;
use DBD\Entity\Interfaces\EntityMapper;
use ReflectionClass;
use ReflectionException;

/**
 * Class MapperAttributed
 *
 * @package DBD\Entity
 */
class MapperAttributed implements EntityMapper
{
    use MapperTrait;

    /**
     * @throws ReflectionException|EntityException
     */
    public function __construct(
        protected string $entityClass
    )
    {
        $this->getAllVariables();
    }

    public function name(): string
    {
        return substr($this->entityClass, strrpos($this->entityClass, '\\') + 1) . 'Map';
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return string
     * @throws ReflectionException
     * @throws EntityException
     */
    public function getScheme(): string
    {
        $reflection = new ReflectionClass($this->entityClass);

        $attributes = $reflection->getAttributes(EntityTable::class);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance()->scheme;
        }
        // @codeCoverageIgnoreStart
        throw new EntityException('Missing attribute scheme for ' . $this->entityClass);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     * @throws EntityException
     * @throws ReflectionException
     */
    public function getTableName(): string
    {
        $reflection = new ReflectionClass($this->entityClass);

        $attributes = $reflection->getAttributes(EntityTable::class);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance()->name;
        }
        // @codeCoverageIgnoreStart
        throw new EntityException('Missing attribute name for ' . $this->entityClass);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     * @throws EntityException
     * @throws ReflectionException
     */
    public function getAnnotation(): string
    {
        $reflection = new ReflectionClass($this->entityClass);

        $attributes = $reflection->getAttributes(EntityTable::class);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance()->annotation;
        }
        // @codeCoverageIgnoreStart
        throw new EntityException('Missing attribute annotation for ' . $this->entityClass);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return MapperVariables
     * @throws ReflectionException
     * @throws EntityException
     */
    public function getAllVariables(): MapperVariables
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->allVariables[$thisName])) {
            $reflectionClass = new ReflectionClass($this->entityClass);

            $properties = $reflectionClass->getProperties();

            /**
             * @var Constraint[] $constraints
             * @var Embedded[] $embedded
             * @var Complex[] $complexes
             * @var Column[] $columns
             */
            $constraints = $embedded = $complexes = $columns = [];

            foreach ($properties as $property) {
                $attributes = $property->getAttributes();

                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === Column::class || is_subclass_of($attribute->getName(), Column::class)) {
                        $columns[$property->getName()] = $attribute->newInstance();
                    } else if ($attribute->getName() === Constraint::class || is_subclass_of($attribute->getName(), Constraint::class)) {
                        $constraints[$property->getName()] = $attribute->newInstance();
                    } else if ($attribute->getName() === Embedded::class || is_subclass_of($attribute->getName(), Embedded::class)) {
                        $embedded[$property->getName()] = $attribute->newInstance();
                    } else if ($attribute->getName() === Complex::class || is_subclass_of($attribute->getName(), Complex::class)) {
                        $complexes[$property->getName()] = $attribute->newInstance();
                    }
                }
            }

            $this->processComplexes($complexes);
            $this->processEmbedded($embedded);
            $this->processColumns($columns);
            $this->processConstraints($constraints, $columns, $embedded, $complexes);
        }

        return MapperCache::me()->allVariables[$thisName];
    }

    /**
     * @param Complex[] $complexes
     * @return void
     */
    private function processComplexes(array $complexes): void
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->complex[$thisName])) {
            MapperCache::me()->complex[$thisName] = [];
        }

        foreach ($complexes as $complexName => $complex) {
            $this->$complexName = $complex;
            MapperCache::me()->complex[$thisName][$complexName] = $this->$complexName;
        }
    }

    /**
     * @param Embedded[] $embedded
     * @return void
     */
    private function processEmbedded(array $embedded): void
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->embedded[$thisName])) {
            MapperCache::me()->embedded[$thisName] = [];
        }

        foreach ($embedded as $embeddedName => $embeddedValue) {
            $this->$embeddedName = $embeddedValue;

            MapperCache::me()->embedded[$thisName][$embeddedName] = $this->$embeddedName;
        }
    }

    /**
     * @param Column[] $columns
     * @return void
     */
    private function processColumns(array $columns): void
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->columns[$thisName])) {
            MapperCache::me()->columns[$thisName] = [];
        }

        foreach ($columns as $columnName => $column) {
            MapperCache::me()->columns[$thisName][$columnName] = $column;

            $this->$columnName = $column;
        }
    }

    /**
     * @param Constraint[] $constraints
     * @param Column[] $columns
     * @param Embedded[] $embedded
     * @param Complex[] $complexes
     * @return void
     * @throws EntityException
     * @throws ReflectionException
     */
    private function processConstraints(array $constraints, array $columns, array $embedded, array $complexes): void
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->constraints[$thisName])) {
            MapperCache::me()->constraints[$thisName] = [];
        }

        $entityClass = $this->getEntityClass();

        foreach ($constraints as $constraintName => $constraint) {
            if ($entityClass !== View::class && is_string($constraint->localColumn)) {
                $constraint->localColumn = $this->findColumnByOriginName($constraint->localColumn);
            }

            MapperCache::me()->constraints[$thisName][$constraintName] = $constraint;
        }

        MapperCache::me()->allVariables[$thisName] = new MapperVariables($columns, $constraints, $embedded, $complexes);

        foreach (MapperCache::me()->constraints[$thisName] as $constraintName => $constraint) {
            $constraint->localTable = $this->getTable();

            $this->$constraintName = $constraint;
        }
    }
}
