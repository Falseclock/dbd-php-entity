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

use DateTime;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Entity;
use DBD\Entity\EntityCache;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Tests\Entities\DeclarationChain\B;
use DBD\Entity\Tests\Entities\DeclarationChain\C;
use DBD\Entity\Tests\Entities\DeclarationChain\D;
use DBD\Entity\Tests\Entities\JsonTypeColumn;
use DBD\Entity\Tests\Entities\JsonTypeColumnMap;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use DBD\Entity\Tests\Entities\PersonBaseOnlyDeclared;
use DBD\Entity\Tests\Entities\PersonBaseSetters;
use DBD\Entity\Tests\Entities\PersonBaseWithoutMapping;
use DBD\Entity\Tests\Entities\PersonBaseWithUnmappedProperty;
use DBD\Entity\Tests\Entities\Synthetic;
use DBD\Entity\Tests\Entities\UnUsedPropertyInMapper;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class EntityTest
 * @package DBD\Entity\Tests
 */
class EntityTest extends TestCase
{
    /**
     * @throws EntityException
     */
    public function testMapperHasPropertyNotUsedInEntity()
    {
        $entity = new UnUsedPropertyInMapper();
        self::assertInstanceOf(Entity::class, $entity);

        $entity = new UnUsedPropertyInMapper(Data::getUnUsedPropertyInMapperData());
        self::assertInstanceOf(Entity::class, $entity);
    }

