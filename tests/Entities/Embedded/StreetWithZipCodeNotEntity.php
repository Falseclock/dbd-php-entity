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

namespace DBD\Entity\Tests\Entities\Embedded;

use DBD\Entity\Embedded;
use DBD\Entity\Interfaces\StrictlyFilledEntity;

class StreetWithZipCodeNotEntity extends StreetWithZipCode implements StrictlyFilledEntity
{
    /**
     * @var ZipCode $ZipCode
     * @see StreetWithZipCodeNotEntityMap::$ZipCode
     */
    public $ZipCode;
}

/**
 * Class StreetWithZipCodeNotEntityMap
 * @package DBD\Entity\Tests\Entities\Embedded
 * @property Embedded $ZipCode
 */
class StreetWithZipCodeNotEntityMap extends StreetWithZipCodeMap
{
    /**
     * @var Embedded
     * @see StreetWithZipCodeNotEntity::$ZipCode
     */
    protected $ZipCode = [
        Embedded::NAME => "street_zip_code",
        Embedded::IS_ITERABLE => false,
    ];
}
