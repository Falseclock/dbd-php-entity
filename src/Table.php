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

use DBD\Common\DBDException;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Join\ManyToMany;
use DBD\Entity\Join\ManyToOne;
use DBD\Entity\Join\OneToMany;
use DBD\Entity\Join\OneToOne;
use Exception;
use ReflectionException;

/**
 * Class Table
 *
 * @package DBD\Entity
 */
class Table
{
    /** @var string $name */
    public $name;
    /** @var string $scheme */
    public $scheme;
    /** @var Column[] $columns */
    public $columns = [];
    /** @var Key[] $keys */
    public $keys = [];
    /** @var string $annotation */
    public $annotation;
    /** @var Constraint[] $constraints */
    public $constraints = [];

    /**
     * @param Table $table
     * @param Constraint $constraintValue
     *
     * @return Constraint
     * @throws Common\MapperException
     * @throws DBDException
     * @throws EntityException
     * @throws ReflectionException
     */
    private static function convertToConstraint(Table $table, Constraint $constraintValue): Constraint
    {
        $constraint = new Constraint();

        $constraintValue = (object)$constraintValue;

        /** @var Entity $foreignClass */
        $foreignClass = $constraintValue->class;

        $foreignClassMapInstance = $foreignClass::map();
        $foreignTable = $foreignClassMapInstance->getTable();

        if ($foreignTable !== null) {
            $constraint->foreignTable = $foreignTable;
            $constraint->foreignColumn = self::findColumnByOriginName($foreignTable, $constraintValue->foreignColumn);
        } else {
            $constraint->foreignTable = self::getFromMapper($foreignClass::map());
            $constraint->foreignColumn = self::findColumnByOriginName($constraint->foreignTable, $constraintValue->foreignColumn);
        }

        $constraint->localTable = $table;
        $constraint->localColumn = self::findColumnByOriginName($table, $constraintValue->localColumn);

        switch ($constraintValue->join) {
            case Join::MANY_TO_ONE:
                $constraint->join = new ManyToOne();
                break;
            case Join::MANY_TO_MANY:
                $constraint->join = new ManyToMany();
                break;
            case Join::ONE_TO_ONE:
                $constraint->join = new OneToOne();
                break;
            case Join::ONE_TO_MANY:
                $constraint->join = new OneToMany();
                break;
        }
        $constraint->class = $constraintValue->class;

        return $constraint;
    }

    /**
     * @param Table $table
     * @param string $columnOriginName
     *
     * @return Column
     * @throws EntityException
     */
    private static function findColumnByOriginName(Table $table, string $columnOriginName): Column
    {
        foreach ($table->columns as $column) {
            if ($column->name == $columnOriginName) {
                return $column;
            }
        }
        throw new EntityException("can't find column '{$columnOriginName}' in table '{$table->name}'. Looks like this column not described in Mapper class.");
    }

    /**
     * @param Mapper $mapper
     *
     * @return Table
     * @throws DBDException
     * @throws EntityException
     * @throws ReflectionException
     */
    public static function getFromMapper(Mapper $mapper)
    {
        $table = new Table();

        /** @var Entity $entityClass Getting class name from Mapper instance, which can be used as class instance */
        $entityClass = $mapper->getEntityClass();

        $table->name = $entityClass::TABLE;
        $table->scheme = $entityClass::SCHEME;

        self::convertVariables($table, $mapper);

        $table->annotation = $mapper->getAnnotation();
        $table->keys = self::getKeys($table);

        return $table;
    }

    /**
     * Converts vars to Column & Constraint
     *
     * @param Table $table
     * @param Mapper $mapper
     *
     * @throws EntityException
     * @throws DBDException
     * @throws ReflectionException
     * @throws Exception
     */
    private static function convertVariables(Table $table, Mapper $mapper): void
    {
        $variables = $mapper->getAllVariables();

        // Read all variables and convert to Column and Constraint
        foreach ($variables->columns as $columnName) {

            $columnValue = $mapper->$columnName;

            // This is fix for old annotation when we used only column name as variable; TODO: remove after migration
            if (is_string($columnValue)) {
                $table->columns[$columnName] = new Column($columnValue);
                continue;
            }
            // It should be array always? otherwise throw exception
            if (is_array($columnValue)) {
                $table->columns[$columnName] = self::convertToColumn($columnValue);
                continue;
            }

            if ($columnValue instanceof Column) {
                $table->columns[$columnName] = $columnValue;
                continue;
            }

            throw new EntityException("Unknown type of Mapper variable {$columnName} in {$mapper}");
        }
        /**
         * foreach ($variables->otherColumns as $otherColumnName) {
         *
         * $otherColumnValue = $mapper->$otherColumnName;
         *
         * // This is fix for old annotation when we used only column name as variable;
         * // TODO: remove after migration
         * if (is_string($otherColumnValue)) {
         * $table->otherColumns[$otherColumnName] = new Column($otherColumnValue);
         * continue;
         * }
         * // It should be array always? otherwise throw exception
         * if (is_array($otherColumnValue)) {
         * $table->otherColumns[$otherColumnName] = self::convertToColumn($otherColumnValue);
         * } else {
         * throw new EntityException("Unknown type of Mapper variable {$otherColumnName} in {$mapper}");
         * }
         * }
         */
        // now parse all constraints
        // All constraints should be processed after columns
        foreach ($variables->constraints as $constraintName) {

            /** @var Constraint $constraintValue */
            $constraintValue = $mapper->$constraintName;

            $table->constraints[] = self::convertToConstraint($table, $constraintValue);
        }
    }

    /**
     * @param $columnValue
     *
     * @return Column
     */
    private static function convertToColumn($columnValue): Column
    {
        /** @var Column $columnValue Yes, we are 100% column annotation */
        $column = new Column();

        foreach ($columnValue as $key => $value) {
            if ($key == Column::PRIMITIVE_TYPE)
                $column->$key = new Primitive($value);
            else
                $column->$key = $value;
        }

        return $column;
    }

    /**
     * @param Table $table
     *
     * @return array
     */
    private static function getKeys(Table $table)
    {
        $keys = [];
        foreach ($table->columns as $column) {
            if ($column->key === true) {
                $keys[] = new Key($column);
            }
        }

        return $keys;
    }
}
