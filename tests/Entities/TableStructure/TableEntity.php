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

use DBD\Common\DBDException;
use DBD\Common\PgUtils;
use DBD\Entity\Column;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Constraint;
use DBD\Entity\Embedded;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;
use ReflectionException;

class TableEntity extends Entity implements SyntheticEntity, StrictlyFilledEntity
{
    public $schema;
    public $name;
    public $type;
    public $comment;
    /** @var Column[] $columns */
    public $columns;
    /** @var Constraint[] $foreignKeys */
    public $foreignKeys;

    /**
     * @param string|null $constraints
     *
     * @throws EntityException
     * @throws ReflectionException
     */
    public function setForeignKeys(?string $constraints): void
    {
        $constraints = json_decode($constraints, true) ?? [];

        foreach ($constraints as &$constraintInitial) {
            $constraintInitial = new ConstraintEntity($constraintInitial);

            $constraint = new Constraint();
            $constraint->localColumn = $constraintInitial->localColumnName;
            $constraint->localTable = $this->name;
            $constraint->foreignColumn = $constraintInitial->foreignColumnName;
            $constraint->foreignTable = $constraintInitial->foreignTableName;
            $constraint->foreignScheme = $constraintInitial->foreignTableScheme;

            $constraintInitial = $constraint;
        }

        $this->foreignKeys = $constraints;
    }

    /**
     * @param string $columns
     *
     * @throws DBDException
     * @throws EntityException
     * @throws ReflectionException
     */
    public function setColumns(string $columns): void
    {
        $columns = json_decode($columns, true);
        foreach ($columns as &$columnInitial) {
            $columnInitial = new ColumnEntity($columnInitial);

            $column = new Column($columnInitial->name);

            $column->nullable = $columnInitial->isNullable;
            $column->defaultValue = $columnInitial->defaultValue;
            $column->annotation = $columnInitial->comment;
            $column->key = $columnInitial->isPrimary;
            $column->type = PgUtils::getPrimitive($columnInitial->udtType);
            $column->originType = $columnInitial->udtType;
            $column->maxLength = $columnInitial->characterLength;

            if (!is_null($columnInitial->numericPrecision))
                $column->precision = $columnInitial->numericPrecision;

            if (!is_null($columnInitial->numericScale))
                $column->scale = $columnInitial->numericScale;

            if (!is_null($columnInitial->datetimePrecision))
                $column->precision = $columnInitial->datetimePrecision;

            if (in_array($column->type->getValue(), [Primitive::Int16, Primitive::Int32(), Primitive::Int64])) {
                $column->scale = null;
                $column->precision = null;
            }

            $columnInitial = $column;
        }

        $this->columns = $columns;
    }
}

class TableEntityMap extends Mapper implements FullMapper
{
    public const ANNOTATION = "";
    /**
     * @var Column $schema
     */
    public $schema = [
        Column::NAME => "table_schema",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::ORIGIN_TYPE => "varchar"
    ];

    /**
     * @var Column $name
     */
    public $name = [
        Column::NAME => "table_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::ORIGIN_TYPE => "varchar"
    ];

    /**
     * @var Column $type
     */
    public $type = [
        Column::NAME => "table_type",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::ORIGIN_TYPE => "varchar"
    ];

    /**
     * @var Column $comment
     */
    public $comment = [
        Column::NAME => "table_comment",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::ORIGIN_TYPE => "varchar"
    ];

    /**
     * @var Column $columns
     */
    public $columns = [
        Column::NAME => "table_columns",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "json",
    ];

    /**
     * @var Embedded $foreignKeys
     */
    public $foreignKeys = [
        Column::NAME => "table_constraints",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "json",
    ];
}
