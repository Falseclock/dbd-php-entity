<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Common\DBDException;
use Falseclock\DBD\Common\Enforcer;
use ReflectionException;

/**
 * Абстрактный класс для моделирования объектов данных. Все поля должны быть замаплены через переменную map
 * TODO: make EntityException instead of DBDException
 */
abstract class EntityCache
{
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
	const SCHEME     = "abstract";
	const TABLE      = "abstract";
	protected $objects = [/* create me */ ];
	protected $json    = [/* set me */ ];

	/**
	 * Конструктор модели
	 *
	 * @param null $data
	 *
	 * @param int  $maxLevels
	 * @param int  $currentLevel
	 *
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function __construct($data = null, int $maxLevels = 2, int $currentLevel = 1) {

		Enforcer::__add(__CLASS__, get_called_class());

		if($currentLevel <= $maxLevels) {

			// Эте сделано для того, чтобы сотни раз не делать одно и тоже когда большие выборки
			$calledClass = get_called_class();

			if(!isset(EntityCache::$mapCache[$calledClass])) {

				$map = self::readMap();

				EntityCache::$mapCache[$calledClass]['arrayMap'] = $map->fields();
				EntityCache::$mapCache[$calledClass]['reverseMap'] = array_flip(EntityCache::$mapCache[$calledClass]['arrayMap']);
			}

			$this->setModelData($data, $maxLevels, $currentLevel);

			unset($this->json);
			unset($this->objects);
		}
	}

	/**
	 * @return Mapper
	 * @throws DBDException
	 */
	final public static function map() {
		return self::readMap();
	}

	/**
	 * Return table name with scheme prefix
	 * @return string
	 */
	public static function table() {
		return get_called_class()::SCHEME . "." . get_called_class()::TABLE;
	}

	/**
	 * @return Mapper
	 * @throws DBDException
	 */
	final private static function readMap() {
		$calledClass = get_called_class();
		$mapClass = null;
		try {
			/** @var Mapper $mapClass */
			$mapClass = $calledClass . "Map";
			$mapClass = $mapClass::me();
		}
		catch(DBDException $e) {
			trigger_error("Entity class $calledClass does not have mapping: {$e->getMessage()}", E_USER_ERROR);
		}

		return $mapClass::me();
	}

	final private function setModelData($data, $maxLevels, $currentLevel) {

		$currentLevel++;

		if($data !== null) {
			$calledClass = get_called_class();

			$arrayMap = EntityCache::$mapCache[$calledClass]['arrayMap']; //$this->arrayMap;
			$reverseMap = EntityCache::$mapCache[$calledClass]['reverseMap']; //$this->reverseMap;

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
				trigger_error(get_class($this) . " does not have mapping", E_USER_ERROR);
			}

			// Защита от зацикливания если дочерний класс и родительский класс ссылаются друг на друга
			$data['ALL_CLASSES_CHAIN'][] = get_class($this);

			// Теперь пробегаемся по все объектам в классе и создаем объекты
			foreach($this->objects as $classVariableName => $classFullNamespace) {
				if(is_null($classFullNamespace)) {
					trigger_error("Class '$classVariableName' does not have CLASS_NAME constant", E_USER_ERROR);
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
