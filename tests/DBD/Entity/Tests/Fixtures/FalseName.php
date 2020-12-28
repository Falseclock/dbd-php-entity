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

declare(strict_types=1);

namespace DBD\Entity\Tests\Fixtures;

use DBD\Entity\Column;
use DBD\Entity\Embedded;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;
use DBD\Entity\Type;
use stdClass;

class FalseName extends Entity implements SyntheticEntity, StrictlyFilledEntity
{
    /** @var int */
    public $id;
    /** @var string */
    public $debug;
    /** @var stdClass */
    public $json;

    /**
     * @param string $value
     */
    public function setJson(string $value)
    {
        $this->json = json_decode($value, false);
    }
}

class FalseNameMap extends Mapper
{
    /**
     * @var Column $gid
     * @see Session::$gid
     */
    public $id = [
        Column::NAME => "id",
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::NULLABLE => false,
        Column::ORIGIN_TYPE => "varchar",
    ];

    /**  @var Embedded */
    protected $debug = [
        Embedded::NAME => false,
        Embedded::DB_TYPE => Type::Varchar,
    ];

    /**  @var Embedded */
    protected $json = [
        Embedded::NAME => 'setter',
        Embedded::DB_TYPE => Type::Varchar,
    ];
}
