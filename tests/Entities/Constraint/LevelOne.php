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
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class LevelOne extends Entity implements StrictlyFilledEntity
{
    public const TABLE = "public";
    public const SCHEME = "level1";
    /**
     * @var int $id
     * @see LevelOneMap::$id
     */
    public $id;
    /**
     * @var LevelTwo $LevelTwo
     * @see LevelOneMap::$LevelTwo
     */
    public $LevelTwo;

    /**
     * @var int $levelTwoId
     * @see LevelOneMap::$levelTwoId
     */
    public $levelTwoId;
}

class LevelOneMap extends Mapper
{
    public const ANNOTATION = "";

    /**
     * @var Column $id
     * @see LevelOne::$id
     */
    public $id = [
        Column::NAME => "level1_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $levelTwoId
     */
    public $levelTwoId = [
        Column::NAME => "level2_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Constraint $LevelTwo
     * @see LevelOne::$LevelTwo
     */
    protected $LevelTwo = [
        Constraint::LOCAL_COLUMN => "level2_id",
        Constraint::FOREIGN_SCHEME => LevelTwo::SCHEME,
        Constraint::FOREIGN_TABLE => LevelTwo::TABLE,
        Constraint::FOREIGN_COLUMN => "level2_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => LevelTwo::class,
    ];
}
