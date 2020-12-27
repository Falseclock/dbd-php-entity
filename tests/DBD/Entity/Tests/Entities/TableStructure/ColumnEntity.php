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
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class ColumnEntity extends Entity implements SyntheticEntity, StrictlyFilledEntity
{
    /**
     * @var int $characterLength
     * @see ColumnEntityMap::$characterLength
     */
    public $characterLength;
    /**
     * @var string $comment
     * @see ColumnEntityMap::$comment
     */
    public $comment;
    /**
     * @var int $datetimePrecision
     * @see ColumnEntityMap::$datetimePrecision
     */
    public $datetimePrecision;
    /**
     * @var string $defaultValue
     * @see ColumnEntityMap::$defaultValue
     */
    public $defaultValue;
    /**
     * @var int $intervalPrecision
     * @see ColumnEntityMap::$intervalPrecision
     */
    public $intervalPrecision;
    /**
     * @var bool $isNullable
     * @see ColumnEntityMap::$isNullable
     */
    public $isNullable;
    /**
     * @var bool $isPrimary
     * @see ColumnEntityMap::$isPrimary
     */
    public $isPrimary;
    /**
     * @var string $name
     * @see ColumnEntityMap::$name
     */
    public $name;
    /**
     * @var int $numericPrecision
     * @see ColumnEntityMap::$numericPrecision
     */
    public $numericPrecision;
    /**
     * @var int $numericScale
     * @see ColumnEntityMap::$numericScale
     */
    public $numericScale;
    /**
     * @var int $position
     * @see ColumnEntityMap::$position
     */
    public $position;
    /**
     * @var string $type
     * @see ColumnEntityMap::$type
     */
    public $type;
    /**
     * @var string $udtType
     * @see ColumnEntityMap::$udtType
     */
    public $udtType;
}

class ColumnEntityMap extends Mapper implements FullMapper
{
    public const ANNOTATION = "";
    /** @var Column */
    public $characterLength = [
        Column::NAME => "column_character_length",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $comment = [
        Column::NAME => "column_comment",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "text",
    ];
    /** @var Column */
    public $datetimePrecision = [
        Column::NAME => "column_datetime_precision",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $defaultValue = [
        Column::NAME => "column_default_value",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $intervalPrecision = [
        Column::NAME => "column_interval_precision",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $isNullable = [
        Column::NAME => "column_is_nullable",
        Column::PRIMITIVE_TYPE => Primitive::Boolean,
        Column::ORIGIN_TYPE => "bool",
    ];
    /** @var Column */
    public $isPrimary = [
        Column::NAME => "column_is_primary",
        Column::PRIMITIVE_TYPE => Primitive::Boolean,
        Column::ORIGIN_TYPE => "bool",
    ];
    /** @var Column */
    public $name = [
        Column::NAME => "column_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $numericPrecision = [
        Column::NAME => "column_numeric_precision",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $numericScale = [
        Column::NAME => "column_numeric_scale",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $position = [
        Column::NAME => "column_position",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => "int4",
    ];
    /** @var Column */
    public $type = [
        Column::NAME => "column_type",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
    /** @var Column */
    public $udtType = [
        Column::NAME => "column_udt_type",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "varchar",
    ];
}
