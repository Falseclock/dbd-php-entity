<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 **************************************************************************/

declare(strict_types=1);

namespace DBD\Common;

use DBD\Entity\Common\EntityException;

abstract class Singleton implements Instantiatable
{
    /**
     * Все вызванные ранее инстансы классов
     *
     * @var array $instances
     */
    private static $instances = [];

    /**
     * Singleton constructor. You can't create me
     */
    private function __construct()
    {
    }

    /**
     * @return Instantiatable|Singleton|static
     */
    public static function me(): Instantiatable
    {
        return self::getInstance(get_called_class());
    }

    /**
     * Функция получения инстанса класса
     *
     * @param string $class
     *
     * @return Singleton
     */
    final public static function getInstance(string $class): Singleton
    {
        if (!isset(self::$instances[$class])) {
            $object = new $class;

            return self::$instances[$class] = $object;
        } else {
            return self::$instances[$class];
        }
    }

    /**
     * do not clone me
     * @throws EntityException
     */
    protected function __clone()
    {
        throw new EntityException("Singleton classes should not be cloned");
    }
}
