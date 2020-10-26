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

use DBD\Entity\Column;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Common\MapperException;
use DBD\Entity\Tests\Entities\Constraint\CountryMap;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use DBD\Entity\Tests\Fixtures\MapperBoolProperty;
use DBD\Entity\Tests\Fixtures\MapperEmptyProperty;
use DBD\Entity\Tests\Fixtures\MapperNullProperty;
use DBD\Entity\Tests\Fixtures\MapperWithTwoKeys;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class MapperTest extends TestCase
{
    public function testNullProperty()
    {
        $this->expectException(MapperException::class);
        MapperNullProperty::me();
    }

    public function testBoolProperty()
    {
        $this->expectException(MapperException::class);
        MapperBoolProperty::me();
    }

    public function testEmptyProperty()
    {
        $this->expectException(MapperException::class);
        MapperEmptyProperty::me();
    }

    public function testUnknownProperty()
    {
        $this->expectException(MapperException::class);
        PersonBaseMap::me()->iDoNotKnow;
    }

    /**
     * @throws MapperException
     * @throws EntityException
     * @throws ReflectionException
     * @covers MapperCache::me
     */
    public function testPrimaryKeys()
    {
        $keys = MapperWithTwoKeys::me()->getPrimaryKey();

        self::assertCount(2, $keys);

        foreach ($keys as $key) {
            self::assertInstanceOf(Column::class, $key);
        }
    }

    public function testVarNameByColumn()
    {
        $primaryKeys = PersonBaseMap::me()->getPrimaryKey();
        $key = array_key_first($primaryKeys);
        $column = array_shift($primaryKeys);
        self::assertInstanceOf(Column::class, $column);
        self::assertEquals(PersonBaseMap::me()->id->name, $column->name);

        array_unshift($primaryKeys, $column);

        self::assertEquals($key, PersonBaseMap::me()->getVarNameByColumn($column));

        $columns = CountryMap::me()->getColumns();
        self::assertInstanceOf(Column::class, array_shift($columns));

        $this->expectException(MapperException::class);
        PersonBaseMap::me()->getVarNameByColumn(array_shift($columns));
    }

    public function testFindColumnByOriginName()
    {
        $mapper = PersonBaseMap::me();
        $this->expectException(MapperException::class);
        $mapper->findColumnByOriginName("unknown field");
    }
}
