<?php

namespace Falseclock\DBD\Entity\Join;

use Falseclock\DBD\Entity\Join;

class OneToOne extends Join
{
	public function __construct($type = Join::ONE_TO_ONE) {
		parent::__construct($type);
	}
}