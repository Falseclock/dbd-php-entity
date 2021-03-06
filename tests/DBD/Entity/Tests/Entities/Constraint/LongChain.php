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

namespace DBD\Entity\Tests\Entities\Constraint;

use DBD\Entity\Column;
use DBD\Entity\Constraint;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Primitive;

class LongChain extends User implements SyntheticEntity
{
    /**
     * @var LevelOne $LevelOne
     * @see LongChainMap::$LevelOne
     */
    public $LevelOne;
}

class LongChainMap extends UserMap
{
    /**
     * @var Column $levelOneId
     */
    public $levelOneId = [
        Column::NAME => "level1_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "Reference to level1",
        Column::ORIGIN_TYPE => "int4"
    ];
    /**
     * @var Constraint $LevelOne
     */
    protected $LevelOne = [
        Constraint::LOCAL_COLUMN => "level1_id",
        Constraint::FOREIGN_SCHEME => LevelOne::SCHEME,
        Constraint::FOREIGN_TABLE => LevelOne::TABLE,
        Constraint::FOREIGN_COLUMN => "level1_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => LevelOne::class,
    ];
}
