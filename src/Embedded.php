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
 * Class Embedded used when you generate value with view or with calculations
 * Should be always public when mapped in Mapper
 *
 * @package Falseclock\DBD\Entity
 */
class Embedded
{
    /** @var string MANDATORY option */
    public const DB_TYPE = "dbType";
    public const ENTITY_CLASS = "entityClass";
    public const IS_ITERABLE = "isIterable";
    public const NAME = "name";
    /** @var string $name name of the columns in view or selected with AS */
    public $name;
    /** @var bool $isIterable */
    public $isIterable = false;
    /** @var string $entityClass default empty. Will be converted to Entity if not null */
    public $entityClass;
    /** @var Type $dbType */
    public $dbType;

    /**
     * Embedded constructor.
     *
     * @param null $arrayOfValues
     */
    public function __construct($arrayOfValues = null)
    {
        if (isset($arrayOfValues)) {
            foreach ($arrayOfValues as $key => $value) {
                if (property_exists($this, $key))
                    $this->$key = $value;
            }
        }
    }
}
