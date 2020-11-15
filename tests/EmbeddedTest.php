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

use DBD\Entity\Common\EntityException;
use DBD\Entity\Common\MapperException;
use DBD\Entity\Embedded;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Tests\Entities\Constraint\Person;
use DBD\Entity\Tests\Entities\Embedded\CountryWithRegions;
use DBD\Entity\Tests\Entities\Embedded\Region;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCode;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCodeNotEntity;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCodeNotJson;
use DBD\Entity\Tests\Entities\Embedded\ZipCode;
use DBD\Entity\Tests\Entities\Embedded\ZipCodeMap;
use DBD\Entity\Tests\Entities\SelfReference\OneEmbedded;
use DBD\Entity\Tests\Fixtures\Data;
use DBD\Entity\Type;
use PHPUnit\Framework\TestCase;

// TODO: test Embedded has Complex or Constraint

class EmbeddedTest extends TestCase
{
    public function testSelfReferenceChain()
    {
        $one = ['one_id' => 1];
        $four = ['four_id' => 4, 'one' => $one];
        $three = ['three_id' => 3, 'four' => $four];
        $two = ['two_id' => 2, 'three' => $three];

        $data = $one + ['two' => $two + ['three' => $three]];

        $entity = new OneEmbedded($data, 2);

        self::assertInstanceOf(Entity::class, $entity);
        self::assertInstanceOf(StrictlyFilledEntity::class, $entity);
        self::assertCount(1, get_object_vars($entity->TwoEmbedded->ThreeEmbedded));

        $entity = new OneEmbedded($data, 3);
        self::assertCount(2, get_object_vars($entity->TwoEmbedded->ThreeEmbedded));

        // $four contains only simple one, so should be exception
        $this->expectException(EntityException::class);
        /** @noinspection PhpExpressionResultUnusedInspection */
        new OneEmbedded($data, 4);
    }

    public function testMissingColumns()
    {
        $data = ['one_id' => 1];
        $this->expectException(EntityException::class);
        /** @noinspection PhpExpressionResultUnusedInspection */
        new OneEmbedded($data);
    }

    public function testNoEntity()
    {
        $data = Data::getStreetWithZipCodeNotJsonData();
        $embedded = new StreetWithZipCodeNotEntity($data);

        self::assertNotNull($embedded->name);
        self::assertNotNull($embedded->id);
        self::assertNotNull($embedded->ZipCode);
        self::assertIsArray($embedded->ZipCode);

        self::assertNotNull($embedded->ZipCode[ZipCodeMap::me()->id->name]);
        self::assertNotNull($embedded->ZipCode[ZipCodeMap::me()->value->name]);
    }

    public function testNonIterableNonJson()
    {
        $data = Data::getStreetWithZipCodeNotJsonData();
        $embedded = new StreetWithZipCodeNotJson($data);

        self::assertNotNull($embedded->name);
        self::assertNotNull($embedded->id);
        self::assertNotNull($embedded->ZipCode);
        self::assertInstanceOf(ZipCode::class, $embedded->ZipCode);
        self::assertNotNull($embedded->ZipCode->id);
        self::assertNotNull($embedded->ZipCode->value);

    }

    public function testNonIterable()
    {
        $data = Data::getStreetWithZipCodeJsonData();
        $embedded = new StreetWithZipCode($data);

        self::assertNotNull($embedded->name);
        self::assertNotNull($embedded->id);
        self::assertNotNull($embedded->ZipCode);
        self::assertInstanceOf(ZipCode::class, $embedded->ZipCode);
        self::assertNotNull($embedded->ZipCode->id);
        self::assertNotNull($embedded->ZipCode->value);
    }

    public function testCreationIterable()
    {
        $embedded = new CountryWithRegions();

        self::assertNull($embedded->name);
        self::assertNull($embedded->id);
        self::assertNull($embedded->Regions);

        $embedded = new CountryWithRegions(Data::getCountryWithRegionsData());

        self::assertNotNull($embedded->name);
        self::assertNotNull($embedded->id);
        self::assertNotNull($embedded->Regions);
        self::assertIsIterable($embedded->Regions);
        self::assertCount(count(Data::getRegionsData()), $embedded->Regions);

        foreach ($embedded->Regions as $region) {
            self::assertInstanceOf(Region::class, $region);
            self::assertNotNull($region->name);
            self::assertNotNull($region->id);
        }
    }

    public function testConstruction()
    {
        $embedded = new Embedded([
            Embedded::ENTITY_CLASS => Person::class,
            Embedded::IS_ITERABLE => true,
            Embedded::NAME => "column_name",
            Embedded::DB_TYPE => Type::Array,
        ]);

        self::assertNotNull($embedded->dbType);
        self::assertNotNull($embedded->name);
        self::assertNotNull($embedded->entityClass);
        self::assertNotNull($embedded->isIterable);

        $this->expectException(MapperException::class);
        new Embedded([]);
    }
}
