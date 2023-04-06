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

use DBD\Common\Singleton;
use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\OnlyDeclaredPropertiesEntity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use Exception;
use ReflectionClass;
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

    /** @var array */
    private $rawData;

    /**
     * Конструктор модели
     *
     * @param array|null $data
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     */
    public function __construct(array $data = null, int $maxLevels = 2, int $currentLevel = 0)
    {
        $this->rawData = $data;

        $calledClass = get_class($this);

        if (!$this instanceof SyntheticEntity) {
            Enforcer::__add(__CLASS__, $calledClass);
        }

        try {
            /** @var Mapper $map */
            $map = self::map();
        } catch (Exception $e) {
            throw new EntityException(sprintf("Construction of %s failed, %s", $calledClass, $e->getMessage()));
        }

        if (!isset(EntityCache::$mapCache[$calledClass])) {
            /** @scrutinizer ignore-call */
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
            if ($this instanceof OnlyDeclaredPropertiesEntity) {
                $this->collectDeclarationsOnly(new ReflectionObject($this), $calledClass);
            }
        }

        if ($this instanceof OnlyDeclaredPropertiesEntity) {
            foreach (get_object_vars($this) as $varName => $varValue) {
                if (!isset(EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES][$varName]) && $varName != 'rawData') {
                    unset($this->$varName);
                    EntityCache::$mapCache[$calledClass][EntityCache::UNSET_PROPERTIES][$varName] = true;
                }
            }
        }

        if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
            $checkAgainst = array_merge($map->getColumns(), $map->getComplex(), $map->getEmbedded(), $map->getConstraints());
            foreach (get_object_vars($this) as $propertyName => $propertyDefaultValue) {
                if (!array_key_exists($propertyName, $checkAgainst) && $propertyName != 'rawData') {
                    throw new EntityException(sprintf("Strict Entity %s has unmapped property '%s'", $calledClass, $propertyName));
                }
            }
        }

        if (is_null($this->rawData)) {
            return;
        }
        // Если мы определяем класс с интерфейсом OnlyDeclaredPropertiesEntity и экстендим его
        // то по сути мы не можем знать какие переменные классам нам обязательны к обработке.
        // Ладно еще если это 2 класса, а если цепочка?
        //if($this instanceof OnlyDeclaredPropertiesEntity and !$reflectionObject->isFinal())
        //	throw new EntityException("Class " . $reflectionObject->getParentClass()->getShortName() . " which implements OnlyDeclaredPropertiesEntity interface must be final");

        if ($currentLevel <= $maxLevels) {
            $this->setModelData($map, $maxLevels, $currentLevel);
        }
    }

    /**
     * @return Singleton|Mapper|static
     * @throws EntityException
     * @noinspection PhpDocMissingThrowsInspection ReflectionClass will never throw exception because of get_called_class()
     */
    final public static function map()
    {
        $calledClass = get_called_class();

        $mapClass = $calledClass . Mapper::POSTFIX;

        if (!class_exists($mapClass, false)) {
            throw new EntityException(sprintf("Class %s does not have Map definition", $calledClass));
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = new ReflectionClass($calledClass);
        $interfaces = $reflection->getInterfaces();

        if (isset($interfaces[SyntheticEntity::class])) {
            return $mapClass::meWithoutEnforcer();
        } else {
            return $mapClass::me();
        }
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

            if ($declaringClass->name == $calledClass || $declaringClass->name == $parentClass) {
                EntityCache::$mapCache[$calledClass][EntityCache::DECLARED_PROPERTIES][$property->name] = true;
            }
        }

        $parentClass = $reflectionObject->getParentClass();
        $parentInterfaces = $parentClass->getInterfaces();

        if (isset($parentInterfaces[OnlyDeclaredPropertiesEntity::class])) {
            $this->collectDeclarationsOnly($parentClass, $calledClass, $parentClass->name);
        }

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
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     */
    private function setModelData(Mapper $map, int $maxLevels, int $currentLevel): void
    {
        $currentLevel++;

        $this->setBaseColumns($map);

        // TODO: check if I declare Constraint in Mapper and use same property name in Entity
        $this->setEmbedded($map, $maxLevels, $currentLevel);

        $this->setComplex($map, $maxLevels, $currentLevel);

        $this->postProcessing();
    }

    /**
     * Reads public variables and set them to the self instance
     *
     * @param Mapper $mapper
     *
     * @throws EntityException
     * @throws \ReflectionException
     */
    private function setBaseColumns(Mapper $mapper)
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
            $intersection = array_intersect_key($fieldMapping, $this->rawData);
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
        foreach ($this->rawData as $originColumnName => &$columnValue) {

            /** process only if Entity class has such field declaration */
            if (!isset($fieldMapping[$originColumnName])) {
                continue;
            }

            /** @var string $property name of field declaration in Entity class */
            $property = $fieldMapping[$originColumnName];

            if (!property_exists($this, $property)) {
                continue;
            }

            /** Note: Function names are case-insensitive, though it is usually good form to call functions as they appear in their declaration. */
            $setterMethod = sprintf("set%s", $property);

            /** @var Column $fieldDefinition */
            $fieldDefinition = $mapper->$property;

            if (is_null($columnValue) and $fieldDefinition->nullable === false) {
                throw new EntityException(sprintf("Column %s of %s shouldn't accept null values according Mapper definition", $originColumnName, $calledClass));
            }

            /** We can define setter method for field definition in Entity class, so let's check it first */
            if (method_exists($this, $setterMethod)) {
                $this->$setterMethod($columnValue);
            } else {
                /** If initially column type is json, then let's parse it as JSON */
                if (!is_null($columnValue) && !is_null($fieldDefinition->originType) && stripos($fieldDefinition->originType, "json") !== false) {
                    $this->$property = json_decode($columnValue, true);
                } else {
                    /**
                     * Entity public variables should not have default values.
                     * But sometimes we need to have default value for column in case of $rowData has null value
                     * In this case we should not override default value if $columnValue is null
                     * Иными словами нельзя переписывать дефолтное значение, если из базы пришло null
                     * но, если нет дефолтного значения, то мы должны его проинизиализировать null значением
                     */
                    $reflection = new ReflectionObject($this);
                    $reflectionProperty = $reflection->getProperty($property);

                    // Если мы еще не инциализировали переменную и у нас есть значение для этой переменной
                    //if (!isset($this->$property)) {

                        // Если у нас есть значение, то ставим его
                        if (isset($columnValue)) {
                            $this->$property = &$columnValue;
                        } else {
                            // У нас нет прицепленного значения
                            if (!$reflectionProperty->hasDefaultValue()) {
                                $this->$property = $columnValue; // this is NULL value
                            }
                        }
                    //}
                }
            }
        }
    }

    /**
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     */
    private function setEmbedded(Mapper $map, int $maxLevels, int $currentLevel)
    {
        if ($this instanceof FullEntity or $this instanceof StrictlyFilledEntity) {
            /** @var Embedded[] $embeddings */
            $embeddings = MapperCache::me()->embedded[$map->name()];
            $missingColumns = [];
            foreach ($embeddings as $embedding) {
                if ($embedding->name !== false and !array_key_exists($embedding->name, $this->rawData)) {
                    $missingColumns[] = $embedding->name;
                }
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
            if ($embeddedValue->name === false) {
                continue;
            }
            if ($currentLevel <= $maxLevels) {
                $setterMethod = "set" . ucfirst($embeddedName);

                if (method_exists($this, $setterMethod)) {
                    $this->$setterMethod($this->rawData[$embeddedValue->name]);
                    continue;
                }

                if (isset($embeddedValue->dbType) and $embeddedValue->dbType == Type::Json) {
                    if (isset($this->rawData[$embeddedValue->name]) and is_string($this->rawData[$embeddedValue->name])) {
                        $this->rawData[$embeddedValue->name] = json_decode($this->rawData[$embeddedValue->name], true);
                    }
                }
                if (isset($embeddedValue->entityClass)) {
                    if ($embeddedValue->isIterable) {
                        $iterables = [];
                        if (isset($this->rawData[$embeddedValue->name]) and !is_null($this->rawData[$embeddedValue->name])) {
                            foreach ($this->rawData[$embeddedValue->name] as $value) {
                                $iterables[] = new $embeddedValue->entityClass($value, $maxLevels, $currentLevel);
                            }
                            $this->$embeddedName = $iterables;
                        }
                    } else {
                        $this->$embeddedName = new $embeddedValue->entityClass($this->rawData[$embeddedValue->name], $maxLevels, $currentLevel);
                    }
                } else {
                    $this->$embeddedName = &$this->rawData[$embeddedValue->name];
                }
            } else {
                unset($this->$embeddedName);
            }
        }
    }

    /**
     * @param Mapper $map
     * @param int $maxLevels
     * @param int $currentLevel
     */
    private function setComplex(Mapper $map, int $maxLevels, int $currentLevel)
    {
        foreach ($map->getComplex() as $complexName => $complexValue) {
            //if (!property_exists($this, $complexName) or isset(EntityCache::$mapCache[get_called_class()][EntityCache::UNSET_PROPERTIES][$complexName]))
            //    continue;

            if ($currentLevel <= $maxLevels) {
                $this->$complexName = new $complexValue->complexClass($this->rawData, $maxLevels, $currentLevel);
            } else {
                unset($this->$complexName);
            }
        }
    }

    /**
     * If entity data should be modified after setModelData, create same function in Entity.
     * For example, it is heavy cost to aggregate some data in SQL side, any more cost-efficient will do that with PHP
     *
     * @see Embedded::$name
     * @see setModelData()
     */
    protected function postProcessing(): void
    {
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
     * @return array|null
     */
    public function raw(): ?array
    {
        return $this->rawData;
    }

    /**
     * Special getter to access properties with getters
     * For example, having method getName you can access $name property declared with (@)property annotation
     * @param string $methodName
     * @return mixed
     * @throws EntityException
     */
    public function __get(string $methodName)
    {
        $lookupMethod = $methodName;

        if (ctype_lower($methodName[0])) {
            $lookupMethod = ucfirst($methodName);
        }

        $lookupMethod = "get" . $lookupMethod;

        if (!method_exists($this, $lookupMethod)) {
            throw new EntityException(sprintf("Can't find property or getter method for '\$%s' of '%s'", $methodName, get_class($this)));
        }

        $this->$methodName = $this->$lookupMethod();

        return $this->$methodName;
    }
}
