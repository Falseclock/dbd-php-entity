<?php

namespace Falseclock\DBD\Entity\Join;

use Falseclock\DBD\Entity\Join;

class ManyToMany extends Join
{
	public function __construct($type = Join::MANY_TO_MANY) {
		parent::__construct($type);
	}
}