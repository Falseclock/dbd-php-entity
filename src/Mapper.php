<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use Falseclock\DBD\Entity\Common\EntityException;
use InvalidArgumentException;
use ReflectionProperty;

/**
 * Название переменной в дочернем классе, которая должна быть если мы вызываем BaseHandler
 *
 * @property Column $id
 * @property Column $constant
 */
class Mapper extends Singleton
{
	const ANNOTATION = "abstract";
	const POSTFIX    = "Map";

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
	public function __get($name) {

		if(!property_exists($this, $name)) {
			throw new InvalidArgumentException("Getting the field '$name' is not valid for '{$this}");
		}

		return $this->$name;
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
	 * @throws EntityException
	 */
	public function getAllVariables() {

		if(!isset(MapperCache::me()->allVariables[$this->name()])) {

			$allVars = get_object_vars($this);
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
					throw new EntityException("variable '{$varName}' of '{$this}' is type of " . gettype($varValue));
				}
			}

			if(!isset(MapperCache::me()->columns[$this->name()])) {
				foreach(array_merge($columns, $otherColumns) as $columnName => $columnValue) {
					$this->$columnName = new Column($columnValue);
					MapperCache::me()->columns[$this->name()][$columnName] = $this->$columnName;
				}
			}

			if(!isset(MapperCache::me()->constraints[$this->name()])) {
				foreach($constraints as $constraintName => $constraintValue) {
					$this->$constraintName = new ConstraintRaw($constraintValue);
					MapperCache::me()->constraints[$this->name()][$constraintName] = $this->$constraintName;
				}
			}

			MapperCache::me()->allVariables[$this->name()] = new MapperVariables($columns, $constraints, $otherColumns);
		}

		return MapperCache::me()->allVariables[$this->name()];
	}

	public function name() {
		return (substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
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
	 * @return Column[]
	 * @throws Exception
	 */
	public function getColumns() {
		return MapperCache::me()->columns[$this->name()];
	}

	/**
	 * @return Constraint[]
	 * @throws Exception
	 */
	public function getConstraints() {
		return MapperCache::me()->constraints[$this->name()];
	}

	/**
	 * Returns Entity class name which uses this Mapper
	 *
	 * @return string
	 */
	public function getEntityClass() {
		return substr("{$this}", 0, strlen(self::POSTFIX) * -1);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getOriginFieldNames() {

		if(!isset(MapperCache::me()->originFieldNames[$this->name()])) {

			foreach($this->getColumns() as $columnName => $column) {
				MapperCache::me()->originFieldNames[$this->name()][$columnName] = $column->name;
			}
		}

		return MapperCache::me()->originFieldNames[$this->name()];
	}

	public function getTable() {
		return MapperCache::me()->table[$this->name()];
	}

	/**
	 * Used for quick access to the mapper without instantiating it and have only one instance
	 */
	public static function me() {

		/** @var static $self */
		$self = parent::me();

		$calledClass = get_called_class();

		if(!MapperCache::me()->fullyInstantiated[$self->name()]) {

			// Check we set ANNOTATION properly in Mapper instance
			Enforcer::__add(__CLASS__, $calledClass);

			$self->getAllVariables();

			MapperCache::me()->fullyInstantiated[$self->name()] = true;

			return $self;
		}

		return $self;
	}

	/**
	 * @param $string
	 *
	 * @return mixed
	 * @throws Exception
	 * @deprecated
	 */
	public function revers($string) {
		$revers = array_flip($this->getOriginFieldNames());

		return $revers[$string];
	}
}

class MapperCache extends Singleton
{
	/** @var array $table */
	public $table;
	/** @var array $fullyInstantiated */
	public $fullyInstantiated ;
	/** @var array $allVariables */
	public $allVariables;
	/** @var array $columns */
	public $columns;
	/** @var array $constraints */
	public $constraints;
	/** @var array $originFieldNames */
	public $originFieldNames;
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
