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

use DBD\Entity\Common\MapperException;
use DBD\Entity\Complex;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Tests\Entities\OnlyComplex;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;
use stdClass;

// TODO: chain check

class ComplexTest extends TestCase
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

    public function testArrayInstanceUsage()
    {
        $complex = new Complex([
            Complex::TYPE => PersonBase::class
        ]);

        self::assertNotNull($complex);
    }

    public function testStringInstanceUsage()
    {
        $complexName = PersonBase::class;
        $complex = new Complex($complexName);

        self::assertInstanceOf(Complex::class, $complex);
        self::assertEquals($complexName, $complex->complexClass);
    }

    public function testNullInstanceUsage()
    {
        $this->expectException(MapperException::class);
        new Complex(null);
    }

    public function testBoolInstanceUsage()
    {
        $this->expectException(MapperException::class);
        new Complex(true);
    }

    public function testObjectInstanceUsage()
    {
        $this->expectException(MapperException::class);
        new Complex(new stdClass());
    }
}
