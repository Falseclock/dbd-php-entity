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
use ReflectionException;
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

	public function __get(string $propertyName) {

		return null;
	}

	public function annotation() {
		return $this::ANNOTATION;
	}

	/**
	 * @return array
	 */
	public function fields() {
		/** @var Column[] $fields */
		$fields = get_object_vars($this);

		foreach($fields as &$field) {
			$field = $field->name;
		}

		return $fields;
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
	 * @return mixed|Singleton|static
	 * @throws EntityException
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public static function me() {

		/** @var Mapper $self */
		$self = self::getInstance(get_called_class());

		Enforcer::__add(__CLASS__, get_called_class());

		if(!isset(MapperCache::me()->conversionCache[get_class($self)])) {
			$vars = get_object_vars($self);

			foreach($vars as $varName => $varValue) {

				// This is fix for all annotation when we used only column name as variable
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

					$constraint->foreignColumn = self::findColumnByOriginName(self::getMapByClass($constraintValue->baseClass), $constraintValue->foreignColumn);
					$constraint->column = $constraint->foreignColumn;
					$constraint->column->name = $constraintValue->column;
					// Own field definition could have own values and annotations
					unset($constraint->column->defaultValue);
					unset($constraint->column->key);
					unset($constraint->column->annotation);

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

			MapperCache::me()->conversionCache[get_class($self)] = true;
		}

		return $self;
	}

	public function revers($string) {
		$revers = array_flip($this->fields());

		return $revers[$string];
	}

	/**
	 * @param Mapper $mapper
	 * @param string $columnOriginName
	 *
	 * @return Column
	 * @throws Exception
	 */
	private static function findColumnByOriginName(Mapper $mapper, string $columnOriginName): Column {
		foreach(get_object_vars($mapper) as $column) {
			if($column instanceof Column and $column->name == $columnOriginName) {
				return $column;
			}
		}
		throw new Exception("Can't find column {$columnOriginName}");
	}

	/**
	 * @param stdClass $tableDefinition
	 *
	 * @return Table
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
	 */
	private static function getMapByClass(string $class) {
		$mapClass = sprintf("\%sMap", $class);

		return call_user_func($mapClass . "::me");
	}
}