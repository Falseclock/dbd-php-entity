<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use Falseclock\DBD\Entity\Common\EntityException;
use Falseclock\DBD\Entity\Join\ManyToMany;
use Falseclock\DBD\Entity\Join\OneToMany;
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
 * @property Column id       это поле класса, которое, как правило, является serial полем
 * @property Column constant это также поля класса
 */
abstract class Entity
{
	const SCHEME = "abstract";
	const TABLE  = "abstract";

	/**
	 * Конструктор модели
	 *
	 * @param null $data
	 *
	 * @param int  $maxLevels
	 * @param int  $currentLevel
	 *
	 * @throws EntityException
	 * @throws ReflectionException
	 */
	public function __construct($data = null, int $maxLevels = 2, int $currentLevel = 1) {

		Enforcer::__add(__CLASS__, get_called_class());

		if($currentLevel <= $maxLevels) {

			// Эте сделано для того, чтобы сотни раз не делать одно и тоже когда большие выборки
			$calledClass = get_called_class();

			$map = self::map();

			if(!isset(EntityCache::$mapCache[$calledClass])) {

				EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP] = $map->getOriginFieldNames();
				EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE] = array_flip(EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP]);
			}

			$this->setModelData($data, $map, $maxLevels, $currentLevel);
		}
	}

	/**
	 * Returns table scheme name
	 *
	 * @return mixed
	 */
	public static function getSchemeName() {
		return get_called_class()::SCHEME;
	}

	/**
	 * Returns table name
	 *
	 * @return string
	 */
	public static function getTableName() {
		return get_called_class()::TABLE;
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

	final private function setComplex(?array $data, Mapper $map) {
		foreach($map->getComplexes() as $complexName => $complexValue) {

			if(isset($data[$complexValue->viewColumn])) {
				if(isset($complexValue->dbType) and $complexValue->dbType == Type::Json) {
					$data[$complexValue->viewColumn] = json_decode($data[$complexValue->viewColumn], true);
				}
				if(isset($complexValue->entityClass)) {
					if($complexValue->isIterable) {
						$iterables = [];
						foreach($data[$complexValue->viewColumn] as $value) {
							$iterables[] = new $complexValue->entityClass($value);
						}
						$this->$complexName = $iterables;
					}
					else {
						$this->$complexName = new $complexValue->entityClass($data[$complexValue->viewColumn]);
					}
				}
				else {
					$this->$complexName = $data[$complexValue->viewColumn];
				}
			}
		}
	}

	final  private function setConstraints(array $rowData, Mapper $mapper, $maxLevels, $currentLevel) {

		foreach($mapper->getConstraints() as $entityName => $constraint) {
			/**
			 * Check we have data for this constraint
			 * Проверяем, что у нас есть данные для данного constraint
			 */

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
						$this->$entityName = $newConstraintValue;
					}
			}
		}

		return;
	}

	final private function setModelData(?array $data, Mapper $map, int $maxLevels, int $currentLevel): void {
		$currentLevel++;

		// We always should provide data
		if(!isset($data))
			return;

		$calledClass = get_called_class();

		$this->setBaseColumns($data, $map, $calledClass);

		$this->setConstraints($data, $map, $maxLevels, $currentLevel);

		$this->setComplex($data, $map);

		return;
	}

	/**
	 * @param $data
	 * @param $maxLevels
	 * @param $currentLevel
	 *
	 * @throws EntityException
	 */
	final private function setModelDataOld(array $data, Mapper $map, int $maxLevels, int $currentLevel) {

		$currentLevel++;

		if($data !== null) {
			$calledClass = get_called_class();

			$arrayMap = EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_MAP];
			$reverseMap = EntityCache::$mapCache[$calledClass][EntityCache::ARRAY_REVERSE];

			// Сперва бегаем по каждому полю в выборке и сэтим его как переменную класса
			if(count($reverseMap)) {
				foreach($data as $key => $value) {
					if(isset($reverseMap[$key])) {
						$property = $reverseMap[$key];

						// Note: Function names are case-insensitive, though it is usually good form to call functions as they appear in their declaration.
						$setterMethod = "set{$property}"; //. ucfirst($property);

						// Сразу парсим JSON поля
						if(isset($this->json[$key])) {
							if(method_exists($this, $setterMethod)) {
								$this->$setterMethod(json_decode($value, true));
							}
							else {
								$this->$property = json_decode($value, true);
							}
						}
						else {
							if(method_exists($this, $setterMethod)) {
								$this->$setterMethod($value);
							}
							else {
								// У нас могут быть стандартно задефайненные переменные, в основном объекты, которые обозначены как массивы
								if(!isset($this->$property) and isset($value)) {
									$this->$property = $value;
								}
							}
						}
					}
				}
			}
			else {
				throw new EntityException(get_class($this) . " does not have mapping");
			}

			// Защита от зацикливания если дочерний класс и родительский класс ссылаются друг на друга
			$data['ALL_CLASSES_CHAIN'][] = get_class($this);

			// Теперь пробегаемся по все объектам в классе и создаем объекты
			foreach($this->objects as $classVariableName => $classFullNamespace) {
				if(is_null($classFullNamespace)) {
					throw new EntityException("Class '$classVariableName' does not have CLASS_NAME constant");
				}

				// FIXME: проверить как работает если мы ссылаемся на класс через 3-ий и выше класс и вообще возможность такой ситуации
				// Чтобы не было перецикливания, проверяем был ли уже проход через класс
				if(!in_array($classFullNamespace, $data['ALL_CLASSES_CHAIN'])) {

					$keyFromMap = null;
					if(isset($arrayMap[$classVariableName])) {
						$keyFromMap = $arrayMap[$classVariableName];
					}

					$jsonTest = null;
					if(isset($data[$keyFromMap]) && is_string($data[$keyFromMap])) {
						$jsonTest = json_decode($data[$keyFromMap]);
					}
					// Мы данные в первом прогоне могли уже сформировать в полноценный массив
					// Но в дочерние классы мы должны передавать  JSON строкой, а массивом,
					// Поэтому вертаем все назад как было
					if(isset($data[$keyFromMap]) && is_array($data[$keyFromMap])) {
						$jsonTest = $data[$keyFromMap];
						$data[$keyFromMap] = json_encode($data[$keyFromMap], JSON_NUMERIC_CHECK);
					}

					// Есди у нас действительно json строка
					if($jsonTest !== null) {
						//Если это массив объектов
						if(is_array($jsonTest)) {
							// Разбиваем на нормальный массив
							$jsonDecodedField = json_decode($data[$keyFromMap], true);
							$classVariableValue = [];

							foreach($jsonDecodedField as $object) {
								//Пакуем каждый объект в класс и добавляем в массив
								$object['ALL_CLASSES_CHAIN'] = $data['ALL_CLASSES_CHAIN'];
								$classVariableValue[] = new $classFullNamespace($object);
							}
							$this->$classVariableName = $classVariableValue;
						}
						else {
							$jsonDecodedField = json_decode($data[$keyFromMap], true);
							$jsonDecodedField['ALL_CLASSES_CHAIN'] = $data['ALL_CLASSES_CHAIN'];
							$this->$classVariableName = new $classFullNamespace($jsonDecodedField);
						}
					}
					else {
						$setterMethod = "set" . ucfirst($classVariableName);

						// У нас не понятно что, поэтому пытаемся распарсить как есть
						if(isset($data[$keyFromMap]) && $data[$keyFromMap] != null) {
							$newClassValue = new $classFullNamespace($data);
						}
						else {
							// Тот случай, когда мы вытаскиваем солянку колонк из вьюшки и определяем колонки только в объектах
							if($keyFromMap === null && !isset($arrayMap[$classVariableName])) {
								/** @see setModelDataOld */
								$newClassValue = new $classFullNamespace($data, $maxLevels, $currentLevel);
							}
							else {
								$newClassValue = null;
							}
						}

						if(method_exists($this, $setterMethod)) {
							$this->$setterMethod($newClassValue);
						}
						else
							// Если у нас переменная класа уже инициализирована, и нету значения из базы
							// то скорее всего этот объект является массивом данных
							if(isset($this->$classVariableName) and !isset($newClassValue)) {

							}
							else {
								$this->$classVariableName = $newClassValue;
							}
					}
				}
			}
		}
	}
}
