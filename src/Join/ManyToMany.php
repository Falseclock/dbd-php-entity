<?php

namespace DBD\Entity\Join;

use DBD\Common\DBDException;
use DBD\Entity\Join;
use ReflectionException;

final class ManyToMany extends Join
{
	/**
	 * ManyToMany constructor.
	 *
	 * @param string $type
	 *
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function __construct($type = Join::MANY_TO_MANY) {
		parent::__construct($type);
	}
}