<?php

namespace Falseclock\DBD\Entity;

class Table
{
	/** @var Column[] $field */
	public $field;
	/** @var Column[] $keys */
	public $keys;
	/** @var string $annotation */
	public $annotation;
}