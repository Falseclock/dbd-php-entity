<?php
/**
 * <description should be written here>
 *
 * @author       Written by Nurlan Mukhanov <nurike@gmail.com>, March 2020
 */

namespace DBD\Entity;

use ReflectionProperty;

final class MapperVariables
{
	public $columns;
	public $complex;
	public $constraints;
	public $embedded;
	public $otherColumns;

	/**
	 * MapperVariables constructor.
	 *
	 * @param $columns
	 * @param $constraints
	 * @param $otherColumns
	 * @param $embedded
	 * @param $complex
	 */
	public function __construct($columns, $constraints, $otherColumns, $embedded, $complex) {
		$this->columns = $this->filter($columns);
		$this->constraints = $this->filter($constraints);
		$this->otherColumns = $this->filter($otherColumns);
		$this->embedded = $this->filter($embedded);
		$this->complex = $this->filter($complex);
	}

	/**
	 * @param ReflectionProperty[] $vars
	 *
	 * @return array
	 */
	private function filter(array $vars) {
		$list = [];
		foreach($vars as $varName => $varValue) {
			$list[] = $varName;
		}

		return $list;
	}
}
