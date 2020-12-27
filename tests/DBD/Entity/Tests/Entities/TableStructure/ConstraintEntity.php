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

namespace DBD\Entity\Tests\Entities\TableStructure;

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class ConstraintEntity extends Entity implements SyntheticEntity, StrictlyFilledEntity
{
    /**
     * @var string $foreignColumnName
     * @see ConstraintEntityMap::$foreignColumnName
     */
    public $foreignColumnName;
    /**
     * @var string $foreignTableName
     * @see ConstraintEntityMap::$foreignTableName
     */
    public $foreignTableName;
    /**
     * @var string $foreignTableScheme
     * @see ConstraintEntityMap::$foreignTableScheme
     */
    public $foreignTableScheme;
    /**
     * @var string $localColumnName
     * @see ConstraintEntityMap::$localColumnName
     */
    public $localColumnName;
    /**
     * @var string $name
     * @see ConstraintEntityMap::$name
     */
    public $name;
}

class ConstraintEntityMap extends Mapper
{
    public const ANNOTATION = "";
    /** @var Column */
    public $foreignColumnName = [
        Column::NAME => "constraint_foreign_column_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $foreignTableName = [
        Column::NAME => "constraint_foreign_table_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $foreignTableScheme = [
        Column::NAME => "constraint_foreign_table_schema",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $localColumnName = [
        Column::NAME => "constraint_local_column_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $name = [
        Column::NAME => "constraint_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
}