    /**
     * @throws EntityException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function testNullValueToNonNull()
    {
        $personData = Data::getPersonFullEntityData();
        $personData[PersonBaseMap::me()->id->name] = null;

        $this->expectException(EntityException::class);
        new PersonBase($personData);
    }

    /**
     * @throws EntityException
     */
    public function testDeclarationChain3()
    {
        $d = new D(Data::getDeclarationChainData());
        self::assertInstanceOf(Entity::class, $d);

        $reflection = new ReflectionClass($d);
        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->name;
            self::assertTrue($d->$name);

            // Actually property exist
            self::assertTrue(property_exists($d, $name));
            // And has such attribute
            self::assertObjectHasAttribute($name, $d);

            // but calling this property should trigger exception
            self::assertTrue(isset($d->$name));
            $d->$name;
        }
    }

    /**
     * @throws EntityException
     */
    public function testDeclarationChain2()
    {
        $c = new C(Data::getDeclarationChainData());
        self::assertInstanceOf(Entity::class, $c);
        self::assertTrue($c->a1);

        $missingProperty = 'a3';

        self::assertTrue($c->a1);
        self::assertTrue($c->a2);

        // Actually property exist
        self::assertTrue(property_exists($c, $missingProperty));
        // And has such attribute
        self::assertObjectHasAttribute($missingProperty, $c);

        // but calling this property should trigger exception
        self::assertFalse(isset($c->$missingProperty), "C class still has property '{$missingProperty}'");

        // Undefined property: DBD\Entity\Tests\Entities\DeclarationChain\B::$a3
        self::expectNotice();
        $c->$missingProperty;
    }

    /**
     * @throws EntityException
     */
    public function testDeclarationChain1()
    {
        $b = new B(Data::getDeclarationChainData());
        self::assertInstanceOf(Entity::class, $b);
        $missingProperty = 'a3';

        self::assertTrue($b->a1);
        self::assertTrue($b->a2);

        // Actually property exist
        self::assertTrue(property_exists($b, $missingProperty));
        // And has such attribute
        self::assertObjectHasAttribute($missingProperty, $b);

        // but calling this property should trigger exception
        self::assertFalse(isset($b->$missingProperty));

        // Undefined property: DBD\Entity\Tests\Entities\DeclarationChain\B::$a3
        self::expectNotice();
        $b->$missingProperty;
    }

    /**
     * @throws EntityException
     */
    public function testJsonType()
    {
        $entity = new JsonTypeColumn();
        self::assertInstanceOf(Entity::class, $entity);

        $entity = new JsonTypeColumn(Data::getJsonTypeColumnData());

        self::assertIsArray($entity->json);
        self::assertEquals(json_decode(Data::getJsonTypeColumnData()[JsonTypeColumnMap::me()->json->name], true), $entity->json);
    }

    /**
     *
     */
    public function testGetConst()
    {
        $entity = new Synthetic();

        self::assertNotNull($entity::SCHEME);
        self::assertNotNull($entity::TABLE);
    }

    /**
     *
     */
    public function testGetTable()
    {
        $table = PersonBase::table();

        self::assertNotNull($table);
        self::assertEquals(PersonBase::SCHEME . "." . PersonBase::TABLE, $table);
    }

    /**
     * Test Synthetic entity can be without ANNOTATION, TABLE and SCHEMA constants
     *
     * @throws EntityException
     */
    public function testSynthetic()
    {
        $entity = new Synthetic();

        self::assertNotNull($entity);
        self::assertInstanceOf(SyntheticEntity::class, $entity);

        $map = $entity::map();

        self::assertInstanceOf(Mapper::class, $map);

        // Check entity creation
        $personData = Data::getPersonFullEntityData();
        $person = new Synthetic($personData);

        self::assertNotNull($person->id);
    }

    /**
     * All Entity variables must be mapped in case of FullEntity or StrictlyFilledEntity
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function testUnmappedProperty()
    {
        $this->expectException(EntityException::class);
        new PersonBaseWithUnmappedProperty();
        self::assertIsArray(EntityCache::$mapCache);
    }

    /**
     * Just test instantiation
     *
     * @throws EntityException
     */
    public function testInstance()
    {
        $person = new PersonBase();

        self::assertInstanceOf(Entity::class, $person);
        self::assertInstanceOf(FullEntity::class, $person);

        // Check entity creation
        $personData = Data::getPersonFullEntityData();
        $person = new PersonBase($personData);

        // Check all variables of person equals to initial array
        self::assertEquals($person->id, $personData[PersonBaseMap::me()->id->name]);
        self::assertEquals($person->name, $personData[PersonBaseMap::me()->name->name]);
        self::assertEquals($person->email, $personData[PersonBaseMap::me()->email->name]);
        self::assertEquals($person->isActive, $personData[PersonBaseMap::me()->isActive->name]);
        self::assertEquals($person->registrationDate, $personData[PersonBaseMap::me()->registrationDate->name]);
    }

    /**
     * Test setters functions
     *
     * @throws EntityException
     */
    public function testSetters()
    {
        $personData = Data::getPersonFullEntityData();
        $person = new PersonBaseSetters($personData);

        self::assertInstanceOf(PersonBase::class, $person);
        self::assertInstanceOf(DateTime::class, $person->registrationDate);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	on ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	off ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	t ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	f ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	1';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '0	';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = '	yes';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = ' no ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = ' tRuE ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = ' fAlSe ';
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = true;
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = false;
        $person = new PersonBaseSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = 'some other value';
        $person = new PersonBaseSetters($personData);
        self::assertNull($person->isActive);

        $personData[PersonBaseMap::me()->isActive->name] = 2;
        $person = new PersonBaseSetters($personData);
        self::assertNull($person->isActive);
    }

    /**
     * Case when we selecting a lot of fields and forget to select some of them for FullEntity or StrictlyFilledEntity
     * @throws EntityException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function testMissingColumns()
    {
        $personData = Data::getPersonFullEntityData();
        $expectCount = count($personData) - 1;

        unset($personData[PersonBaseMap::me()->name->name]);
        self::assertCount($expectCount, $personData);
        self::assertArrayNotHasKey(PersonBaseMap::me()->name->name, $personData);

        $this->expectException(EntityException::class);
        new PersonBase($personData);
    }

    /**
     * When Entity class does not have Mapper class
     *
     * @throws EntityException
     */
    public function testMissingMapper()
    {
        $this->expectException(EntityException::class);
        PersonBaseWithoutMapping::map();
    }

    /**
     *
     * @throws EntityException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function testOnlyDeclared()
    {
        $personData = Data::getPersonFullEntityData();
        $person = new PersonBaseOnlyDeclared($personData);

        $entityCache = EntityCache::$mapCache;

        $reflection = new ReflectionClass(PersonBaseOnlyDeclared::class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $properties = array_filter($properties, function ($property) {
            return $property->class == PersonBaseOnlyDeclared::class;
        }, ARRAY_FILTER_USE_BOTH);


        self::assertInstanceOf(PersonBase::class, $person);
        self::assertCount(count($properties), get_object_vars($person));

        foreach ($entityCache[PersonBaseOnlyDeclared::class][EntityCache::DECLARED_PROPERTIES] as $propertyName => $boolean)
            self::assertTrue(property_exists($person, $propertyName));

        // Unset some unnecessary columns and check we do not have any exception, cause PersonOnlyDeclared extends Person which is FullEntity instance
        unset($personData[PersonBaseMap::me()->registrationDate->name]);
        new PersonBaseOnlyDeclared($personData);

        // Unset some required columns
        unset($personData[PersonBaseMap::me()->name->name]);
        unset($personData[PersonBaseMap::me()->isActive->name]);
        $this->expectException(EntityException::class);
        new PersonBaseOnlyDeclared($personData);
    }
}
