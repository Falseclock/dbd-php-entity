<?php

namespace Falseclock\DBD\Entity;

class Table
{
	/** @var string $name */
	public $name;
	/** @var string $scheme */
	public $scheme;
	/** @var Column[] $columns */
	public $columns;
	/** @var Key[] $keys */
	public $keys;
	/** @var string $annotation */
	public $annotation;
	/** @var Constraint[] $constraints */
	public $constraints;
}