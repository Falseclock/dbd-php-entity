<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Common\DBDException;
use Falseclock\DBD\Common\Singleton;

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
	 * @return mixed|Singleton|static
	 * @throws DBDException
	 */
	public static function me() {

		/** @var Entity $self */
		$self = self::getInstance(get_called_class());

		// FIXME: uncomment me
		//Enforcer::__add(__CLASS__, get_called_class());

		if(!isset(MapperCache::me()->conversionCache[get_class($self)])) {
			$vars = get_object_vars($self);

			foreach($vars as $varName => $varValue) {

				if(is_scalar($varValue)) {
					$self->$varName = new Column($varValue);
				}
				else if(is_array($varValue)) {
					$varValue = (object) $varValue;
					$column = new Column();

					foreach($varValue as $key => $value) {
						if($key == Column::TYPE) {
							$column->$key = new Primitive($value);
						}
						else {
							$column->$key = $value;
						}
					}

					$self->$varName = $column;
				}
				else {
					throw new DBDException("Unknown type of Mapper variable {$varName} in $self");
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
}