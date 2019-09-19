<?php

namespace Falseclock\DBD\Entity;

class ConstraintRaw extends Constraint
{
	const BASE_CLASS     = "class";
	const FOREIGN_COLUMN = "foreignColumn";
	const FOREIGN_SCHEME = "foreignScheme";
	const FOREIGN_TABLE  = "foreignTable";
	const JOIN_TYPE      = "join";
	const LOCAL_COLUMN   = "localColumn";
	const LOCAL_TABLE    = "localTable";
	/** @var string $localColumn */
	public $localColumn;
	/** @var string $localTable */
	public $localTable;
	/** @var string $foreignTable */
	public $foreignTable;
	/** @var string $foreignColumn */
	public $foreignColumn;
	/** @var string $joinType */
	public $join;
	/** @var string $class */
	public $class;

	public function __construct(?array $constraint = null) {
		if(isset($constraint)) {
			foreach($constraint as $key => $value) {
				$this->$key = $value;
			}
		}
	}
}