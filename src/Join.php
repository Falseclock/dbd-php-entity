<?php

namespace DBD\Entity;

use DBD\Common\DBDException;
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
		foreach($this->getConstants() as $name => $value) {
			if($value == $type) {
				$this->type = $type;

				return;
			}
		}
		throw new DBDException("Unknown join type {$type}");
	}

	/**
	 * @return string
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function getConstantName(): string {
		foreach($this->getConstants() as $name => $value) {
			if($value == $this->type) {
				return $name;
			}
		}
		throw new DBDException("Something strange happen");
	}

	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	private function getConstants(): iterable {
		$r = new ReflectionClass(self::class);

		return $r->getConstants();
	}
}