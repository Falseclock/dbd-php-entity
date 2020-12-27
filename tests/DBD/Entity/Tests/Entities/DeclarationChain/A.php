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

namespace DBD\Entity\Tests\Entities\DeclarationChain;

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class A extends Entity implements SyntheticEntity, FullEntity
{
    /**
     * @var string $a1
     * @see AMap::$a1
     */
    public $a1;
    /**
     * @var string $a2
     * @see AMap::$a2
     */
    public $a2;
    /**
     * @var string $a3
     * @see AMap::$a3
     */
    public $a3;
}

class AMap extends Mapper implements FullMapper
{
    /**
     * @var Column $a1
     * @see A::$a1
     */
    public $a1 = [
        Column::NAME => "a1",
        Column::PRIMITIVE_TYPE => Primitive::String,
    ];
    /**
     * @var Column $a2
     * @see A::$a2
     */
    public $a2 = [
        Column::NAME => "a2",
        Column::PRIMITIVE_TYPE => Primitive::String,
    ];
    /**
     * @var Column $a3
     * @see A::$a3
     */
    public $a3 = [
        Column::NAME => "a3",
        Column::PRIMITIVE_TYPE => Primitive::String,
    ];
}
