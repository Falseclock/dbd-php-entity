<?php

namespace DBD\Entity;

class Key
{
	public $column;

	/**
	 * Key constructor.
	 *
	 * @param $column
	 */
	public function __construct($column) {
		$this->column = $column;
	}
}