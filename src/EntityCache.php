<?php
/**
 * <description should be written here>
 *
 * @author       Written by Nurlan Mukhanov <nurike@gmail.com>, March 2020
 */

namespace DBD\Entity;

/**
 * Абстрактный класс для моделирования объектов данных. Все поля должны быть замаплены через переменную map
 */
abstract class EntityCache
{
	const ARRAY_MAP           = "arrayMap";
	const ARRAY_REVERSE_MAP   = "reverseMap";
	const DECLARED_PROPERTIES = "declaredProperties";
	const UNSET_PROPERTIES    = "unsetProperties";
	public static $mapCache = [];
}