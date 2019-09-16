<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Common\DBDException;
use ReflectionClass;
use ReflectionException;

class Join
{
	const MANY_TO_MANY = "manyToMany";
	const MANY_TO_ONE  = "manyToOne";
	const ONE_TO_MANY  = "oneToMany";
	const ONE_TO_ONE   = "oneToOne";
	/** @var string $type */
	public $type;

	/**
	 * Join constructor.
	 *
	 * @param $type
	 *
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function __construct($type) {
		$r = new ReflectionClass(self::class);
		foreach($r->getConstants() as $name => $value) {
			if($value == $type) {
				$this->type = $type;

				return;
			}
		}
		throw new DBDException("Unknown join type {$type}");
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}
}