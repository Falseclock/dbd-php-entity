<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2022] [Nurlan Mukhanov <nurike@gmail.com>]                      *
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

namespace DBD\Entity\Tests\Entities;

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\FullMapper;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class Currency extends Entity implements FullEntity
{
    const SCHEME = "handbook";
    const TABLE = "currencies";

    /** @var string */
    public $abbreviation;
    /** @var string */
    public $code;
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $shortName;
    /** @var string */
    public $symbol;
}

class CurrencyMap extends Mapper implements FullMapper
{
    const ANNOTATION = "Таблица валют";
    /** @var Column */
    public $abbreviation = [
        Column::NAME => "currency_abbreviation"
    ];
    /** @var Column */
    public $code = [
        Column::NAME => "currency_code"
    ];
    /** @var Column */
    public $id = [
        Column::NAME => "currency_id"
    ];
    /** @var Column */
    public $name = [
        Column::NAME => "currency_name"
    ];
    /** @var Column */
    public $shortName = [
        Column::NAME => "currency_short_name"
    ];
    /** @var Column */
    public $symbol = [
        Column::NAME => "currency_symbol"
    ];
}

