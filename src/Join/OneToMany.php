<?php

namespace DBD\Entity\Join;

use DBD\Entity\Join;

final class OneToMany extends Join
{
	public function __construct($type = Join::ONE_TO_MANY) {
		parent::__construct($type);
	}
}