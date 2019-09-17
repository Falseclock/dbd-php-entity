<?php

namespace Falseclock\DBD\Entity;

use Exception;
use Falseclock\DBD\Common\Singleton;
use Falseclock\DBD\Entity\Common\Enforcer;
use Falseclock\DBD\Entity\Common\EntityException;
use ReflectionException;

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
			foreach($vars as $varName => $varValue) {
				if(!is_array($varValue))
					continue;

				$varValue = (object) $varValue;
				$constraintCheck = Constraint::FOREIGN_COLUMN;

				if(isset($varValue->$constraintCheck)) {
					$constraint = new Constraint();
					$constraint->column = self::findColumnByOriginName($self, $varValue->column);

					$self->$varName = $constraint;
				}
			}

			MapperCache::me()->conversionCache[get_class($self)] = true;
		}

		return $self;
	}

	/**
	 * @param Mapper $self
	 * @param string $columnOriginName
	 *
	 * @return Column
	 * @throws Exception
	 */
	private static function findColumnByOriginName(Mapper $self, string $columnOriginName): Column {
		foreach(get_object_vars($self) as $column) {
			if ($column instanceof Column and $column->name == $columnOriginName) {
				return $column;
			}
		}
		throw new Exception("Can't find column {$columnOriginName}");
	}

	public function revers($string) {
		$revers = array_flip($this->fields());

		return $revers[$string];
	}
}