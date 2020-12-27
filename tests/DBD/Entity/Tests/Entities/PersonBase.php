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

class PersonBase extends Entity implements FullEntity
{
    const SCHEME = "public";
    const TABLE = "person";
    /**
     * @var string $email
     * @see PersonBaseMap::$email
     */
    public $email;
    /**
     * @var int $id
     * @see PersonBaseMap::$id
     */
    public $id;
    /**
     * @var bool $isActive
     * @see PersonBaseMap::$isActive
     */
    public $isActive;
    /**
     * @var string $name
     * @see PersonBaseMap::$name
     */
    public $name;
    /**
     * @var bool $registrationDate
     * @see PersonBaseMap::$registrationDate
     */
    public $registrationDate;
}

/**
 * Class PersonBaseMap
 * @package DBD\Entity\Tests\Entities
 * @property $iDoNotKnow
 */
class PersonBaseMap extends Mapper implements FullMapper
{
    const ANNOTATION = "Table description";
    /**
     * @var Column
     * @see PersonBase::$email
     */
    public $email = [
        Column::NAME => "person_email",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => false,
        Column::MAXLENGTH => 256,
        Column::ANNOTATION => "Email address",
        Column::ORIGIN_TYPE => "varchar",
    ];
    /**
     * @var Column
     * @see PersonBase::$id
     */
    public $id = [
        Column::NAME => "person_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::IS_AUTO => true,
        Column::NULLABLE => false,
        Column::ANNOTATION => "Unique ID",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4",
    ];
    /**
     * @var Column
     * @see PersonBase::$isActive
     */
    public $isActive = [
        Column::NAME => "person_is_active",
        Column::PRIMITIVE_TYPE => Primitive::Boolean,
        Column::DEFAULT => "true",
        Column::NULLABLE => false,
        Column::ANNOTATION => "Active status",
        Column::ORIGIN_TYPE => "bool",
    ];
    /**
     * @var Column
     * @see PersonBase::$name
     */
    public $name = [
        Column::NAME => "person_name",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::NULLABLE => true,
        Column::ANNOTATION => "Just name",
        Column::ORIGIN_TYPE => "text",
    ];
    /**
     * @var Column
     * @see PersonBase::$registrationDate
     */
    public $registrationDate = [
        Column::NAME => "person_registration_date",
        Column::PRIMITIVE_TYPE => Primitive::DateTimeOffset,
        Column::DEFAULT => "now()",
        Column::NULLABLE => false,
        Column::PRECISION => 6,
        Column::ANNOTATION => "Date of registration",
        Column::ORIGIN_TYPE => "timestamptz",
    ];
}
