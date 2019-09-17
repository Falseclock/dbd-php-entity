<?php

namespace Falseclock\DBD\Entity;

class Key
{
	public $column;

	public function __construct($column) {
		$this->column = $column;
	}
}