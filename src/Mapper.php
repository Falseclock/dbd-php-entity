<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use Falseclock\DBD\Entity\Common\EntityException;
use Falseclock\DBD\Entity\Common\Utils;
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

		$thisName = $this->name();

		if(!isset(MapperCache::me()->allVariables[$thisName])) {

			/**
			 * All available variables
			 * Columns and Complex are always PUBLIC
			 * Constraints and Embedded are always PROTECTED
			 */
			$allVars = get_object_vars($this);
			$publicVars = Utils::getObjectVars($this);
			$protectedVars = Utils::arrayDiff($allVars, $publicVars);

			$constraints = [];
			$otherColumns = [];
			$embedded = [];
			$complex = [];
			$columns = [];
			$constraintCheck = Constraint::LOCAL_COLUMN;

			foreach($publicVars as $varName => $varValue) {
				// Column::TYPE is mandatory for Columns
				if(isset($varValue[Column::TYPE])) {
					$columns[$varName] = $varValue;
				} else {
					$complex[$varName] = $varValue;
				}
			}

			foreach($protectedVars as $varName => $varValue) {
				if(is_array($varValue)) {
					if(isset($varValue[$constraintCheck])) {
						$constraints[$varName] = $varValue;
					}
					else {
						if(isset($varValue[Complex::TYPE])) {
							$embedded[$varName] = $varValue;
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

			/** ----------------------COMPLEX------------------------ */
			foreach($embedded as $embeddedName => $embeddedValue) {
				$this->$embeddedName = new Complex($embeddedValue);
				MapperCache::me()->complex[$thisName][$embeddedName] = $this->$embeddedName;
			}
			// У нас может не быть эмбедов
			if(!isset(MapperCache::me()->complex[$thisName])) {
				MapperCache::me()->complex[$thisName] = [];
			}

			/** ----------------------EMBEDDED------------------------ */
			foreach($complex as $complexName => $complexValue) {
				$this->$complexName = new Embedded($complexValue);
				MapperCache::me()->embedded[$thisName][$complexName] = $this->$complexName;
			}
			// У нас может не быть комплексов
			if(!isset(MapperCache::me()->embedded[$thisName])) {
				MapperCache::me()->embedded[$thisName] = [];
			}

			/** ----------------------COLUMNS------------------------ */
			if(!isset(MapperCache::me()->columns[$thisName])) {
				foreach($columns as $columnName => $columnValue) {
					$this->$columnName = new Column($columnValue);
					MapperCache::me()->baseColumns[$thisName][$columnName] = $this->$columnName;
					MapperCache::me()->columns[$thisName][$columnName] = $this->$columnName;
				}
				foreach($otherColumns as $columnName => $columnValue) {
					$this->$columnName = new Column($columnValue);
					MapperCache::me()->otherColumns[$thisName][$columnName] = $this->$columnName;
					MapperCache::me()->columns[$thisName][$columnName] = $this->$columnName;
				}
			}
			// У нас может не быть колонок
			if(!isset(MapperCache::me()->columns[$thisName])) {
				MapperCache::me()->columns[$thisName] = [];
			}
			if(!isset(MapperCache::me()->otherColumns[$thisName])) {
				MapperCache::me()->otherColumns[$thisName] = [];
			}

			/** ----------------------CONSTRAINTS------------------------ */
			if(!isset(MapperCache::me()->constraints[$thisName])) {
				foreach($constraints as $constraintName => $constraintValue) {
					$temporaryConstraint = new ConstraintRaw($constraintValue);
					$temporaryConstraint->localColumn = $this->findColumnByOriginName($temporaryConstraint->localColumn);
					$temporaryConstraint->localTable = $this->getTable();
					$this->$constraintName = $temporaryConstraint;

					MapperCache::me()->constraints[$thisName][$constraintName] = $this->$constraintName;
				}
			}
			// У нас может не быть констрейнтов
			if(!isset(MapperCache::me()->constraints[$thisName])) {
				MapperCache::me()->constraints[$thisName] = [];
			}

			MapperCache::me()->allVariables[$thisName] = new MapperVariables($columns, $constraints, $otherColumns, $embedded, $complex);
		}

		return MapperCache::me()->allVariables[$thisName];
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

	/**
	 * @return Embedded[]
	 * @throws Exception
	 */
	public function getEmbedded() {
		return MapperCache::me()->embedded[$this->name()];
	}

	/**
	 * @return Complex[]
	 * @throws Exception
	 */
	public function getComplex() {
		return MapperCache::me()->complex[$this->name()];
	}

	/**
	 * @return Column[]
	 * @throws Exception
	 */
	public function getPrimaryKey() {
		$keys = [];
		foreach(MapperCache::me()->columns[$this->name()] as $columnName => $column) {
			if(isset($column->key) and $column->key == true) {
				$keys[$columnName] = $column;
			}
		}
		return $keys;
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

		$thisName = $this->name();
		if(!isset(MapperCache::me()->originFieldNames[$thisName])) {
			foreach($this->getColumns() as $columnName => $column) {
				MapperCache::me()->originFieldNames[$thisName][$columnName] = $column->name;
			}
		}

		return MapperCache::me()->originFieldNames[$thisName];
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getOtherColumns() {
		return MapperCache::me()->otherColumns[$this->name()];
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */
	public function getTable() {

		$thisName = $this->name();

		if(!isset(MapperCache::me()->table[$thisName])) {
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
	 * Used for quick access to the mapper without instantiating it and have only one instance
	 *
	 * @throws Exception
	 */
	public static function me() {

		/** @var static $self */
		$self = parent::me();

		if(!isset(MapperCache::me()->fullyInstantiated[$self->name()])) {

			// Check we set ANNOTATION properly in Mapper instance
			Enforcer::__add(__CLASS__, get_class($self));

			$self->getAllVariables();

			MapperCache::me()->fullyInstantiated[$self->name()] = true;

			return $self;
		}

		return $self;
	}

	private function name() {
		$name = get_class($this);
		return (substr($name, strrpos($name, '\\') + 1));
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
	/** @var array $complex */
	public $complex = [];
	/** @var array $embedded */
	public $embedded = [];
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
