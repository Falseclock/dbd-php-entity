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

use DBD\Entity\Interfaces\OnlyDeclaredPropertiesEntity;
use DBD\Entity\Interfaces\SyntheticEntity;

/**
 * Class B should contains only $a1 and $a2
 * @package DBD\Entity\Tests\Entities\DeclarationChain
 */
class B extends A implements SyntheticEntity, OnlyDeclaredPropertiesEntity
{
    /**
     * @var string $a1
     * @see BMap::$a1
     */
    public $a1;
    /**
     * @var string $a2
     * @see BMap::$a2
     */
    public $a2;
}

class BMap extends AMap
{
}
