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

use DBD\Entity\Common\MapperException;
use ReflectionClass;

/**
 * Class Join
 *
 * @package DBD\Entity
 */
class Join
{
    const MANY_TO_MANY = "manyToMany";
    const MANY_TO_ONE = "manyToOne";
    const ONE_TO_MANY = "oneToMany";
    const ONE_TO_ONE = "oneToOne";
    /** @var string $type */
    public $type;

    /**
     * Join constructor.
     *
     * @param $type
     *
     * @throws MapperException
     */
    public function __construct($type)
    {
        foreach ($this->getConstants() as $name => $value) {
            if ($value == $type) {
                $this->type = $type;

                return;
            }
        }
        throw new MapperException("Unknown join type {$type}");
    }

    /**
     * @return array
     */
    private function getConstants(): iterable
    {
        $r = new ReflectionClass(self::class);

        return $r->getConstants();
    }

    /**
     * @return string
     * @throws MapperException
     */
    public function getConstantName(): string
    {
        foreach ($this->getConstants() as $name => $value) {
            if ($value == $this->type) {
                return $name;
            }
        }
        throw new MapperException("Something strange happen");
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
