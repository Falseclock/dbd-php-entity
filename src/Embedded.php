<?php

namespace Falseclock\DBD\Entity;

/**
 * Class Embedded used when you join other table and want to get variable which is child of Entity
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
		if(isset($embedded)) {
			if(is_string($embeddedNameOrArray)) {
				$this->typeClass = $embeddedNameOrArray;
			}
			else {
				foreach($embedded as $key => $value) {
					$this->$key = $value;
				}
			}
		}
	}
}