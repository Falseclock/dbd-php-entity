<?php

namespace DBD\Entity;

use DBD\Common\Singleton;
use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Join\ManyToMany;
use DBD\Entity\Join\OneToMany;
use Exception;
use ReflectionException;

/**
 * Абстрактный класс для моделирования объектов данных. Все поля должны быть замаплены через переменную map
 */
abstract class EntityCache
{
	const ARRAY_MAP     = "arrayMap";
	const ARRAY_REVERSE = "reverseMap";
	public static $mapCache = [];
}

/**
 * Class Entity
 *
 * @property mixed id       это поле класса, которое, как правило, является serial полем
 * @property mixed constant это также поля класса
 */
abstract class Entity
{
	const SCHEME = "abstract";
	const TABLE  = "abstract";

	/**
	 * Конструктор модели
	 *
	 * @param array $data
	 * @param int   $maxLevels
	 * @param int   $currentLevel
	 *
	 * @throws EntityException
	 * @throws ReflectionException
	 */
	public function __construct(array $data = null, int $maxLevels = 2, int $currentLevel = 1) {

		$calledClass = get_class($this);

		Enforcer::__add(__CLASS__, $calledClass);

		if($currentLevel <= $maxLevels) {

			$map = self::map();

			if(!isset(EntityCache::$mapCache[$calledClass])) {

				EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP] = $map->getOriginFieldNames();
				EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE] = array_flip(EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP]);
			}

			$this->setModelData($data, $map, $maxLevels, $currentLevel);
		}
	}

	/**
	 * @return Singleton|Mapper|static
	 * @throws EntityException
	 */
	final public static function map() {
		$calledClass = get_called_class();

		try {

			/** @var Mapper $mapClass */
			$mapClass = $calledClass . Mapper::POSTFIX;
			$mapClass = $mapClass::me();
		}
		catch(Exception $e) {
			throw new EntityException(sprintf("Parsing '%s' end up with error: '%s' in %s:%s", $calledClass, $e->getMessage(), basename($e->getFile()), $e->getLine()));
		}

		return $mapClass;
	}

	/**
	 * get Entity table name
	 *
	 * @return string
	 */
	public static function table() {
		$calledClass = get_called_class();

		return $calledClass::SCHEME . "." . $calledClass::TABLE;
	}

	/**
	 * Reads public variables and set them to the self instance
	 *
	 * @param array  $rowData associative array where key is column name and value is column fetched data
	 * @param Mapper $mapper
	 * @param string $calledClass
	 */
	final private function setBaseColumns(array $rowData, Mapper $mapper, string $calledClass) {
		/** @var array $fieldMapping array where KEY is database origin column name and VALUE is Entity class field declaration */
		$fieldMapping = EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE];

		/**
		 * @var string $originColumnName database origin column name
		 * @var mixed  $columnValue      value of this columns
		 */
		foreach($rowData as $originColumnName => $columnValue) {
			// process only if Entity class has such field declaration
			if(isset($fieldMapping[$originColumnName])) {
				/** @var string $property name of field declaration in Entity class */
				$property = $fieldMapping[$originColumnName];

				/** Note: Function names are case-insensitive, though it is usually good form to call functions as they appear in their declaration. */
				$setterMethod = "set{$property}";

				/** @var Column $fieldDefinition */
				if(!property_exists($mapper, $property)) {
					return;
				}

				$fieldDefinition = $mapper->$property;
				if(is_array($fieldDefinition)) {
					$fieldDefinition = new Column($fieldDefinition);
				}

				/** We can define setter method for field definition in Entity class, so let's check it first */
				if(method_exists($this, $setterMethod)) {
					$this->$setterMethod($columnValue);
				}
				else {
					/** If initially column type is json, then let's parse it as JSON */
					if(stripos($fieldDefinition->originType, "json") !== false) {
						$this->$property = json_decode($columnValue, true);
					}
					else {
						/**
						 * Entity public variables should not have default values.
						 * But some times we need to have default value for column in case of $rowData has null value
						 * In this case we should not override default value if $columnValue is null
						 */
						if(!isset($this->$property) and isset($columnValue)) {
							$this->$property = $columnValue;
						}
					}
				}
			}
		}

		return;
	}

	/**
	 * @param array|null $data
	 * @param Mapper     $map
	 * @param            $maxLevels
	 * @param            $currentLevel
	 *
	 * @throws Exception
	 */
	private function setComplex(?array $data, Mapper $map, $maxLevels, $currentLevel) {
		foreach($map->getComplex() as $complexName => $complexValue) {

			if(!property_exists($this, $complexName))
				continue;

			$this->$complexName = new $complexValue->typeClass($data, $maxLevels, $currentLevel);
		}
	}

	/**
	 * @param array  $rowData
	 * @param Mapper $mapper
	 * @param        $maxLevels
	 * @param        $currentLevel
	 *
	 * @throws EntityException
	 * @throws Exception
	 */
	final private function setConstraints(array $rowData, Mapper $mapper, $maxLevels, $currentLevel) {

		foreach($mapper->getConstraints() as $entityName => $constraint) {
			/**
			 * Check we have data for this constraint
			 * Проверяем, что у нас есть данные для данного constraint
			 */
			if(!property_exists($this, $entityName))
				continue;

			if($constraint->localColumn instanceof Column) {
				$constraintValue = isset($rowData[$constraint->localColumn->name]) ? $rowData[$constraint->localColumn->name] : null;
			}
			else {
				/** @var ConstraintRaw $constraint */
				$constraintValue = isset($rowData[$constraint->localColumn]) ? $rowData[$constraint->localColumn] : null;
			}

			$testForJsonString = null;

			if(isset($constraintValue) and is_string($constraintValue)) {
				$testForJsonString = json_decode($constraintValue);
			}
			// Мы данные в первом прогоне могли уже сформировать в полноценный массив
			// Но в дочерние классы мы должны передавать  JSON строкой, а массивом,
			// Поэтому вертаем все назад как было
			if(isset($constraintValue) and is_array($constraintValue)) {
				$testForJsonString = $constraintValue;
				$constraintValue = json_encode($constraintValue, JSON_NUMERIC_CHECK);
			}

			// Если у нас действительно json строка
			if($testForJsonString !== null) {
				// Если это массив объектов
				if(is_array($testForJsonString)) {
					if($constraint->join instanceof ManyToMany or $constraint->join instanceof OneToMany) {
						// Разбиваем на нормальный массив, чтобы затолкать в переменную
						$jsonDecodedField = json_decode($constraintValue, true);
						$classVariableValue = [];

						foreach($jsonDecodedField as $object) {
							$classVariableValue[] = new $constraint->class($object, $maxLevels, $currentLevel);
						}
						$this->$entityName = $classVariableValue;
					}
					else {
						throw new EntityException("Variable '$entityName' of class {$this}");
					}
				}
				else {
					$jsonDecodedField = json_decode($constraintValue, true);
					$this->$entityName = new $constraint->class($jsonDecodedField, $maxLevels, $currentLevel);
				}
			}
			else {

				/**
				 * Случай, когда мы просто делаем джоин таблицы и вытаскиваем дополнительные поля,
				 * то просто их прогоняем через класс и на выходе получим готовый объект
				 */
				if(isset($constraintValue)) {
					$newConstraintValue = new $constraint->class($rowData, $maxLevels, $currentLevel);
				}
				else {
					//throw new EntityException("Понять какие это случаи и описать их тут");
					// Мы можем создать view, в которой не вытаскиваем данные по определенному constraint, потому что они нам не нужны
					$newConstraintValue = null;
					/*					if($keyFromMap === null && !isset($arrayMap[$entityName])) {

											$newConstraintValue = new $constraint->class($rowData, $maxLevels, $currentLevel);
										}
										else {
											$newConstraintValue = null;
										}*/
				}

				$setterMethod = "set" . ucfirst($entityName);

				if(method_exists($this, $setterMethod)) {
					$this->$setterMethod($newConstraintValue);
				}
				else
					// Если у нас переменная класа уже инициализирована, и нету значения из базы
					// то скорее всего этот объект является массивом данных
					if(isset($this->$entityName) and !isset($newConstraintValue)) {

					}
					else {
						if(isset($newConstraintValue)) {
							$this->$entityName = $newConstraintValue;
						}
						else {
							$this->$entityName = new $constraint->class($rowData, $maxLevels, $currentLevel);
						}
					}
			}
		}

		return;
	}

	/**
	 * @param array|null $data
	 * @param Mapper     $map
	 *
	 * @throws Exception
	 */
	final private function setEmbedded(?array $data, Mapper $map) {
		foreach($map->getEmbedded() as $embeddedName => $embeddedValue) {

			// TODO: do not override default class name if data is null

			if(isset($data[$embeddedValue->name])) {
				if(isset($embeddedValue->dbType) and $embeddedValue->dbType == Type::Json) {
					if(is_string($data[$embeddedValue->name])) {
						$data[$embeddedValue->name] = json_decode($data[$embeddedValue->name], true);
					}
				}
				if(isset($embeddedValue->entityClass)) {
					if($embeddedValue->isIterable) {
						$iterables = [];
						foreach($data[$embeddedValue->name] as $value) {
							$iterables[] = new $embeddedValue->entityClass($value);
						}
						$this->$embeddedName = $iterables;
					}
					else {
						$this->$embeddedName = new $embeddedValue->entityClass($data[$embeddedValue->name]);
					}
				}
				else {
					$this->$embeddedName = $data[$embeddedValue->name];
				}
			}
		}
	}

	/**
	 * @param array|null $data
	 * @param Mapper     $map
	 * @param int        $maxLevels
	 * @param int        $currentLevel
	 *
	 * @throws EntityException
	 * @throws Exception
	 */
	final private function setModelData(?array $data, Mapper $map, int $maxLevels, int $currentLevel): void {
		$currentLevel++;

		// We always should provide data
		if(!isset($data))
			return;

		$calledClass = get_called_class();

		$this->setBaseColumns($data, $map, $calledClass);

		$this->setConstraints($data, $map, $maxLevels, $currentLevel);

		$this->setEmbedded($data, $map);

		$this->setComplex($data, $map, $maxLevels, $currentLevel);

		return;
	}
}
