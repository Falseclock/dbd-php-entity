<?php

namespace Falseclock\DBD\Entity;

class Constraint
{
	/** @var Column $column */
	public $column;
	/** @var Table $foreignTable */
	public $foreignTable;
	/** @var Column $foreignColumn */
	public $foreignColumn;
}