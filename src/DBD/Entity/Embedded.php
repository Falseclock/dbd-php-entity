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

use DBD\Entity\Common\EntityException;

/**
 * Class Embedded used when you generate value with view or with calculations or need
 * to decode JSON value or get iterable property
 * Should be always public when mapped in Mapper
 *
 * @package DBD\Entity
 */
class Embedded
{
    /** @var string MANDATORY option */
    public const DB_TYPE = "embeddedDbType";
    public const ENTITY_CLASS = "embeddedEntityClass";
    public const IS_ITERABLE = "embeddedIsIterable";
    /** @var string set to FALSE if you want avoid exceptions for Strictly Filled Entity */
    public const NAME = "embeddedName";
    /** @var string $name name of the columns in view or selected with AS. Set to FALSE if you don't gonna pass data, but wont set data manually */
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
     * @param array|null $arrayOfValues
     * @throws EntityException
     */
    public function __construct(array $arrayOfValues)
    {
        foreach ($arrayOfValues as $key => $value) {
            switch ($key) {
                case self::DB_TYPE:
                    $this->dbType = $value;
                    break;
                case self::ENTITY_CLASS:
                    $this->entityClass = $value;
                    break;
                case self::IS_ITERABLE:
                    $this->isIterable = $value;
                    break;
                case self::NAME:
                    $this->name = $value;
                    break;
            }
        }

        if (is_null($this->name))
            throw new EntityException("Embedded name not set");
    }
}
