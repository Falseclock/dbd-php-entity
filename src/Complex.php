<?php

namespace DBD\Entity;

/**
 * Class Complex is like JOIN table
 * Используется, когда к основному Entity джоинится таблица, не описанная в Entity
 *
 * @package Falseclock\DBD\Entity
 */
class Complex
{
	const ITERABLE = "isIterable";
	const TYPE     = "typeClass";
	const NULLABLE = "nullable";
	/** @var string $type full class name with namespace */
	public $typeClass;
	/** @var bool $isIterable */
	public $isIterable = false;
	/** @var bool $nullable If no value is specified for a single-valued property, the Nullable attribute defaults to true. */
	public $nullable = true;

	public function __construct($embeddedNameOrArray = null) {
		if(isset($embeddedNameOrArray)) {
			if(is_string($embeddedNameOrArray)) {
				$this->typeClass = $embeddedNameOrArray;
			}
			else {
				foreach($embeddedNameOrArray as $key => $value) {
					$this->$key = $value;
				}
			}
		}
	}
}