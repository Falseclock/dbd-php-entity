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

namespace DBD\Entity\Tests\Entities\Characterization;

use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

/**
 * Entity exposing getter-backed virtual properties (accessed through Entity::__get()).
 *
 * @property string $computed
 * @property mixed $nothing
 */
class WithGetter extends Entity implements SyntheticEntity
{
    /** @var int how many times getComputed() has been invoked; tests reset it */
    public static int $getterCalls = 0;

    public $id;

    public function getComputed(): string
    {
        self::$getterCalls++;

        return sprintf('computed-%s-%d', $this->id, self::$getterCalls);
    }

    public function getNothing()
    {
        return null;
    }
}

class WithGetterMap extends Mapper
{
    public $id = [
        Column::NAME => 'with_getter_id',
        Column::PRIMITIVE_TYPE => Primitive::Int32,
        Column::ORIGIN_TYPE => 'int4',
    ];
}
