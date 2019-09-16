<?php

namespace Falseclock\DBD\Entity\Common;

use Exception;

class EntityException extends Exception
{
	// Переопределим исключение так, что параметр message станет обязательным
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	// Переопределим строковое представление объекта.
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}