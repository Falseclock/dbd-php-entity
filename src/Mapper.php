<?php
/*************************************************************************************
 *   MIT License                                                                     *
 *                                                                                   *
 *   Copyright (C) 2020 by Nurlan Mukhanov <nurike@gmail.com>                        *
 *                                                                                   *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy    *
 *   of this software and associated documentation files (the "Software"), to deal   *
 *   in the Software without restriction, including without limitation the rights    *
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell       *
 *   copies of the Software, and to permit persons to whom the Software is           *
 *   furnished to do so, subject to the following conditions:                        *
 *                                                                                   *
 *   The above copyright notice and this permission notice shall be included in all  *
 *   copies or substantial portions of the Software.                                 *
 *                                                                                   *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR      *
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,        *
 *   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE    *
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER          *
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,   *
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE   *
 *   SOFTWARE.                                                                       *
 ************************************************************************************/

namespace DBD\Entity;

use DBD\Common\Singleton;
use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\MapperException;
use DBD\Entity\Common\Utils;
use InvalidArgumentException;
use ReflectionException;

/**
 * Название переменной в дочернем классе, которая должна быть если мы вызываем BaseHandler
 *
 * @property Column $id
 * @property Column $constant
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
    public static function me()
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
     * @throws Common\EntityException
     * @throws MapperException
     */
    public function getAllVariables()
    {
        $thisName = $this->name();

        if (!isset(MapperCache::me()->allVariables[$thisName])) {

            /**
             * All available variables
             * Columns and Complex are always PUBLIC
             * Constraints and Embedded are always PROTECTED
             */
            $allVars = get_object_vars($this);
            $publicVars = get_object_vars($this);
            $protectedVars = Utils::arrayDiff($allVars, $publicVars);

            $constraints = [];
            $otherColumns = [];
            $embedded = [];
            $complex = [];
            $columns = [];

            foreach ($publicVars as $varName => $varValue) {
                if (is_null($varValue))
                    throw new MapperException(sprintf("property '\$%s' of %s is null", $varName, get_class($this)));

                if (!is_array($varValue))
                    throw new MapperException(sprintf("property '\$%s' of %s is not array", $varName, get_class($this)));

                if (count($varValue) == 0)
                    throw new MapperException(sprintf("property '\$%s' of %s does not have definitions", $varName, get_class($this)));

                // Column::PRIMITIVE_TYPE is mandatory for Columns
                if (isset($varValue[Column::PRIMITIVE_TYPE]))
                    $columns[$varName] = $varValue;
                else
                    $otherColumns[$varName] = $varValue;
            }

            foreach ($protectedVars as $varName => $varValue) {
                if (is_array($varValue)) {
                    if (isset($varValue[Constraint::LOCAL_COLUMN])) {
                        $constraints[$varName] = $varValue;
                    } else {
                        if (isset($varValue[Embedded::DB_TYPE]))
                            $embedded[$varName] = $varValue;
                        else if (isset($varValue[Complex::TYPE]))
                            $complex[$varName] = $varValue;
                        else
                            $otherColumns[$varName] = $varValue;
                    }
                } else {
                    throw new MapperException(sprintf("variable '%s' of '%s' is type of %s", $varName, get_class($this), gettype($varValue)));
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
                foreach ($otherColumns as $columnName => $columnValue) {
                    $this->$columnName = new Column($columnValue);
                    MapperCache::me()->otherColumns[$thisName][$columnName] = $this->$columnName;
                    MapperCache::me()->columns[$thisName][$columnName] = $this->$columnName;
                }
            }
            // У нас может не быть колонок
            if (!isset(MapperCache::me()->columns[$thisName]))
                MapperCache::me()->columns[$thisName] = [];

            if (!isset(MapperCache::me()->otherColumns[$thisName]))
                MapperCache::me()->otherColumns[$thisName] = [];

            if (!isset(MapperCache::me()->baseColumns[$thisName]))
                MapperCache::me()->baseColumns[$thisName] = [];

            /** ----------------------CONSTRAINTS------------------------ */
            if (!isset(MapperCache::me()->constraints[$thisName])) {
                $entityClass = get_parent_class($this->getEntityClass());

                foreach ($constraints as $constraintName => $constraintValue) {
                    $temporaryConstraint = new ConstraintRaw($constraintValue);
                    $temporaryConstraint->localTable = $this->getTable();

                    // If we use View - we do not always need to define constraint fields
                    if ($entityClass !== View::class)
                        $temporaryConstraint->localColumn = $this->findColumnByOriginName($temporaryConstraint->localColumn);

                    $this->$constraintName = $temporaryConstraint;

                    MapperCache::me()->constraints[$thisName][$constraintName] = $this->$constraintName;
                }
            }
            // У нас может не быть констрейнтов
            if (!isset(MapperCache::me()->constraints[$thisName]))
                MapperCache::me()->constraints[$thisName] = [];

            MapperCache::me()->allVariables[$thisName] = new MapperVariables($columns, $constraints, $otherColumns, $embedded, $complex);
        }

        return MapperCache::me()->allVariables[$thisName];
    }

    private function name()
    {
        $name = get_class($this);

        return (substr($name, strrpos($name, '\\') + 1));
    }

    /**
     * Returns Entity class name which uses this Mapper
     *
     * @return string
     */
    public function getEntityClass()
    {
        return substr(get_class($this), 0, strlen(self::POSTFIX) * -1);
    }

    /**
     * @return mixed
     * @throws MapperException
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
            $table->otherColumns = $this->getOtherColumns();
            // FIXME:
            //$table->constraints = $this->getConstraints();
            //$table->keys = $this->getKeys();
            $table->annotation = $this->getAnnotation();

            MapperCache::me()->table[$thisName] = $table;
        }

        return MapperCache::me()->table[$thisName];
    }

    /**
     * @return mixed
     * @throws MapperException
     */
    public function getBaseColumns()
    {
        return MapperCache::me()->baseColumns[$this->name()];
    }

    /**
     * @return Column[]
     * @throws MapperException
     */
    public function getOtherColumns()
    {
        return MapperCache::me()->otherColumns[$this->name()];
    }

    /**
     * Returns table comment
     *
     * @return string
     */
    public function getAnnotation()
    {
        return $this::ANNOTATION;
    }

    /**
     * @param string $originName
     *
     * @return Column
     * @throws MapperException
     */
    public function findColumnByOriginName(string $originName)
    {
        foreach ($this->getColumns() as $column) {
            if ($column->name == $originName) {
                return $column;
            }
        }
        throw new MapperException(sprintf("Can't find origin column '%s' in %s. If it is reference column, map it as protected", $originName, get_class($this)));
    }

    /**
     * @return Column[]
     * @throws MapperException
     */
    public function getColumns()
    {
        return MapperCache::me()->columns[$this->name()];
    }

    /**
     * @return Mapper|static
     * @throws Common\EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public static function meWithoutEnforcer()
    {
        return self::instantiate(false);
    }

    /**
     * Special getter to access protected and private properties
     * Unfortunately abstract class doesn't have access to child class,
     * that is why we use Reflection.
     * TODO: set all to public with some markers to identify fields, constraints and complex
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {

        if (!property_exists($this, $name)) {
            throw new InvalidArgumentException(sprintf("Getting the field '%s' is not valid for '%s'", $name, get_class($this)));
        }

        return $this->$name;
    }

    /**
     * @return Complex[]
     * @throws MapperException
     */
    public function getComplex()
    {
        return MapperCache::me()->complex[$this->name()];
    }

    /**
     * @return Constraint[]
     * @throws MapperException
     */
    public function getConstraints()
    {
        return MapperCache::me()->constraints[$this->name()];
    }

    /**
     * @return Embedded[]
     * @throws MapperException
     */
    public function getEmbedded()
    {
        return MapperCache::me()->embedded[$this->name()];
    }

    /**
     * @return Column[]
     * @throws MapperException
     */
    public function getPrimaryKey()
    {
        $keys = [];
        foreach (MapperCache::me()->columns[$this->name()] as $columnName => $column) {
            if (isset($column->key) and $column->key == true)
                $keys[$columnName] = $column;
        }

        return $keys;
    }

    /**
     * @param Column $column
     *
     * @return mixed
     * @throws MapperException
     */
    public function getVarNameByColumn(Column $column)
    {
        foreach ($this->getColumnsDefinition() as $varName => $originFieldName) {
            if ($originFieldName == $column->name)
                return $varName;
        }

        return null;
    }

    /**
     * @return array
     * @throws MapperException
     */
    public function getColumnsDefinition()
    {
        $thisName = $this->name();
        if (!isset(MapperCache::me()->originFieldNames[$thisName])) {
            foreach ($this->getColumns() as $columnName => $column)
                MapperCache::me()->originFieldNames[$thisName][$columnName] = $column->name;
        }

        return MapperCache::me()->originFieldNames[$thisName];
    }
}
