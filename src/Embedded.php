<?php

namespace Falseclock\DBD\Entity;

/**
 * Class Embedded is like JOIN table
 * Используется, когда к основному Entity джоинится таблица, не описанная в Entity
 *
 * @package Falseclock\DBD\Entity
 */
class Embedded
{
	const ITERABLE = "isIterable";
	const TYPE     = "typeClass";
	/** @var string $type full class name with namespace */
	public $typeClass;
	/** @var bool $isIterable */
	public $isIterable = false;

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