<?php

namespace DBD\Entity\Join;

use DBD\Entity\Join;

final class ManyToOne extends Join
{
	public function __construct($type = Join::MANY_TO_ONE) {
		parent::__construct($type);
	}
}