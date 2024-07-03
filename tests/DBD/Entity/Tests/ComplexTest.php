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
use DBD\Entity\Complex;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Tests\Entities\OnlyComplex;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Entities\SelfReference\FourComplex;
use DBD\Entity\Tests\Entities\SelfReference\OneComplex;
use DBD\Entity\Tests\Entities\SelfReference\ThreeComplex;
use DBD\Entity\Tests\Entities\SelfReference\TwoComplex;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;
use stdClass;

// TODO: test Complex has Embedded or Constraint

/**
 * Class ComplexTest
 * @package DBD\Entity\Tests
 */
class ComplexTest extends TestCase
{
    /**
     * @throws EntityException
     */
    public function testSelfReferenceChain()
    {
        $data = [
            'one_id' => 1,
            'two_id' => 2,
            'three_id' => 3,
            'four_id' => 4,
        ];

        $entity = new OneComplex($data);

        self::assertInstanceOf(Entity::class, $entity);
        self::assertCount(3, get_object_vars($entity));
        self::assertEquals(1, $entity->id);
        self::assertInstanceOf(TwoComplex::class, $entity->TwoComplex);
        self::assertInstanceOf(FourComplex::class, $entity->FourComplex);

        self::assertCount(3, get_object_vars($entity->TwoComplex));
        self::assertEquals(2, $entity->TwoComplex->id);
        self::assertInstanceOf(ThreeComplex::class, $entity->TwoComplex->ThreeComplex);
        self::assertInstanceOf(OneComplex::class, $entity->TwoComplex->OneComplex);

        // Level 2
        self::assertCount(1, get_object_vars($entity->TwoComplex->OneComplex));
        self::assertCount(1, get_object_vars($entity->TwoComplex->ThreeComplex));

        self::assertCount(4, get_object_vars($entity->FourComplex));

        self::assertCount(1, get_object_vars($entity->FourComplex->OneComplex));
        self::assertCount(1, get_object_vars($entity->FourComplex->TwoComplex));
        self::assertCount(1, get_object_vars($entity->FourComplex->ThreeComplex));
    }

    /**
     * @throws EntityException
     */
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

    /**
     * @throws EntityException
     */
    public function testArrayInstanceUsage()
    {
        $complex = new Complex([
            Complex::TYPE => PersonBase::class
        ]);

        self::assertNotNull($complex);
    }

    /**
     * @throws EntityException
     */
    public function testStringInstanceUsage()
    {
        $complexName = PersonBase::class;
        $complex = new Complex($complexName);

        self::assertInstanceOf(Complex::class, $complex);
        self::assertEquals($complexName, $complex->complexClass);
    }

    /**
     *
     */
    public function testNullInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Complex(null);
    }
}
