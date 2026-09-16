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
use DBD\Entity\Complex;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;

/**
 * Self-referencing entity (through Complex) that counts postProcessing() invocations.
 */
class WithPostProcessing extends Entity implements SyntheticEntity
{
    /** @var int how many times postProcessing() has been invoked; tests reset it */
    public static int $postProcessingCalls = 0;

    public $id;
    public $Child;

    protected function postProcessing(): void
    {
        self::$postProcessingCalls++;
    }
}

class WithPostProcessingMap extends Mapper
{
    public $id = [
        Column::NAME => 'pp_id',
    ];

    protected $Child = [
        Complex::TYPE => WithPostProcessing::class,
    ];
}
