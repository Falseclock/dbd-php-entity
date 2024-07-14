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

namespace DBD\Entity\Tests;

use DBD\Entity\Common\Enforcer;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Tests\Entities\WithoutConstants;
use DBD\Entity\Tests\Entities\WithoutConstantsMap;
use Error;
use PHPUnit\Framework\TestCase;

/**
 * Class EnforcerTest
 *
 * @package DBD\Entity\Tests
 */
class EnforcerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        set_error_handler(function($errno, $errstr) {
            throw new Error($errstr, $errno);
        }, E_ALL);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        restore_error_handler();
    }

    public function testEnforcerException()
    {
        self::expectException(EntityException::class);

        Enforcer::__add(__DIR__, __LINE__);
    }

    /**
     *
     */
    public function testExceptionOnEntity(): void
    {
        self::expectException(Error::class);
        self::expectExceptionCode(256);
        self::expectExceptionMessageMatches('/Undefined constant/');

        new WithoutConstants();
    }

    /**
     * @throws EntityException
     */
    public function testExceptionOnMapper(): void
    {
        self::expectException(Error::class);
        self::expectExceptionCode(256);
        self::expectExceptionMessageMatches('/Undefined constant/');

        WithoutConstantsMap::me();
    }
}
