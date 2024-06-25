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

use DBD\Entity\Common\EntityException;
use ReflectionException;

/**
 * Trait MapperTrait
 *
 * @package DBD\Entity
 */
trait MapperTrait
{
    /**
     * @return Complex[]
     */
    public function getComplex(): array
    {
        return MapperCache::me()->complex[$this->name()] ?? [];
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return MapperCache::me()->columns[$this->name()] ?? [];
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array
    {
        return MapperCache::me()->constraints[$this->name()];
    }

    /**
     * @return Embedded[]
     */
    public function getEmbedded(): array
    {
        return MapperCache::me()->embedded[$this->name()] ?? [];
    }

    /**
     * @param string $originName
     *
     * @return Column
     * @throws EntityException
     */
    public function findColumnByOriginName(string $originName): Column
    {
        foreach ($this->getColumns() as $column) {
            if ($column->name == $originName) {
                return $column;
            }
        }

        throw new EntityException(sprintf("Can't find origin column '%s' in %s", $originName, get_class($this)));
    }

    /**
     * @param Column $column
     * @return int|string
     * @throws EntityException
     */
    public function getVarNameByColumn(Column $column): int|string
    {
        foreach ($this->getOriginFieldNames() as $varName => $originFieldName) {
            if ($originFieldName == $column->name) {
                return $varName;
            }
        }

        throw new EntityException(sprintf("Seems column '%s' does not belong to this mapper", $column->name));
    }

    /**
     * @return Column[] that is associative array where key is property name
     */
    public function getPrimaryKey(): array {
        $keys = [];

        foreach ($this->getColumns() as $columnName => $column) {
            if (isset($column->{Column::KEY}) and $column->{Column::KEY} === true) {
                $keys[$columnName] = $column;
            }
        }

        return $keys;
    }

    /**
     * @return array<string, string>
     */
    public function getOriginFieldNames(): array
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->originFieldNames[$thisName])) {
            MapperCache::me()->originFieldNames[$thisName] = [];

            foreach ($this->getColumns() as $columnName => $column) {
                MapperCache::me()->originFieldNames[$thisName][$columnName] = $column->name;
            }
        }

        return MapperCache::me()->originFieldNames[$thisName];
    }

    /**
     * @return Table
     * @throws ReflectionException
     * @throws EntityException
     */
    public function getTable(): Table
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->tables[$thisName])) {
            $table = new Table();

            $table->name = $this->getTableName();
            $table->scheme = $this->getScheme();
            $table->columns = $this->getColumns();
            $table->constraints = $this->getConstraints();
            $table->keys = $this->getPrimaryKey();
            $table->annotation = $this->getAnnotation();

            MapperCache::me()->table[$thisName] = $table;
        }

        return MapperCache::me()->table[$thisName];
    }
}
