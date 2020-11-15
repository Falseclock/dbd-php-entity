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
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class HaveConstraintProperty extends Entity implements FullEntity, SyntheticEntity
{
    public $id;
    public $companyId;
    public $Company;
}

class HaveConstraintPropertyMap extends Mapper implements FullMapper
{
    /**
     * @var Column $id
     * @see Company::$id
     */
    public $id = [
        Column::NAME => "user_id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::KEY => true,
        Column::ORIGIN_TYPE => "int4"
    ];
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
}
