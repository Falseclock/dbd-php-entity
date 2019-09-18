<?php

namespace Falseclock\DBD\Entity;

class Constraint
{
	const BASE_CLASS     = "class";
	const FOREIGN_COLUMN = "foreignColumn";
	const FOREIGN_SCHEME = "foreignScheme";
	const FOREIGN_TABLE  = "foreignTable";
	const JOIN_TYPE      = "join";
	const LOCAL_COLUMN   = "localColumn";
	const LOCAL_TABLE    = "localTable";
	/** @var Column $localColumn */
	public $localColumn;
	/** @var Table $localTable */
	public $localTable;
	/** @var Table $foreignTable */
	public $foreignTable;
	/** @var Column $foreignColumn */
	public $foreignColumn;
	/** @var Join $joinType */
	public $join;
	/** @var string $class */
	public $class;
}