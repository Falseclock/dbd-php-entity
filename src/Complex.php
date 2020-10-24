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

/**
 * Class Complex is like JOIN table
 * Используется, когда к основному Entity джоинится таблица, не описанная в Entity
 *
 * @package Falseclock\DBD\Entity
 */
class Complex
{
    const ITERABLE = "isIterable";
    const TYPE = "typeClass";
    const NULLABLE = "nullable";
    /** @var string $type full class name with namespace */
    public $typeClass;
    /** @var bool $isIterable */
    public $isIterable = false;
    /** @var bool $nullable If no value is specified for a single-valued property, the Nullable attribute defaults to true. */
    public $nullable = true;

    /**
     * Complex constructor.
     *
     * @param null $embeddedNameOrArray
     */
    public function __construct($embeddedNameOrArray = null)
    {
        if (isset($embeddedNameOrArray)) {
            if (is_string($embeddedNameOrArray)) {
                $this->typeClass = $embeddedNameOrArray;
            } else {
                foreach ($embeddedNameOrArray as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }
}
