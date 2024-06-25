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

namespace DBD\Entity;

use Attribute;
use DBD\Entity\Common\EntityException;

/**
 * Use Complex when you need JOIN several tables and select all in once
 * Corresponding property with Entity type must be defined in base entity class
 *
 * @package DBD\Entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Complex
{
    const TYPE = "complexClass";
    /** @var string $complexClass full class name with namespace */
    public $complexClass;

    /**
     * Complex constructor.
     *
     * @param string|array $complexNameOrArray
     * @throws EntityException
     */
    public function __construct($complexNameOrArray)
    {
        if (isset($complexNameOrArray)) {
            if (is_string($complexNameOrArray)) {
                $this->complexClass = $complexNameOrArray;
            } else if (is_array($complexNameOrArray)) {
                foreach ($complexNameOrArray as $key => $value) {
                    $this->$key = $value;
                }
            } else {
                throw new EntityException("Complex constructor accepts only string or array");
            }
        }

        if (!isset($this->complexClass)) {
            throw new EntityException("Complex className not set");
        }
    }
}
