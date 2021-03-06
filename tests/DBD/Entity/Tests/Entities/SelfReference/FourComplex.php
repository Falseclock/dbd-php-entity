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

namespace DBD\Entity\Tests\Entities\SelfReference;

use DBD\Entity\Column;
use DBD\Entity\Complex;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;

class FourComplex extends Entity implements SyntheticEntity
{
    /**
     * @var OneComplex
     */
    public $OneComplex;
    /**
     * @var ThreeComplex
     */
    public $ThreeComplex;
    /**
     * @var TwoComplex
     */
    public $TwoComplex;
    public $id;
}

class FourComplexMap extends Mapper
{
    /**
     * @var Column
     */
    public $id = [
        Column::NAME => 'four_id',
    ];
    /**
     * @var Complex
     */
    protected $OneComplex = [
        Complex::TYPE => OneComplex::class,
    ];
    /**
     * @var Complex
     */
    protected $ThreeComplex = [
        Complex::TYPE => ThreeComplex::class,
    ];
    /**
     * @var Complex
     */
    protected $TwoComplex = [
        Complex::TYPE => TwoComplex::class,
    ];
}
