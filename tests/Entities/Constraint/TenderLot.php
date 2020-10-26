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

class TenderLot extends Entity implements FullEntity
{
    const TABLE = "";
    const SCHEME = "";

    /**
     * @var int $tenderId
     * @see TenderLotMap::$tenderId
     */
    public $tenderId;

    /**
     * @var int $id
     * @see TenderLotMap::$id
     */
    public $id;

    /**
     * @var string $name
     * @see TenderLotMap::$name
     */
    public $name;
}

class TenderLotMap extends Mapper implements FullMapper
{
    const ANNOTATION = "";

    /**
     * @var Column $tenderId
     * @see TenderLot::$tenderId
     */
    public $tenderId = [
        Column::NAME => "tender_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "Reference to Tender",
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $id
     * @see TenderLot::$id
     */
    public $id = [
        Column::NAME => "tender_lot_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $name
     * @see TenderLot::$name
     */
    public $name = [
        Column::NAME => "tender_lot_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::MAXLENGTH => 512,
        Column::ANNOTATION => "",
        Column::ORIGIN_TYPE => "varchar"
    ];

    /**
     * @var Constraint $Tender
     */
    protected $Tender = [
        Constraint::LOCAL_COLUMN => "tender_id",
        Constraint::FOREIGN_SCHEME => Tender::SCHEME,
        Constraint::FOREIGN_TABLE => Tender::TABLE,
        Constraint::FOREIGN_COLUMN => "tender_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => Tender::class,
    ];
}
