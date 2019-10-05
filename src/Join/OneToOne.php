<?php

namespace DBD\Entity\Join;

use DBD\Entity\Join;

final class OneToOne extends Join
{
	public function __construct($type = Join::ONE_TO_ONE) {
		parent::__construct($type);
	}
}