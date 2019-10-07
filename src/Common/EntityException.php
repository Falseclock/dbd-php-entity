<?php

namespace DBD\Entity\Common;

use Exception;

class EntityException extends Exception
{
	/**
	 * EntityException constructor.
	 * Переопределим исключение так, что параметр message станет обязательным
	 *
	 * @param                $message
	 * @param int            $code
	 * @param Exception|null $previous
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Переопределим строковое представление объекта.
	 *
	 * @return string
	 */
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}