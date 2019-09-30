<?php

namespace Falseclock\DBD\Entity;

class Column
{
	const ANNOTATION  = "annotation";
	const DEFAULT     = "defaultValue";
	const IS_AUTO     = "isAuto";
	const KEY         = "key";
	const MAXLENGTH   = "maxLength";
	const NAME        = "name";
	const NULLABLE    = "nullable";
	const ORIGIN_TYPE = "originType";
	const PRECISION   = "precision";
	const SCALE       = "scale";
	const TYPE        = "type";
	/** @var string $annotation TODO: Annotation|Annotation[] */
	public $annotation;
	/** @var string $name name of column in database */
	public $name;
	/** @var string $type type of column as written in database */
	public $originType;
	/** @var Primitive $type */
	public $type;
	/** @var bool $nullable */
	public $nullable;
	/** @var int $maxLength */
	public $maxLength;
	/** @var int $precision */
	public $precision;
	/** @var mixed $scale */
	public $scale;
	/** @var mixed $defaultValue */
	public $defaultValue;
	/** @var boolean $key Flag of Primary key */
	public $key;
	/** @var boolean $isAuto is column has auto increment or auto generated value? */
	public $isAuto = false;

	public function __construct($columnNameOrArray = null) {
		if(isset($columnNameOrArray)) {
			if(is_string($columnNameOrArray)) {
				$this->name = $columnNameOrArray;
			}

			if(is_array($columnNameOrArray)) {
				foreach($columnNameOrArray as $key => $value) {
					if($key == self::TYPE) {
						$this->type = new Primitive($value);
					}
					else {
						$this->$key = $value;
					}
				}
			}
		}
	}
}