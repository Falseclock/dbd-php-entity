<?php
/*************************************************************************************
 *   MIT License                                                                     *
 *                                                                                   *
 *   Copyright (C) 2020 by Nurlan Mukhanov <nurike@gmail.com>                        *
 *                                                                                   *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy    *
 *   of this software and associated documentation files (the "Software"), to deal   *
 *   in the Software without restriction, including without limitation the rights    *
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell       *
 *   copies of the Software, and to permit persons to whom the Software is           *
 *   furnished to do so, subject to the following conditions:                        *
 *                                                                                   *
 *   The above copyright notice and this permission notice shall be included in all  *
 *   copies or substantial portions of the Software.                                 *
 *                                                                                   *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR      *
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,        *
 *   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE    *
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER          *
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,   *
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE   *
 *   SOFTWARE.                                                                       *
 ************************************************************************************/

namespace DBD\Entity;

use DBD\Entity\Common\EntityException;
use ReflectionException;

/**
 * Class View is mainly used for fetching data from DB views.
 * If view does not imply some columns, you can unset it in construction method
 * Actually this is not good way of programming and you should declare base Entity class and extend it further
 *
 * @package Falseclock\DBD\Entity
 */
abstract class View extends Entity
{
    /**
     * View constructor.
     *
     * @param null $data
     * @param int $maxLevels
     * @param int $currentLevel
     *
     * @throws EntityException
     * @throws ReflectionException
     */
    public function __construct($data = null, int $maxLevels = 2, int $currentLevel = 1)
    {
        parent::__construct($data, $maxLevels, $currentLevel);
    }

    /**
     * Special magic method to avoid getting of unset properties in construction method
     *
     * @param $name
     *
     * @return mixed
     * @throws EntityException
     */
    function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new EntityException(sprintf("Property '%s' of '%s' not exist or unset in constructor", $name, get_class($this)));
        }

        return $this->$name;
    }

    /**
     * Special magic method to avoid setting of unset properties in construction method
     *
     * @param $name
     * @param $value
     *
     * @throws EntityException
     */
    function __set($name, $value)
    {
        if (!property_exists($this, $name)) {

            throw new EntityException(sprintf("Property '%s' of '%s' not exist or unset in constructor", $name, get_class($this)));
        }

        $this->$name = $value;
    }
}
