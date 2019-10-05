<?php

namespace DBD\Entity\Join;

use DBD\Entity\Join;

final class ManyToMany extends Join
{
	public function __construct($type = Join::MANY_TO_MANY) {
		parent::__construct($type);
	}
}