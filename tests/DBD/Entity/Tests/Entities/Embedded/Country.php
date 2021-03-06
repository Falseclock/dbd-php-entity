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

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class Country extends Entity implements FullEntity
{
    const TABLE = "";
    const SCHEME = "";

    /**
     * @var int $id
     * @see CountryMap::$id
     */
    public $id;

    /**
     * @var string $name
     * @see CountryMap::$name
     */
    public $name;
}

class CountryMap extends Mapper implements FullMapper
{
    const ANNOTATION = "";

    /**
     * @var Column $id
     * @see Country::$id
     */
    public $id = [
        Column::NAME => "country_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $name
     * @see Country::$name
     */
    public $name = [
        Column::NAME => "country_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::MAXLENGTH => 255,
        Column::ANNOTATION => "",
        Column::ORIGIN_TYPE => "varchar"
    ];
}
