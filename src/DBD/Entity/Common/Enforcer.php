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

declare(strict_types=1);

namespace DBD\Entity\Common;

use ReflectionClass;
use ReflectionException;

/**
 * Class Enforcer
 *
 * @package DBD\Entity\Common
 */
class Enforcer
{
    /**
     * @param $class
     * @param $c
     *
     * @throws EntityException
     */
    public static function __add($class, $c)
    {
        try {
            $reflection = new ReflectionClass($class);
            $constantsForced = $reflection->getConstants();
            foreach ($constantsForced as $constant => $value) {
                if (constant("$c::$constant") == "abstract") {
                    trigger_error(sprintf("Undefined constant %s in %s", $constant, $c), E_USER_ERROR);
                }
            }
        } catch (ReflectionException $e) {
            throw new EntityException($e->getMessage());
        }
    }
}
