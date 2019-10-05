<?php

namespace DBD\Entity;

use Exception;

final class Order
{
	const ASCENDING  = "ASC";
	const DESCENDING = "DESC";

	/**
	 * Order constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		throw new Exception("Cannot create instance of static util class");
	}
}