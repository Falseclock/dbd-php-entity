<?php

namespace DBD\Entity\Join;

use DBD\Common\DBDException;
use DBD\Entity\Join;
use ReflectionException;

final class OneToOne extends Join
{
	/**
	 * OneToOne constructor.
	 *
	 * @param string $type
	 *
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	public function __construct($type = Join::ONE_TO_ONE) {
		parent::__construct($type);
	}
}