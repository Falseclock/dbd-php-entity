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
    public function testEnforcerException(): void
    {
        self::expectException(EntityException::class);

        Enforcer::__add(__DIR__, __LINE__);
    }

    public function testExceptionOnEntity(): void
    {
        self::expectException(Error::class);
        self::expectExceptionCode(E_USER_ERROR);
        self::expectExceptionMessageMatches('/Undefined constant/');
        self::expectExceptionMessage('Undefined constant SCHEME in ' . WithoutConstants::class);

        new WithoutConstants();
    }

    public function testExceptionOnMapper(): void
    {
        self::expectException(Error::class);
        self::expectExceptionCode(E_USER_ERROR);
        self::expectExceptionMessageMatches('/Undefined constant/');
        self::expectExceptionMessage('Undefined constant ANNOTATION in ' . WithoutConstantsMap::class);

        // WithoutConstantsMap is declared in the WithoutConstants fixture file and is not PSR-4 discoverable on its own
        class_exists(WithoutConstants::class);

        WithoutConstantsMap::me();
    }

    /**
     * The Error is thrown directly by Enforcer: no user error handler is needed to observe it,
     * and it is a plain Error (not an EntityException) carrying E_USER_ERROR as its code.
     */
    public function testMissingConstantIsCatchableWithoutErrorHandler(): void
    {
        try {
            new WithoutConstants();
        } catch (Error $error) {
            self::assertSame(Error::class, get_class($error));
            self::assertSame(E_USER_ERROR, $error->getCode());
            self::assertSame('Undefined constant SCHEME in ' . WithoutConstants::class, $error->getMessage());

            return;
        }

        self::fail('Constructing an Entity without SCHEME/TABLE constants must throw Error');
    }

    /**
     * Regression for PHP 8.4+: trigger_error(E_USER_ERROR) emitted
     * "Passing E_USER_ERROR to trigger_error() is deprecated since 8.4" before the actual error.
     * Enforcer must raise no PHP error or deprecation at all, on any supported PHP version.
     */
    public function testMissingConstantDoesNotEmitPhpErrorsOrDeprecations(): void
    {
        // autoload everything involved first: compile-time diagnostics of other files (PHP 8.4 implicit nullable
        // parameters in Entity/EntityException) are not Enforcer's and must not leak into the capture window below
        class_exists(WithoutConstants::class);
        class_exists(Enforcer::class);

        $diagnostics = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$diagnostics): bool {
            $diagnostics[] = sprintf('[%d] %s', $errno, $errstr);

            return true;
        }, E_ALL);

        try {
            try {
                new WithoutConstants();
            } catch (Error) {
                // expected, see testMissingConstantIsCatchableWithoutErrorHandler()
            }
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $diagnostics);
    }
}
