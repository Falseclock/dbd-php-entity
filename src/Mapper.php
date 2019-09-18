<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use InvalidArgumentException;
use ReflectionException;
use ReflectionProperty;

/**
 * Название переменной в дочернем классе, которая должна быть если мы вызываем BaseHandler
 *
 * @property Column $id
 * @property Column $constant
 */
abstract class Mapper extends Singleton
{
	const ANNOTATION = "abstract";
	const POSTFIX    = "Map";

	/**
	 * Special getter to access protected and private properties
	 * Unfortunately abstract class doesn't have access to child class,
	 * that is why we use Reflection.
	 * TODO: set all to public with some markers to identify fields and constraints
	 *
	 * @param $name
	 *
	 * @return mixed
	 * @throws ReflectionException
	 */
	public function __get($name) {
		$name = chop($name);
		if(!property_exists($this, $name)) {
			throw new InvalidArgumentException("Getting the field '$name' is not valid for '{$this}");
		}

		$reflection = new ReflectionProperty($this, $name);
		$reflection->setAccessible($name);

		return $reflection->getValue($this);
	}

	/**
	 * To print class name if used as a string in exceptions
	 *
	 * @return string
	 */
	public function __toString(): string {
		return get_class($this);
	}

	/**
	 * Read all public, private and protected variable names and their values.
	 * Used when we need convert Mapper to Table instance
	 *
	 * @return MapperVariables
	 * @throws Exception
	 */
	public function getAllVariables() {

		if(!isset(MapperCache::me()->variablesCache["{$this}"])) {

			$allVars = get_class_vars($this);
			$columns = Utils::getObjectVars($this);
			$protectedVars = Utils::arrayDiff($allVars, $columns);

			$constraints = [];
			$otherColumns = [];

			$constraintCheck = Constraint::LOCAL_COLUMN;

			foreach($protectedVars as $varName => $varValue) {
				if(is_array($varValue)) {
					if(isset($varValue[$constraintCheck])) {
						$constraints[$varName] = $varValue;
					}
					else {
						$otherColumns[$varName] = $varValue;
					}
				}
				else {
					throw new Exception("This should not happen!");
				}
			}

			MapperCache::me()->variablesCache["{$this}"] = new MapperVariables($columns, $constraints, $otherColumns);
		}

		return MapperCache::me()->variablesCache["{$this}"];
	}

	/**
	 * Returns table comment
	 *
	 * @return string
	 */
	public function getAnnotation() {
		return $this::ANNOTATION;
	}

	/**
	 * @return Constraint[]
	 */
	public function getConstraints() {

		$constraints = [];
		foreach(get_object_vars($this) as $varName => $varValue) {
			if($varValue instanceof Constraint) {
				$constraints[$varName] = $varValue;
			}
		}

		return $constraints;
	}

	/**
	 * Returns Entity class name which uses this Mapper
	 *
	 * @return string
	 */
	public function getEntityClass() {
		return rtrim($this, self::POSTFIX);
	}

	/**
	 * @return array
	 */
	public function getOriginFieldNames() {
		/** @var Column[] $fields */
		$fields = get_object_vars($this);

		foreach($fields as $entityColumnName => $column) {
			if($column instanceof Column) {
				$fields[$entityColumnName] = $column->name;
			}
			else {
				unset($fields[$entityColumnName]);
			}
		}

		return $fields;
	}

	/**
	 * Used for quick access to the mapper without instantiating it and have only one instance
	 */
	public static function me() {

		$calledClass = get_called_class();

		/** @var Mapper $self */
		$self = self::getInstance($calledClass);

		// Check we set ANNOTATION properly in Mapper instance
		Enforcer::__add(__CLASS__, $calledClass);

		if(!in_array("{$calledClass}", MapperCache::me()->conversionCache)) {

			$table = Table::getFromMapper($self);

			// Convert arrays to Column
			foreach(array_merge($table->columns, $table->otherColumns) as $columnName => $columnValue) {
				$self->$columnName = $columnValue;
			}

			foreach($table->constraints as $constraint) {
				$varName = $self->getConstraintVariableNameByFieldName($constraint->localColumn->name);
				$self->$varName = $constraint;
			}

			MapperCache::me()->conversionCache[] = "{$calledClass}";
		}

		return $self;
	}

	public function revers($string) {
		$revers = array_flip($this->getOriginFieldNames());

		return $revers[$string];
	}

	/**
	 * @param string $lookUpName
	 *
	 * @return string
	 * @throws Exception
	 */
	private function getConstraintVariableNameByFieldName(string $lookUpName): string {
		foreach(get_object_vars($this) as $varName => $varValue) {
			if(is_array($varValue) and isset($varValue[Constraint::LOCAL_COLUMN]) and $varValue[Constraint::LOCAL_COLUMN] == $lookUpName) {
				return $varName;
			}
		}
		throw new Exception("Can't find constraint");
	}
}

class MapperCache extends Singleton
{
	public $conversionCache = [];
	public $variablesCache  = [];
}

final class MapperVariables
{
	public $columns;
	public $constraints;
	public $otherColumns;

	public function __construct($columns, $constraints, $otherColumns) {
		$this->columns = $this->filter($columns);
		$this->constraints = $this->filter($constraints);
		$this->otherColumns = $this->filter($otherColumns);
	}

	/**
	 * @param ReflectionProperty[] $vars
	 *
	 * @return array
	 */
	private function filter(array $vars) {
		$list = [];
		foreach($vars as $varName => $varValue) {
			$list[] = $varName;
		}

		return $list;
	}
}
