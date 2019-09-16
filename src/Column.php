<?php

namespace Falseclock\DBD\Entity;

class Column
{
	const ANNOTATION  = "annotation";
	const DEFAULT     = "defaultValue";
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
	/** @var string $name Column name */
	public $name;
	/** @var string $type */
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

	public function __construct(string $columnName = null) {
		if(isset($columnName)) {
			$this->name = $columnName;
		}
	}
}