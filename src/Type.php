<?php

namespace Falseclock\DBD\Entity;

use MyCLabs\Enum\Enum;

/**
 * Class Type describes DB types
 *
 * @package Falseclock\DBD\Entity
 * @method static Type Json()
 * @method static Type Varchar()
 * @method static Type Char()
 */
class Type extends Enum
{
	public const Char    = "char";
	public const Json    = "json";
	public const Varchar = "varchar";
}