<?php

namespace Falseclock\DBD\Entity;

class Table
{
	/** @var Column[] $columns */
	public $columns;
	/** @var Column[] $keys */
	public $keys;
	/** @var string $annotation */
	public $annotation;
}