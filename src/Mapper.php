<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use Falseclock\DBD\Entity\Common\EntityException;
use Falseclock\DBD\Entity\Join\ManyToMany;
use Falseclock\DBD\Entity\Join\ManyToOne;
use Falseclock\DBD\Entity\Join\OneToMany;
use Falseclock\DBD\Entity\Join\OneToOne;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class MapperCache extends Singleton
{
	public $conversionCache = [];
}

/**
 * Название переменной в дочернем классе, которая должна быть если мы вызываем BaseHandler
 *
 * @property Column $id
 * @property Column $constant
 */
abstract class Mapper extends Singleton
{
	const ANNOTATION = "abstract";
	/** @var string $parentClassStringValue */
	private $parentClassStringValue;

	/**
	 * Special getter to access protected and private properties
	 * Unfortunately abstract class doesn't have access to child class,
	 * that is why we use Reflection.
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
	 * To print class name if used as a string
	 *
	 * @return string
	 */
	public function __toString(): string {
		return get_class($this);
	}

	/**
	 * @return array
	 */
	public function getOriginFieldNames() {
		/** @var Column[] $fields */
		$fields = get_object_vars($this);

		foreach($fields as &$field) {
			$field = $field->name;
		}

		return $fields;
	}

	/**
	 * @return MapperVariables
	 * @throws ReflectionException
	 */
	public function getAllVariables() {

		$reflection = new ReflectionClass($this);
		$public = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
		$protected = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
		$private = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

		unset($private['parentClassStringValue']);

		return new MapperVariables($public, $protected, $private);
	}

	public function getAnnotation() {
		return $this::ANNOTATION;
	}

	/**
	 * @return Column[]
	 */
	public function getColumns() {
		$columns = [];
		$fields = get_object_vars($this);

		foreach($fields as $field) {
			if($field instanceof Column)
				$columns[] = $field;
		}

		return $columns;
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
	 * Returns Entity class name
	 *
	 * @return string
	 */
	public function getEntityClass() {
		return $this->parentClassStringValue;
	}

	/**
	 * @return Key[]
	 */
	public function getKeys() {
		$columns = $this->getColumns();
		$keys = [];
		foreach($columns as $column) {
			if($column->key === true) {
				$keys[] = $column;
			}
		}

		return $keys;
	}

	/**
	 * @param $parentClass
	 *
	 * @return Mapper|Singleton|static
	 * @throws EntityException
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public static function init($parentClass) {
		$self = self::getInstance(get_called_class());

		$self->setParent($parentClass);

		return self::me();
	}

	/**
	 * @return Mapper|Singleton|static
	 * @throws EntityException
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public static function me() {

		$calledClass = get_called_class();

		/** @var Mapper $self */
		$self = self::getInstance($calledClass);

		// Check we set ANNOTATION properly in Mapper instance
		Enforcer::__add(__CLASS__, $calledClass);

		return $self;

		// We convert initial Mapper instance variables to Columns and References, so do it only once
		if(isset(MapperCache::me()->conversionCache[$calledClass])) {
			return $self;
		}
		else {
			$vars = get_object_vars($self);

			// This is local value and shouldn't be parsed
			unset($vars['parentClassStringValue']);

			// Read all variables and convert to Column and Constraint
			foreach($vars as $varName => $varValue) {

				// This is fix for old annotation when we used only column name as variable; TODO: remove after migration
				if(is_scalar($varValue)) {
					$self->$varName = new Column($varValue);
				}
				else if(is_array($varValue)) {

					$varValue = (object) $varValue;

					$constraintCheck = Constraint::FOREIGN_COLUMN;
					if(isset($varValue->$constraintCheck)) {
						// all constraints should be parsed after all columns processed
						continue;
					}

					$column = new Column();

					foreach($varValue as $key => $value) {
						if($key == Column::TYPE)
							$column->$key = new Primitive($value);
						else
							$column->$key = $value;
					}

					$self->$varName = $column;
					unset($vars[$varName]);
				}
				else {
					throw new EntityException("Unknown type of Mapper variable {$varName} in $self");
				}
			}

			// All constraints should be processed after columns
			foreach($vars as $constraintName => $constraintValue) {
				if(!is_array($constraintValue))
					continue;

				$constraintValue = (object) $constraintValue;
				$constraintCheck = Constraint::FOREIGN_COLUMN;

				if(isset($constraintValue->$constraintCheck)) {
					$constraint = new Constraint();
					$constraint->foreignTable = self::getForeignTable($constraintValue);
					$constraint->foreignColumn = self::findColumnByOriginName($constraint->foreignTable, $constraintValue->foreignColumn);

					$constraint->localTable = self::getTable($self->parentClassStringValue);
					//$constraint->localColumn->name = $constraintValue->column;

					// Own field definition could have own values and annotations
					//unset($constraint->localColumn->defaultValue);
					//unset($constraint->localColumn->key);
					//unset($constraint->localColumn->annotation);

					switch($constraintValue->joinType) {
						case Join::MANY_TO_ONE:
							$constraint->join = new ManyToOne();
							break;
						case Join::MANY_TO_MANY:
							$constraint->join = new ManyToMany();
							break;
						case Join::ONE_TO_ONE:
							$constraint->join = new OneToOne();
							break;
						case Join::ONE_TO_MANY:
							$constraint->join = new OneToMany();
							break;
					}
					$constraint->class = $constraintValue->baseClass;

					$self->$constraintName = $constraint;
				}
			}

			MapperCache::me()->conversionCache[$calledClass] = true;
		}

		return $self;
	}

	public function revers($string) {
		$revers = array_flip($this->getOriginFieldNames());

		return $revers[$string];
	}

	public function setParent($parentClass) {
		$this->parentClassStringValue = $parentClass;
	}

	/**
	 * @param stdClass $tableDefinition
	 *
	 * @return Table
	 * @throws EntityException
	 */
	private static function getForeignTable(stdClass $tableDefinition) {

		$map = self::getMapByClass($tableDefinition->baseClass);

		$table = new Table();
		$table->name = $tableDefinition->foreignTable;
		$table->scheme = $tableDefinition->foreignScheme;
		$table->columns = $map->getColumns();
		$table->constraints = $map->getConstraints();
		$table->keys = $map->getKeys();
		$table->annotation = $map->getAnnotation();

		return $table;
	}

	/**
	 * @param string $class
	 *
	 * @return Mapper
	 * @throws EntityException
	 */
	private static function getMapByClass(string $class) {

		/** @var Entity $class */
		return $class::mappingInstance();
	}

	/**
	 * @param string $parentClass
	 *
	 * @return Table
	 * @throws EntityException
	 */
	private static function getTable(string $parentClass) {
		$map = self::getMapByClass($parentClass);

		$table = new Table();
		/** @var Entity $parentClass */
		$table->name = $parentClass::getTableName();
		$table->scheme = $parentClass::getSchemeName();
		$table->columns = $map->getColumns();
		$table->constraints = $map->getConstraints();
		$table->keys = $map->getKeys();
		$table->annotation = $map->getAnnotation();

		return $table;
	}
}

final class MapperVariables
{
	public $columns;
	public $constraints;
	public $otherColumns;

	public function __construct($public, $protected, $private) {
		$this->columns = $this->filter($public);
		$this->constraints = $this->filter($protected);
		$this->otherColumns = $this->filter($private);
	}

	/**
	 * @param ReflectionProperty[] $vars
	 *
	 * @return array
	 */
	private function filter(array $vars) {
		$list = [];
		foreach($vars as $var) {
			$list[] = $var->getName();
		}

		return $list;
	}
}