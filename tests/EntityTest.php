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
use DBD\Entity\Common\MapperException;
use DBD\Entity\Entity;
use DBD\Entity\EntityCache;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Tests\Entities\DeclarationChain\B;
use DBD\Entity\Tests\Entities\DeclarationChain\C;
use DBD\Entity\Tests\Entities\DeclarationChain\D;
use DBD\Entity\Tests\Entities\JsonTypeColumn;
use DBD\Entity\Tests\Entities\JsonTypeColumnMap;
use DBD\Entity\Tests\Entities\OnlyComplex;
use DBD\Entity\Tests\Entities\Person;
use DBD\Entity\Tests\Entities\PersonMap;
use DBD\Entity\Tests\Entities\PersonOnlyDeclared;
use DBD\Entity\Tests\Entities\PersonSetters;
use DBD\Entity\Tests\Entities\PersonWithoutMapping;
use DBD\Entity\Tests\Entities\PersonWithUnmappedProperty;
use DBD\Entity\Tests\Entities\Synthetic;
use DBD\Entity\Tests\Entities\UnUsedPropertyInMapper;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class EntityTest extends TestCase
{
    public function testComplexDefinition()
    {
        $entity = new OnlyComplex();
        self::assertInstanceOf(Entity::class, $entity);
        self::assertInstanceOf(StrictlyFilledEntity::class, $entity);

        $entity = new OnlyComplex(Data::getJustComplexData());

        self::assertNotNull($entity->Address);
        self::assertNotNull($entity->Address->street);
        self::assertNotNull($entity->Address->id);
        self::assertNotNull($entity->Person);
        self::assertNotNull($entity->Person->name);
        self::assertNotNull($entity->Person->id);
        self::assertNotNull($entity->Person->isActive);
        self::assertNotNull($entity->Person->registrationDate);
        self::assertNotNull($entity->Person->email);
    }

    public function testMapperHasPropertyNotUsedInEntity()
    {
        $entity = new UnUsedPropertyInMapper();
        self::assertInstanceOf(Entity::class, $entity);

        $entity = new UnUsedPropertyInMapper(Data::getUnUsedPropertyInMapperData());
        self::assertInstanceOf(Entity::class, $entity);
    }

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
        $this->expectNotice();
        $c->$missingProperty;
    }

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
        $this->expectNotice();
        $b->$missingProperty;
    }

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
    public function testGetTable()
    {
        $table = Person::table();

        self::assertNotNull($table);
        self::assertEquals(Person::SCHEME . "." . Person::TABLE, $table);
    }

    /**
     * Test Synthetic entity can be without ANNOTATION, TABLE and SCHEMA constants
     *
     * @throws EntityException
     * @throws MapperException
     * @throws ReflectionException
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
     */
    public function testUnmappedProperty()
    {
        $this->expectException(EntityException::class);
        new PersonWithUnmappedProperty();
    }

    /**
     * Just test instantiation
     *
     * @throws EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function testInstance()
    {
        $person = new Person();

        self::assertInstanceOf(Entity::class, $person);
        self::assertInstanceOf(FullEntity::class, $person);

        // Check entity creation
        $personData = Data::getPersonFullEntityData();
        $person = new Person($personData);

        // Check all variables of person equals to initial array
        self::assertEquals($person->id, $personData[PersonMap::me()->id->name]);
        self::assertEquals($person->name, $personData[PersonMap::me()->name->name]);
        self::assertEquals($person->email, $personData[PersonMap::me()->email->name]);
        self::assertEquals($person->isActive, $personData[PersonMap::me()->isActive->name]);
        self::assertEquals($person->registrationDate, $personData[PersonMap::me()->registrationDate->name]);
    }

    /**
     * Test setters functions
     *
     * @throws EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function testSetters()
    {
        $personData = Data::getPersonFullEntityData();
        $person = new PersonSetters($personData);

        self::assertInstanceOf(Person::class, $person);
        self::assertInstanceOf(DateTime::class, $person->registrationDate);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	on ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	off ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	t ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	f ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	1';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '0	';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = '	yes';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = ' no ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = ' tRuE ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = ' fAlSe ';
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = true;
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertTrue($person->isActive);

        $personData[PersonMap::me()->isActive->name] = false;
        $person = new PersonSetters($personData);
        self::assertIsBool($person->isActive);
        self::assertFalse($person->isActive);

        $personData[PersonMap::me()->isActive->name] = 'some other value';
        $person = new PersonSetters($personData);
        self::assertNull($person->isActive);

        $personData[PersonMap::me()->isActive->name] = 2;
        $person = new PersonSetters($personData);
        self::assertNull($person->isActive);
    }

    /**
     * Case when we selecting a lot of fields and forget to select some of them for FullEntity or StrictlyFilledEntity
     */
    public function testMissingColumns()
    {
        $personData = Data::getPersonFullEntityData();
        $expectCount = count($personData) - 1;

        unset($personData[PersonMap::me()->name->name]);
        self::assertCount($expectCount, $personData);
        self::assertArrayNotHasKey(PersonMap::me()->name->name, $personData);

        $this->expectException(EntityException::class);
        new Person($personData);
    }

    /**
     * When Entity class does not have Mapper class
     *
     * @throws EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function testMissingMapper()
    {
        $this->expectException(MapperException::class);
        PersonWithoutMapping::map();
    }

    /**
     *
     * @throws EntityException
     * @throws MapperException
     * @throws ReflectionException
     */
    public function testOnlyDeclared()
    {
        $personData = Data::getPersonFullEntityData();
        $person = new PersonOnlyDeclared($personData);

        $entityCache = EntityCache::$mapCache;

        $reflection = new ReflectionClass(PersonOnlyDeclared::class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $properties = array_filter($properties, function ($property) {
            return $property->class == PersonOnlyDeclared::class;
        }, ARRAY_FILTER_USE_BOTH);


        self::assertInstanceOf(Person::class, $person);
        self::assertCount(count($properties), get_object_vars($person));

        foreach ($entityCache[PersonOnlyDeclared::class][EntityCache::DECLARED_PROPERTIES] as $propertyName => $boolean)
            self::assertTrue(property_exists($person, $propertyName));

        // Unset some unnecessary columns and check we do not have any exception, cause PersonOnlyDeclared extends Person which is FullEntity instance
        unset($personData[PersonMap::me()->registrationDate->name]);
        new PersonOnlyDeclared($personData);

        // Unset some required columns
        unset($personData[PersonMap::me()->name->name]);
        unset($personData[PersonMap::me()->isActive->name]);
        $this->expectException(EntityException::class);
        new PersonOnlyDeclared($personData);
    }
}
