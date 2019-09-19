<?php

namespace Falseclock\DBD\Entity;

class Embedded
{
	const NAME = "name";
	const TYPE = "type";
	/** @var string $name Column name in Entity class */
	public $name;
	/** @var string $type full class name with namespace */
	public $type;
}