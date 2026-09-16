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
use DBD\Entity\MapperAttributed;
use DBD\Entity\Tests\Entities\Attributed;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use DBD\Entity\Tests\Entities\PersonBaseWithoutMapping;
use DBD\Entity\Tests\Entities\Synthetic;
use DBD\Entity\Tests\Entities\SyntheticMap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Pins how Entity::map() resolves the mapper of an entity class and that resolved mappers are process-wide
 * singletons (legacy Mapper through Singleton, attribute mappers through a per-process cache).
 */
class MapperResolutionCharacterizationTest extends TestCase
{
    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testLegacyMapperResolvesToTheMapperSingleton(): void
    {
        self::assertSame(PersonBaseMap::me(), PersonBase::map());
        self::assertSame(PersonBase::map(), PersonBase::map());
    }

    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testSyntheticEntityResolvesItsMapperWithoutEnforcer(): void
    {
        // SyntheticMap does not override ANNOTATION; going through Mapper::me() would trip the Enforcer
        $map = Synthetic::map();

        self::assertInstanceOf(SyntheticMap::class, $map);
        self::assertSame(SyntheticMap::meWithoutEnforcer(), $map);
    }

    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testAttributeMapperIsBuiltOncePerProcess(): void
    {
        $first = Attributed::map();
        $second = Attributed::map();

        self::assertInstanceOf(MapperAttributed::class, $first);
        self::assertSame($first, $second);
        self::assertSame('AttributedMap', $first->name());
    }

    /**
     * @throws ReflectionException
     */
    public function testEntityWithoutMapClassAndWithoutAttributesThrows(): void
    {
        $this->expectException(EntityException::class);
        $this->expectExceptionMessage('does not have Map definition');

        PersonBaseWithoutMapping::map();
    }
}
