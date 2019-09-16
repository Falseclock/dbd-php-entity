<?php

namespace Falseclock\DBD\Entity\Join;

use Falseclock\DBD\Entity\Join;

class ManyToOne extends Join
{
	public function __construct($type = Join::MANY_TO_ONE) {
		parent::__construct($type);
	}
}