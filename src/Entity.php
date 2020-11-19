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
use DBD\Entity\Common\EntityException;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\OnlyDeclaredPropertiesEntity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

/**
 * Class Entity
 *
 * @package DBD\Entity
 */
abstract class Entity
{
    const SCHEME = "abstract";
    const TABLE = "abstract";

    /**
     * Конструктор модели
     *
     * @param array|null $data
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     * @throws ReflectionException
     */
    public function __construct(array $data = null, int $maxLevels = 2, int $currentLevel = 0)
    {
        $calledClass = get_class($this);

        if (!$this instanceof SyntheticEntity)
            Enforcer::__add(__CLASS__, $calledClass);

        try {
            $map = self::map();
        } catch (Exception $e) {
            throw new EntityException(sprintf("Construction of %s failed, %s", $calledClass, $e->getMessage()));
        }

        if (!isset(EntityCache::$mapCache[$calledClass])) {

            $columnsDefinition = $map->getOriginFieldNames();

            EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP] = $columnsDefinition;
            EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE_MAP] = array_flip($columnsDefinition);

            /*            if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
                            foreach (get_object_vars($this) as $propertyName => $propertyDefaultValue) {
                                if (!array_key_exists($propertyName, $columnsDefinition))
                                    throw new EntityException(sprintf("FullEntity or StrictlyFilledEntity %s has unmapped property '%s'", $calledClass, $propertyName));
                            }
                        }*/

