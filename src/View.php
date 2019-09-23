<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Entity\Common\EntityException;
use ReflectionException;

/**
 * Class View is mainly used for fetching data from DB views.
 * If view does not imply some columns, you can unset it in construction method
 * Actually this is not good way of programming and you should declare base Entity class and extend it further
 *
 * @package Falseclock\DBD\Entity
 */
abstract class View extends Entity
{
	/**
	 * View constructor.
	 *
	 * @param null $data
	 * @param int  $maxLevels
	 * @param int  $currentLevel
	 *
	 * @throws EntityException
	 * @throws ReflectionException
	 */
	public function __construct($data = null, int $maxLevels = 2, int $currentLevel = 1) {
		parent::__construct($data, $maxLevels, $currentLevel);
	}

	/**
	 * Special magic method to avoid getting of unset properties in construction method
	 *
	 * @param $name
	 *
	 * @return mixed
	 * @throws EntityException
	 */
	function __get($name) {
		if(!property_exists($this, $name)) {
			throw new EntityException("Property '{$name}' of '{$this}' not exist or unset in constructor");
		}

		return $this->$name;
	}

	/**
	 * Special magic method to avoid setting of unset properties in construction method
	 *
	 * @param $name
	 * @param $value
	 *
	 * @throws EntityException
	 */
	function __set($name, $value) {
		if(!property_exists($this, $name)) {
			throw new EntityException("Property '{$name}' of '{$this}' not exist or unset in constructor");
		}

		$this->$name = $value;
	}
}