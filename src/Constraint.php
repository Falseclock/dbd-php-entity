<?php

namespace Falseclock\DBD\Entity;

class Constraint
{
	const BASE_CLASS     = "baseClass";
	const COLUMN         = "column";
	const FOREIGN_COLUMN = "foreignColumn";
	const FOREIGN_SCHEME = "foreignScheme";
	const FOREIGN_TABLE  = "foreignTable";
	const JOIN_TYPE      = "joinType";
	/** @var Column $column */
	public $column;
	/** @var Table $foreignTable */
	public $foreignTable;
	/** @var Column $foreignColumn */
	public $foreignColumn;
	/** @var Join $joinType */
	public $join;
	/** @var string $class */
	public $class;
}