            // У нас может быть цепочка классов, где какой-то конечный уже не имеет интерфейса OnlyDeclaredPropertiesEntity
            // соответственно нам надо собрать все переменные всех дочерних классов, даже если они расширяют друг друга
            if ($this instanceof OnlyDeclaredPropertiesEntity)
                $this->collectDeclarationsOnly(new ReflectionObject($this), $calledClass);
        }

        if ($this instanceof OnlyDeclaredPropertiesEntity) {
            foreach (get_object_vars($this) as $varName => $varValue) {
                if (!isset(EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES][$varName])) {
                    unset($this->$varName);
                    EntityCache::$mapCache[$calledClass][EntityCache::UNSET_PROPERTIES][$varName] = true;
                }
            }
        }

        if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
            $checkAgainst = array_merge($map->getColumns(), $map->getComplex(), $map->getEmbedded(), $map->getConstraints());
            foreach (get_object_vars($this) as $propertyName => $propertyDefaultValue) {
                if (!array_key_exists($propertyName, $checkAgainst))
                    throw new EntityException(sprintf("Strict Entity %s has unmapped property '%s'", $calledClass, $propertyName));
            }
        }

        if (!isset($data))
            return;

        // Если мы определяем класс с интерфейсом OnlyDeclaredPropertiesEntity и экстендим его
        // то по сути мы не можем знать какие переменные классам нам обязательны к обработке.
        // Ладно еще если это 2 класса, а если цепочка?
        //if($this instanceof OnlyDeclaredPropertiesEntity and !$reflectionObject->isFinal())
        //	throw new EntityException("Class " . $reflectionObject->getParentClass()->getShortName() . " which implements OnlyDeclaredPropertiesEntity interface must be final");

        if ($currentLevel <= $maxLevels)
            $this->setModelData($data, $map, $maxLevels, $currentLevel);
    }

    /**
     * @return Singleton|Mapper|static
     * @throws EntityException
     * @throws ReflectionException
     */
    final public static function map()
    {
        $calledClass = get_called_class();

        /** @var Mapper $mapClass */
        $mapClass = $calledClass . Mapper::POSTFIX;

        if (!class_exists($mapClass, false))
            throw new EntityException(sprintf("Class %s does not have Map definition", $calledClass));

        $reflection = new ReflectionClass($calledClass);
        $interfaces = $reflection->getInterfaces();

        if (isset($interfaces[SyntheticEntity::class]))
            return $mapClass::meWithoutEnforcer();
        else
            return $mapClass::me();
    }

    /**
     * @param ReflectionClass $reflectionObject
     * @param string $calledClass
     * @param string|null $parentClass
     */
    private function collectDeclarationsOnly(ReflectionClass $reflectionObject, string $calledClass, string $parentClass = null): void
    {
        foreach ($reflectionObject->getProperties() as $property) {

            $declaringClass = $property->getDeclaringClass();

            if ($declaringClass->name == $calledClass || $declaringClass->name == $parentClass)
                EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES][$property->name] = true;
        }

        $parentClass = $reflectionObject->getParentClass();
        $parentInterfaces = $parentClass->getInterfaces();

        if (isset($parentInterfaces[OnlyDeclaredPropertiesEntity::class]))
            $this->collectDeclarationsOnly($parentClass, $calledClass, $parentClass->name);

        /** If we have defined declaredProperties key, we must exclude some keys from reverseMap and arrayMap */
        if (isset(EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES])) {
            foreach (EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP] as $propertyName => $fieldName) {
                if (!array_key_exists($propertyName, EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES])) {
                    unset(EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP][$propertyName]);
                    unset(EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE_MAP][$fieldName]);
                }
            }
        }
    }

    /**
     * @param array|null $data
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     * @throws Exception
     */
    final private function setModelData(?array $data, Mapper $map, int $maxLevels, int $currentLevel): void
    {
        $currentLevel++;

        $this->setBaseColumns($data, $map);

        //$this->setConstraints($data, $map, $maxLevels, $currentLevel);
        // TODO: check if I declare Constraint in Mapper and use same property name in Entity
        $this->setEmbedded($data, $map, $maxLevels, $currentLevel);

        $this->setComplex($data, $map, $maxLevels, $currentLevel);

        $this->postProcessing();
    }

    /**
     * Reads public variables and set them to the self instance
     *
     * @param array $rowData associative array where key is column name and value is column fetched data
     * @param Mapper $mapper
     *
     * @throws EntityException
     */
    final private function setBaseColumns(array $rowData, Mapper $mapper)
    {
        $calledClass = get_called_class();

        /**
         * @var array $fieldMapping are public properties of Mapper
         * where KEY is database origin column name and VALUE is Entity class field declaration
         * Structure look like this:
         * {
         *        "person_email":             "email",
         *        "person_id":                "id",
         *        "person_is_active":         "isActive",
         *        "person_name":              "name",
         *        "person_registration_date": "registrationDate"
         * }
         * EntityCache declaration happens in out constructor only once for time savings
         */
        $fieldMapping = EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE_MAP];

        /** If it is FullEntity or StrictlyFilledEntity, we must ensure all database columns are provided */
        if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
            $intersection = array_intersect_key($fieldMapping, $rowData);
            if ($intersection != $fieldMapping) {
                throw new EntityException(sprintf("Missing columns for FullEntity or StrictlyFilledEntity '%s': %s",
                        get_class($this),
                        json_encode(array_keys(array_diff_key($fieldMapping, $intersection)))
                    )
                );
            }
        }

        /**
         * @var string $originColumnName database origin column name
         * @var mixed $columnValue value of this columns
         */
        foreach ($rowData as $originColumnName => $columnValue) {

            /** process only if Entity class has such field declaration */
            if (!isset($fieldMapping[$originColumnName]))
                continue;

            /** @var string $property name of field declaration in Entity class */
            $property = $fieldMapping[$originColumnName];

            if (!property_exists($this, $property))
                continue;

            /** Note: Function names are case-insensitive, though it is usually good form to call functions as they appear in their declaration. */
            $setterMethod = "set{$property}";

            /** @var Column $fieldDefinition */
            $fieldDefinition = $mapper->$property;

            if (is_null($columnValue) and $fieldDefinition->nullable === false)
                throw new EntityException(sprintf("Column %s of %s shouldn't accept null values according Mapper definition", $originColumnName, $calledClass));

            /** We can define setter method for field definition in Entity class, so let's check it first */
            if (method_exists($this, $setterMethod)) {
                $this->$setterMethod($columnValue);
            } else {
                /** If initially column type is json, then let's parse it as JSON */
                if (stripos($fieldDefinition->originType, "json") !== false) {
                    $this->$property = json_decode($columnValue, true);
                } else {
                    /**
                     * Entity public variables should not have default values.
                     * But some times we need to have default value for column in case of $rowData has null value
                     * In this case we should not override default value if $columnValue is null
                     */
                    if (!isset($this->$property) and isset($columnValue))
                        $this->$property = $columnValue;
                }
            }
        }
    }

    /**
     * @param array|null $rowData
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     * @throws Exception
     */
    final private function setEmbedded(?array $rowData, Mapper $map, int $maxLevels, int $currentLevel)
    {
        if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
            /** @var Embedded[] $embeddings */
            $embeddings = MapperCache::me()->embedded[$map->name()];
            $missingColumns = [];
            foreach ($embeddings as $embedding) {
                if ($embedding->name !== false and !array_key_exists($embedding->name, $rowData))
                    $missingColumns[] = $embedding->name;
            }
            if (count($missingColumns) > 0) {
                throw new EntityException(sprintf("Seems you forgot to select columns for FullEntity or StrictlyFilledEntity '%s': %s",
                        get_class($this),
                        json_encode($missingColumns)
                    )
                );
            }
        }

        foreach ($map->getEmbedded() as $embeddedName => $embeddedValue) {
            if ($embeddedValue->name === false)
                continue;

            if ($currentLevel <= $maxLevels) {
                $setterMethod = "set" . ucfirst($embeddedName);

                if (method_exists($this, $setterMethod)) {
                    $this->$setterMethod($rowData[$embeddedValue->name]);
                    continue;
                }

                if (isset($embeddedValue->dbType) and $embeddedValue->dbType == Type::Json) {
                    if (isset($rowData[$embeddedValue->name]) and is_string($rowData[$embeddedValue->name])) {
                        $rowData[$embeddedValue->name] = json_decode($rowData[$embeddedValue->name], true);
                    }
                }
                if (isset($embeddedValue->entityClass)) {
                    if ($embeddedValue->isIterable) {
                        $iterables = [];
                        if (isset($rowData[$embeddedValue->name]) and !is_null($rowData[$embeddedValue->name])) {
                            foreach ($rowData[$embeddedValue->name] as $value)
                                $iterables[] = new $embeddedValue->entityClass($value, $maxLevels, $currentLevel);

                            $this->$embeddedName = $iterables;
                        } else {
                            if (!isset($this->$embeddedName))
                                $this->$embeddedName = null;
                        }
                    } else {
                        $this->$embeddedName = new $embeddedValue->entityClass($rowData[$embeddedValue->name], $maxLevels, $currentLevel);
                    }
                } else {
                    $this->$embeddedName = $rowData[$embeddedValue->name];
                }
            } else {
                unset($this->$embeddedName);
            }
        }
    }

    /**
     * @param array|null $data
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws Exception
     */
    private function setComplex(?array $data, Mapper $map, int $maxLevels, int $currentLevel)
    {
        foreach ($map->getComplex() as $complexName => $complexValue) {
            //if (!property_exists($this, $complexName) or isset(EntityCache::$mapCache[get_called_class()][EntityCache::UNSET_PROPERTIES][$complexName]))
            //    continue;

            if ($currentLevel <= $maxLevels)
                $this->$complexName = new $complexValue->complexClass($data, $maxLevels, $currentLevel);
            else
                unset($this->$complexName);
        }
    }

    /**
     * If entity data should be modified after setModelData, create same function in Entity.
     * For example it is heavy cost to aggregate some data in SQL side, any more cost efficient will do that with PHP
     *
     * @see setModelData()
     */
    protected function postProcessing(): void
    {
        /** @noinspection PhpUnnecessaryReturnInspection */
        return;
    }

    /**
     * get Entity table name
     *
     * @return string
     */
    public static function table(): string
    {
        $calledClass = get_called_class();

        return $calledClass::SCHEME . "." . $calledClass::TABLE;
    }

    /**
     * @param array $rowData
     * @param Mapper $mapper
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws Exception
     */
    final private function setConstraints(array $rowData, Mapper $mapper, int $maxLevels, int $currentLevel)
    {
        foreach ($mapper->getConstraints() as $entityName => $constraint) {

            if ($this instanceof FullEntity) {
                throw new EntityException(sprintf("FullEntity instance must not use constraint fields, the proper way is to extend it and declare as Complex.\nBad entity is '%s', failed property '%s'", get_class($this), $entityName));
            }

            /** Check we have data for this constraint */
            if (!property_exists($this, $entityName) or isset(EntityCache::$mapCache[get_called_class()][EntityCache::UNSET_PROPERTIES][$entityName]))
                continue;

            $constraintValue = isset($rowData[$constraint->localColumn->name]) ? $rowData[$constraint->localColumn->name] : null;

            // Случай, когда мы просто делаем джоин таблицы и вытаскиваем дополнительные поля,
            // то просто их прогоняем через класс и на выходе получим готовый объект

            $newConstraintValue = null;

            if (isset($constraintValue)) {
                if ($currentLevel <= $maxLevels) {
                    $newConstraintValue = new $constraint->class($rowData, $maxLevels, $currentLevel);
                } else {
                    // We skipping level. But we should also remove unsetted properties to issue notice of exception
                    // that calling variable on undefined
                    foreach ($mapper->getConstraints() as $constraintToRemove => $constraintToRemoveValue) {
                        unset($this->$constraintToRemove);
                    }
                }
            }

            $setterMethod = "set" . ucfirst($entityName);

            if (method_exists($this, $setterMethod)) {
                $this->$setterMethod($newConstraintValue);
            } else {
                // Если у нас переменная класа уже инициализирована, и нету значения из базы
                // то скорее всего этот объект является массивом данных
                if (!isset($this->$entityName) or isset($newConstraintValue)) {
                    if (isset($newConstraintValue))
                        $this->$entityName = $newConstraintValue;
                }
            }
        }
    }
}
