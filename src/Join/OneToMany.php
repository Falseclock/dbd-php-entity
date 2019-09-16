<?php

namespace Falseclock\DBD\Entity\Join;

use Falseclock\DBD\Entity\Join;

class OneToMany extends Join
{
	public function __construct($type = Join::ONE_TO_MANY) {
		parent::__construct($type);
	}
}