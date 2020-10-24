<?php
/*************************************************************************************
 *   MIT License                                                                     *
 *                                                                                   *
 *   Copyright (C) 2020 by Nurlan Mukhanov <nurike@gmail.com>                        *
 *                                                                                   *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy    *
 *   of this software and associated documentation files (the "Software"), to deal   *
 *   in the Software without restriction, including without limitation the rights    *
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell       *
 *   copies of the Software, and to permit persons to whom the Software is           *
 *   furnished to do so, subject to the following conditions:                        *
 *                                                                                   *
 *   The above copyright notice and this permission notice shall be included in all  *
 *   copies or substantial portions of the Software.                                 *
 *                                                                                   *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR      *
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,        *
 *   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE    *
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER          *
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,   *
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE   *
 *   SOFTWARE.                                                                       *
 ************************************************************************************/

namespace DBD\Entity\Tests\Entities;

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class Person extends Entity implements FullEntity
{
    const SCHEME = "public";
    const TABLE = "person";
    /**
     * @var string $email
     * @see PersonMap::$email
     */
    public $email;
    /**
     * @var int $id
     * @see PersonMap::$id
     */
    public $id;
    /**
     * @var bool $isActive
     * @see PersonMap::$isActive
     */
    public $isActive;
    /**
     * @var string $name
     * @see PersonMap::$name
     */
    public $name;
    /**
     * @var bool $registrationDate
     * @see PersonMap::$registrationDate
     */
    public $registrationDate;
}

class PersonMap extends Mapper implements FullMapper
{
    const ANNOTATION = "Table description";
    /**
     * @var Column
     * @see Person::$email
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
     * @see Person::$id
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
     * @see Person::$isActive
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
     * @see Person::$name
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
     * @see Person::$registrationDate
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
