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
	 * @param string $originName
	 *
	 * @return Column
	 * @throws Exception
	 */
	public function findColumnByOriginName(string $originName) {
		foreach($this->getColumns() as $column) {
			if($column->name == $originName) {
				return $column;
			}
		}
		throw new Exception("Can't find origin column '{$originName}' in {$this}. If it is reference column, map it as protected");
	}

	/**
	 * Read all public, private and protected variable names and their values.
	 * Used when we need convert Mapper to Table instance
	 *
	 * @return MapperVariables
	 * @throws EntityException
	 * @throws Exception
	 */
	public function getAllVariables() {

		if(!isset(MapperCache::me()->allVariables[$this->name()])) {

			$allVars = get_object_vars($this);
			$columns = Utils::getObjectVars($this);
			$protectedVars = Utils::arrayDiff($allVars, $columns);

			$constraints = [];
			$otherColumns = [];
			$embedded = [];
			$complex = [];
			$constraintCheck = Constraint::LOCAL_COLUMN;

			foreach($protectedVars as $varName => $varValue) {
				if(is_array($varValue)) {
					if(isset($varValue[$constraintCheck])) {
						$constraints[$varName] = $varValue;
					}
					else {
						if(isset($varValue[Embedded::TYPE])) {
							$embedded[$varName] = $varValue;
						}
						else if(isset($varValue[Complex::VIEW_COLUMN])) {
							$complex[$varName] = $varValue;
						}
						else {
							$otherColumns[$varName] = $varValue;
						}
					}
				}
				else {
					throw new EntityException("variable '{$varName}' of '{$this}' is type of " . gettype($varValue));
				}
			}
			/** ----------------------EMBEDDED------------------------ */
			foreach($embedded as $embeddedName => $embeddedValue) {
				$this->$embeddedName = new Embedded($embeddedValue);
				MapperCache::me()->embedded[$this->name()][$embeddedName] = $this->$embeddedName;
			}
			// У нас может не быть эмбедов
			if(!isset(MapperCache::me()->embedded[$this->name()])) {
				MapperCache::me()->embedded[$this->name()] = [];
			}
			/** ----------------------COMPLEX------------------------ */
			foreach($complex as $complexName => $complexValue) {
				$this->$complexName = new Complex($complexValue);
				MapperCache::me()->complex[$this->name()][$complexName] = $this->$complexName;
			}
			// У нас может не быть комплексов
			if(!isset(MapperCache::me()->complex[$this->name()])) {
				MapperCache::me()->complex[$this->name()] = [];
			}

			/** ----------------------COLUMNS------------------------ */
			if(!isset(MapperCache::me()->columns[$this->name()])) {
				foreach($columns as $columnName => $columnValue) {
					$this->$columnName = new Column($columnValue);
					MapperCache::me()->baseColumns[$this->name()][$columnName] = $this->$columnName;
					MapperCache::me()->columns[$this->name()][$columnName] = $this->$columnName;
				}
				foreach($otherColumns as $columnName => $columnValue) {
					$this->$columnName = new Column($columnValue);
					MapperCache::me()->otherColumns[$this->name()][$columnName] = $this->$columnName;
					MapperCache::me()->columns[$this->name()][$columnName] = $this->$columnName;
				}
			}
			// У нас может не быть колонок
			if(!isset(MapperCache::me()->columns[$this->name()])) {
				MapperCache::me()->columns[$this->name()] = [];
			}

			/** ----------------------CONSTRAINTS------------------------ */
			if(!isset(MapperCache::me()->constraints[$this->name()])) {
				foreach($constraints as $constraintName => $constraintValue) {
					$temporaryConstraint = new ConstraintRaw($constraintValue);
					$temporaryConstraint->localColumn = $this->findColumnByOriginName($temporaryConstraint->localColumn);
					$temporaryConstraint->localTable = $this->getTable();
					$this->$constraintName = $temporaryConstraint;

					MapperCache::me()->constraints[$this->name()][$constraintName] = $this->$constraintName;
				}
			}
			// У нас может не быть констрейнтов
			if(!isset(MapperCache::me()->constraints[$this->name()])) {
				MapperCache::me()->constraints[$this->name()] = [];
			}

			MapperCache::me()->allVariables[$this->name()] = new MapperVariables($columns, $constraints, $otherColumns, $embedded, $complex);
		}

		return MapperCache::me()->allVariables[$this->name()];
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
	 * @return mixed
	 * @throws Exception
	 */
	public function getBaseColumns() {
		return MapperCache::me()->baseColumns[$this->name()];
	}

	/**
	 * @return Column[]
	 * @throws Exception
	 */
	public function getColumns() {
		return MapperCache::me()->columns[$this->name()];
	}

	public function getComplexes() {
		if(!isset(MapperCache::me()->complex[$this->name()])) {
			return [];
		}

		return MapperCache::me()->complex[$this->name()];
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

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getOtherColumns() {
		if(!isset(MapperCache::me()->otherColumns[$this->name()])) {
			return [];
		}

		return MapperCache::me()->otherColumns[$this->name()];
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getTable() {

		if(!isset(MapperCache::me()->table[$this->name()])) {
			$parentClass = $this->getEntityClass();
			$table = new Table();
			/** @var Entity $parentClass */
			$table->name = $parentClass::getTableName();
			$table->scheme = $parentClass::getSchemeName();
			$table->columns = $this->getBaseColumns();
			$table->otherColumns = $this->getOtherColumns();
			// FIXME:
			//$table->constraints = $this->getConstraints();
			//$table->keys = $this->getKeys();
			$table->annotation = $this->getAnnotation();

			MapperCache::me()->table[$this->name()] = $table;
		}

		return MapperCache::me()->table[$this->name()];
	}

	/**
	 * Used for quick access to the mapper without instantiating it and have only one instance
	 *
	 * @throws Exception
	 */
	public static function me() {

		/** @var static $self */
		$self = parent::me();

		$calledClass = get_called_class();

		if(!isset(MapperCache::me()->fullyInstantiated[$self->name()])) {

			// Check we set ANNOTATION properly in Mapper instance
			Enforcer::__add(__CLASS__, $calledClass);

			$self->getAllVariables();

			MapperCache::me()->fullyInstantiated[$self->name()] = true;

			return $self;
		}

		return $self;
	}

	public function name() {
		return (substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
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

/**
 * Class MapperCache used to avoid interfering with local variables in child classes
 *
 * @package Falseclock\DBD\Entity
 */
class MapperCache extends Singleton
{
	/** @var array $table */
	public $table = [];
	/** @var array $fullyInstantiated */
	public $fullyInstantiated = [];
	/** @var array $allVariables */
	public $allVariables = [];
	/** @var array $columns */
	public $columns = [];
	/** @var array $otherColumns */
	public $otherColumns = [];
	/** @var array $baseColumns */
	public $baseColumns = [];
	/** @var array $constraints */
	public $constraints = [];
	/** @var array $originFieldNames */
	public $originFieldNames = [];
	/** @var array $embedded */
	public $embedded = [];
	/** @var array $complex */
	public $complex = [];
}

final class MapperVariables
{
	public $columns;
	public $constraints;
	public $otherColumns;
	public $embedded;
	public $complex;

	public function __construct($columns, $constraints, $otherColumns, $embedded, $complex) {
		$this->columns = $this->filter($columns);
		$this->constraints = $this->filter($constraints);
		$this->otherColumns = $this->filter($otherColumns);
		$this->embedded = $this->filter($embedded);
		$this->complex = $this->filter($complex);
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
