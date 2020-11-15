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

class User extends Entity
{
    const TABLE = "";
    const SCHEME = "";

    /**
     * @var int $companyId
     * @see UserMap::$companyId
     */
    public $companyId;

    /**
     * @var int $personId
     * @see UserMap::$personId
     */
    public $personId;

    /**
     * @var int $id
     * @see UserMap::$id
     */
    public $id;
}

class UserMap extends Mapper implements FullMapper
{
    const ANNOTATION = "";

    /**
     * @var Column $companyId
     * @see User::$companyId
     */
    public $companyId = [
        Column::NAME => "company_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => true,
        Column::ANNOTATION => "Reference to companies",
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $personId
     * @see User::$personId
     */
    public $personId = [
        Column::NAME => "person_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "Reference to persons",
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Column $id
     * @see User::$id
     */
    public $id = [
        Column::NAME => "user_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ANNOTATION => "",
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];

    /**
     * @var Constraint $Company
     */
    protected $Company = [
        Constraint::LOCAL_COLUMN => "company_id",
        Constraint::FOREIGN_SCHEME => Company::SCHEME,
        Constraint::FOREIGN_TABLE => Company::TABLE,
        Constraint::FOREIGN_COLUMN => "company_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => Company::class,
    ];

    /**
     * @var Constraint $Person
     */
    protected $Person = [
        Constraint::LOCAL_COLUMN => "person_id",
        Constraint::FOREIGN_SCHEME => Person::SCHEME,
        Constraint::FOREIGN_TABLE => Person::TABLE,
        Constraint::FOREIGN_COLUMN => "person_id",
        Constraint::JOIN_TYPE => null,
        Constraint::BASE_CLASS => Person::class,
    ];
}
