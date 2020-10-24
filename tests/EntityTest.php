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
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Tests\Entities\Person;
use DBD\Entity\Tests\Entities\PersonMap;
use DBD\Entity\Tests\Entities\PersonOnlyDeclared;
use DBD\Entity\Tests\Entities\PersonSetters;
use DBD\Entity\Tests\Entities\PersonWithoutMapping;
use DBD\Entity\Tests\Entities\PersonWithUnmappedProperty;
use DBD\Entity\Tests\Entities\Synthetic;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class EntityTest extends TestCase
{
    /**
     * Test Synthetic entity can be without ANNOTATION, TABLE and SCHEMA constants
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testSynthetic()
    {
        $entity = new Synthetic();

        self::assertNotNull($entity);
        self::assertInstanceOf(SyntheticEntity::class, $entity);

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
