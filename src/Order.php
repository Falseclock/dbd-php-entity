<?php

namespace DBD\Entity;

use Exception;

final class Order
{
	const ASCENDING             = "ASC";
	const ASCENDING_NULLS_LAST  = "ASC NULLS LAST";
	const DESCENDING            = "DESC";
	const DESCENDING_NULLS_LAST = "DESC NULLS LAST";

	/**
	 * Order constructor.
	 *
	 * @throws Exception
	 */
	public function __construct() {
		throw new Exception("Cannot create instance of static util class");
	}
}