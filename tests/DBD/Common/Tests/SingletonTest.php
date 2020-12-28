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

namespace DBD\Common\Tests;

use DBD\Common\Tests\Fixtures\BadSingleTone;
use DBD\Entity\Common\EntityException;
use PHPUnit\Framework\TestCase;

class SingletonTest extends TestCase
{
    /**
     */
    public function testClone()
    {
        $test = BadSingleTone::me();

        self::expectException(EntityException::class);

        /** @noinspection PhpExpressionResultUnusedInspection */
        $test->tryToClone();
    }
}
