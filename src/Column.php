<?php

namespace Falseclock\DBD\Entity;

class Column
{
	const ANNOTATION = "annotation";
	const DEFAULT    = "defaultValue";
	const MAXLENGTH  = "maxLength";
	const NAME       = "name";
	const NULLABLE   = "nullable";
	const PRECISION  = "precision";
	const SCALE      = "scale";
	const TYPE       = "type";
	/** @var Annotation|Annotation[] $annotation */
	public $annotation;
	/** @var string $name Column name */
	public $name;
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

	public function __construct(string $columnName = null) {
		if(isset($columnName)) {
			$this->name = $columnName;
		}
	}
}