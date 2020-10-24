<?php
declare(strict_types=1);

/*************************************************************************************
 *   MIT License                                                                     *
 *                                                                                   *
 *   Copyright (C) 2020 by Nurlan Mukhanov <nurike@gmail.com>                        *
 *                                                                                   *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy    *
 *   of this software and associated documentation files (the "Software"), to deal   *
 *   in the Software without restriction, including without limitation the rights    *
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell       *
 *   copies of the Software, and to permit persons to whom the Software is           *
 *   furnished to do so, subject to the following conditions:                        *
 *                                                                                   *
 *   The above copyright notice and this permission notice shall be included in all  *
 *   copies or substantial portions of the Software.                                 *
 *                                                                                   *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR      *
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,        *
 *   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE    *
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER          *
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,   *
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE   *
 *   SOFTWARE.                                                                       *
 ************************************************************************************/

namespace DBD\Entity\Tests;

use DateTime;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Entity;
use DBD\Entity\EntityCache;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Tests\Entities\Person;
use DBD\Entity\Tests\Entities\PersonMap;
use DBD\Entity\Tests\Entities\PersonOnlyDeclared;
use DBD\Entity\Tests\Entities\PersonSetters;
use DBD\Entity\Tests\Entities\PersonWithoutMapping;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function testEntityInstance()
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

    public function testEntitySetters()
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

    public function testMissingColumns()
    {
        $personData = Data::getPersonFullEntityData();

        unset($personData[PersonMap::me()->name->name]);
        self::assertCount(4, $personData);
        self::assertArrayNotHasKey(PersonMap::me()->name->name, $personData);

        $this->expectException(EntityException::class);
        new Person($personData);
    }

    public function testMissingMapper()
    {
        $this->expectException(EntityException::class);
        PersonWithoutMapping::map();
    }

    public function testOnlyDeclared()
    {
        $personData = Data::getPersonFullEntityData();
        $person = new PersonOnlyDeclared($personData);

        $entityCache = EntityCache::$mapCache;

        self::assertInstanceOf(Person::class, $person);
        self::assertCount(3, get_object_vars($person));
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
