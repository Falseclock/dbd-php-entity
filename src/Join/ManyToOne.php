<?php

namespace DBD\Entity\Join;

use DBD\Common\DBDException;
use DBD\Entity\Join;
use ReflectionException;

final class ManyToOne extends Join
{
	/**
	 * ManyToOne constructor.
	 *
	 * @param string $type
	 *
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function __construct($type = Join::MANY_TO_ONE) {
		parent::__construct($type);
	}
}