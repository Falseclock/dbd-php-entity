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
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class Tender extends Entity implements FullEntity
{
    const TABLE = "";
    const SCHEME = "";

    /**
     * @var string $tenderDatePublication
     * @see TenderMap::$tenderDatePublication
     */
    public $tenderDatePublication;

    /**
     * @var int $tenderId
     * @see TenderMap::$tenderId
     */
    public $tenderId;

    /**
     * @var int $userId
     * @see TenderMap::$userId
     */
    public $userId;
}

class TenderMap extends Mapper implements FullMapper
{
    const ANNOTATION = "";

    /**
     * @var Column $tenderDatePublication
     * @see Tender::$tenderDatePublication
     */
    public $tenderDatePublication = [
        Column::NAME => "tender_date_publication",
        Column::PRIMITIVE_TYPE => Primitive::DateTimeOffset,
        Column::NULLABLE => true,
        Column::PRECISION => 6,
        Column::ANNOTATION => "",
        Column::ORIGIN_TYPE => "timestamptz"
    ];

    /**
     * @var Column $tenderId
     * @see Tender::$tenderId
     */
    public $tenderId = [
        Column::NAME => "tender_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $userId
     * @see Tender::$userId
     */
    public $userId = [
        Column::NAME => "user_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "reference to user",
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Constraint $User
     */
    protected $User = [
        Constraint::LOCAL_COLUMN => "user_id",
        Constraint::FOREIGN_SCHEME => User::SCHEME,
        Constraint::FOREIGN_TABLE => User::TABLE,
        Constraint::FOREIGN_COLUMN => "user_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => User::class,
    ];
}
