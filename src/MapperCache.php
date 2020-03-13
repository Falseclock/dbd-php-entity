<?php
/**
 * <description should be written here>
 *
 * @author       Written by Nurlan Mukhanov <nurike@gmail.com>, March 2020
 */

namespace DBD\Entity;

use DBD\Common\Singleton;

/**
 * Class MapperCache used to avoid interfering with local variables in child classes
 *
 * @package Falseclock\DBD\Entity
 */
class MapperCache extends Singleton
{
	/** @var array $allVariables */
	public $allVariables = [];
	/** @var array $baseColumns */
	public $baseColumns = [];
	/** @var array $columns */
	public $columns = [];
	/** @var array $complex */
	public $complex = [];
	/** @var array $constraints */
	public $constraints = [];
	/** @var array $embedded */
	public $embedded = [];
	/** @var array $fullyInstantiated */
	public $fullyInstantiated = [];
	/** @var array $originFieldNames */
	public $originFieldNames = [];
	/** @var array $otherColumns */
	public $otherColumns = [];
	/** @var array $table */
	public $table = [];
}
