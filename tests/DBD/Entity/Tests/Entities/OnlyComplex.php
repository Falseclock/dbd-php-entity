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

namespace DBD\Entity\Tests\Entities;

use DBD\Entity\Complex;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;

class OnlyComplex extends Entity implements SyntheticEntity, StrictlyFilledEntity
{
    /**
     * @var Address $Address
     * @see OnlyComplexMap::$Address
     */
    public $Address;
    /**
     * @var PersonBase $Person
     * @see OnlyComplexMap::$Person
     */
    public $Person;
}

class OnlyComplexMap extends Mapper
{
    /**
     * @see OnlyComplex::$Address
     * @var Complex
     */
    protected $Address = [
        Complex::TYPE => Address::class,
    ];

    /**
     * @see OnlyComplex::$Person
     * @var Complex
     */
    protected $Person = [
        Complex::TYPE => PersonBase::class,
    ];
}
