<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Common\DBDException;

final class Order
{
	const ASCENDING  = "ASC";
	const DESCENDING = "DESC";

	public function __construct() {
		throw new DBDException("Cannot create instance of static util class");
	}
}