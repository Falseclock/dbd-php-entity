<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2020] [Nurlan Mukhanov <nurike@gmail.com>]                      *
 *                                                                              *
 *   Licensed under the Apache License, Version 2.0 (the "License");            *
 *   you may not use this file except in compliance with the License.           *
 *   You may obtain a copy of the License at                                    *
 *                                                                              *
 *       http://www.apache.org/licenses/LICENSE-2.0                             *
 *                                                                              *
 *   Unless required by applicable law or agreed to in writing, software        *
 *   distributed under the License is distributed on an "AS IS" BASIS,          *
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.   *
 *   See the License for the specific language governing permissions and        *
 *   limitations under the License.                                             *
 *                                                                              *
 ********************************************************************************/

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
