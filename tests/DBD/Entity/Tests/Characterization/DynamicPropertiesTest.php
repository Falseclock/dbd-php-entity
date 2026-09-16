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

namespace DBD\Entity\Tests\Characterization;

use DBD\Entity\Common\EntityException;
use DBD\Entity\Tests\Entities\Characterization\AttributedPlain;
use DBD\Entity\Tests\Entities\Characterization\WithGetter;
use PHPUnit\Framework\TestCase;

/**
 * Characterizes the getter memoization implemented by Entity::__get() and the dynamic properties
 * created by MapperAttributed, and guards both against PHP 8.2+ "Creation of dynamic property" deprecations.
 */
class DynamicPropertiesTest extends TestCase
{
    /** @var string[] */
    private array $deprecations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deprecations = [];
        $previous = null;
        $previous = set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use (&$previous): bool {
            if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
                $this->deprecations[] = sprintf('%s @ %s:%d', $errstr, basename($errfile), $errline);

                return true;
            }

            return $previous !== null ? (bool)$previous($errno, $errstr, $errfile, $errline) : false;
        });
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }

    /**
     * @throws EntityException
     */
    public function testGetterResultIsMemoizedAsRealProperty(): void
    {
        WithGetter::$getterCalls = 0;
        $entity = new WithGetter(['with_getter_id' => 7]);

        self::assertFalse(property_exists($entity, 'computed'));
        self::assertFalse(isset($entity->computed));

        $first = $entity->computed;

        self::assertSame('computed-7-1', $first);
        self::assertSame(1, WithGetter::$getterCalls);

        // The value is stored in a real (dynamic) property ...
        self::assertTrue(property_exists($entity, 'computed'));
        self::assertTrue(isset($entity->computed));
        self::assertSame(['id', 'computed'], array_keys(get_object_vars($entity)));
        self::assertSame('{"id":7,"computed":"computed-7-1"}', json_encode($entity));

        // ... so the getter is not invoked again
        self::assertSame($first, $entity->computed);
        self::assertSame(1, WithGetter::$getterCalls);

        // ... survives serialization without re-invoking the getter
        $copy = unserialize(serialize($entity));
        self::assertSame($first, $copy->computed);
        self::assertSame(1, WithGetter::$getterCalls);

        // ... and unset() re-arms the getter
        unset($entity->computed);
        self::assertSame('computed-7-2', $entity->computed);
        self::assertSame(2, WithGetter::$getterCalls);
    }

    /**
     * @throws EntityException
     */
    public function testCapitalizedAccessCreatesSeparateProperty(): void
    {
        $entity = new WithGetter(['with_getter_id' => 8]);

        self::assertSame('computed-8-' . (WithGetter::$getterCalls + 1), $entity->Computed);
        self::assertTrue(property_exists($entity, 'Computed'));
        self::assertFalse(property_exists($entity, 'computed'));
    }

    /**
     * @throws EntityException
     */
    public function testNullGetterResultIsStoredButNotIsset(): void
    {
        $entity = new WithGetter(['with_getter_id' => 9]);

        self::assertNull($entity->nothing);
        self::assertTrue(property_exists($entity, 'nothing'));
        self::assertFalse(isset($entity->nothing));
        self::assertArrayHasKey('nothing', get_object_vars($entity));
    }

    public function testMissingGetterThrows(): void
    {
        $entity = new WithGetter(['with_getter_id' => 10]);

        $this->expectException(EntityException::class);
        $this->expectExceptionMessage("Can't find property or getter method for '\$missing'");

        /** @noinspection PhpUndefinedFieldInspection */
        $entity->missing;
    }

    /**
     * Regression: on PHP 8.2+ the memoization must not emit "Creation of dynamic property ... is deprecated".
     *
     * @throws EntityException
     */
    public function testGetterMemoizationDoesNotEmitDeprecations(): void
    {
        $entity = new WithGetter(['with_getter_id' => 11]);

        $entity->computed;

        self::assertSame([], $this->deprecations);
    }

    /**
     * Regression: MapperAttributed stores Column/Constraint/Embedded/Complex definitions as dynamic properties
     * (accessed like $map->id->name); on PHP 8.2+ this must not emit deprecations either.
     *
     * AttributedPlain is used by this test only, so its mapper is built here for the first time.
     *
     * @throws EntityException
     */
    public function testAttributedMapperDoesNotEmitDeprecations(): void
    {
        $map = AttributedPlain::map();

        self::assertSame('attributed_plain_id', $map->id->name);
        self::assertSame('attributed_plain_title', $map->title->name);
        self::assertSame([], $this->deprecations);
    }
}
