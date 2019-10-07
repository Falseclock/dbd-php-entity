<?php

namespace DBD\Entity\Common;

class Utils
{
	/**
	 * @param array $bigArray
	 * @param array $smallArray
	 *
	 * @return array
	 */
	public static function arrayDiff(array $bigArray, array $smallArray) {
		foreach($smallArray as $key => $value) {
			if(isset($bigArray[$key])) {
				unset($bigArray[$key]);
			}
		}

		return $bigArray;
	}

	/**
	 * @param string $class
	 *
	 * @return array
	 */
	public static function getClassVars(string $class) {
		return get_class_vars($class);
	}

	/**
	 * @param $object
	 *
	 * @return array
	 */
	public static function getObjectVars($object) {
		return get_object_vars($object);
	}
}