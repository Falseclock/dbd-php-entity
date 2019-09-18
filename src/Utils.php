<?php

namespace Falseclock\DBD\Entity;

class Utils
{
	public static function arrayDiff(array $bigArray, array $smallArray) {
		foreach($smallArray as $key => $value) {
			if(isset($bigArray[$key])) {
				unset($bigArray[$key]);
			}
		}

		return $bigArray;
	}

	public static function getClassVars(string $class) {
		return get_class_vars($class);
	}

	public static function getObjectVars($object) {
		return get_object_vars($object);
	}
}