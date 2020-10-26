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

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class Address extends Entity implements FullEntity
{
    const SCHEME = "Table scheme";
    const TABLE = "Table name";
    /**
     * @var $int $id
     * @see AddressMap::$id
     */
    public $id;
    /**
     * @var string $street
     * @see AddressMap::$street
     */
    public $street;
}

class AddressMap extends Mapper implements FullMapper
{
    const ANNOTATION = "Table description";

    /**
     * @var Column $id
     * @see Address::$id
     */
    public $id = [
        Column::NAME => "address_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::IS_AUTO => true,
        Column::NULLABLE => false,
        Column::ANNOTATION => "Unique ID",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4",
    ];

    /**
     * @var Column $street
     * @see Address::$street
     */
    public $street = [
        Column::NAME => "address_street",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => true,
        Column::ANNOTATION => "Just text",
        Column::ORIGIN_TYPE => "text",
    ];
}
