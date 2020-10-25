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

namespace DBD\Entity\Tests;

use DBD\Entity\Common\EntityException;
use DBD\Entity\Constraint;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Interfaces\StrictlyFilledEntity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Tests\Entities\Constraint\Tender;
use DBD\Entity\Tests\Entities\Constraint\User;
use DBD\Entity\Tests\Entities\Constraint\UserFull;
use DBD\Entity\Tests\Entities\Constraint\UserFullSynthetic;
use DBD\Entity\Tests\Entities\Constraint\UserWithSetter;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;

class ConstraintTest extends TestCase
{
    public function testSetter()
    {
        $entity = new UserWithSetter();

        self::assertInstanceOf(StrictlyFilledEntity::class, $entity);
        self::assertInstanceOf(User::class, $entity);

        $entity = new UserWithSetter(Data::getUserFullData());

        self::assertNotNull($entity->id);
        self::assertNotNull($entity->companyId);
        self::assertNotNull($entity->personId);

        self::assertNotNull($entity->Company->id);
        self::assertNull($entity->Company->name);

        self::assertNull($entity->Person);
    }

    public function testUser()
    {
        $entity = new UserFull();

        self::assertInstanceOf(StrictlyFilledEntity::class, $entity);
        self::assertInstanceOf(User::class, $entity);

        $entity = new UserFullSynthetic(Data::getUserFullData());

        self::assertNotNull($entity->id);
        self::assertNotNull($entity->companyId);
        self::assertNotNull($entity->personId);

        self::assertNotNull($entity->Company);
        self::assertNotNull($entity->Company->name);
        self::assertNotNull($entity->Company->id);

        self::assertNotNull($entity->Person);
        self::assertNotNull($entity->Person->firstName);
        self::assertNotNull($entity->Person->id);

        $this->expectException(EntityException::class);
        new UserFullSynthetic(Data::getUserNonFullData());
    }

    public function testUserSynthetic()
    {
        $entity = new UserFullSynthetic();

        self::assertInstanceOf(SyntheticEntity::class, $entity);
        self::assertInstanceOf(StrictlyFilledEntity::class, $entity);
        self::assertInstanceOf(User::class, $entity);

        $entity = new UserFullSynthetic(Data::getUserFullData());

        self::assertNotNull($entity->id);
        self::assertNotNull($entity->companyId);
        self::assertNotNull($entity->personId);
        self::assertNotNull($entity->Person);
        self::assertNotNull($entity->Person->firstName);
        self::assertNotNull($entity->Person->id);
        self::assertNotNull($entity->Company);
        self::assertNotNull($entity->Company->name);
        self::assertNotNull($entity->Company->id);

        $this->expectException(EntityException::class);
        new UserFullSynthetic(Data::getUserNonFullData());
    }

    public function testInstance()
    {
        $constraint = new Constraint();

        self::assertNotNull($constraint);
        self::assertInstanceOf(Constraint::class, $constraint);

        $constraint = new Constraint([
            Constraint::LOCAL_COLUMN => "tender_id",
            Constraint::FOREIGN_SCHEME => Tender::SCHEME,
            Constraint::FOREIGN_TABLE => Tender::TABLE,
            Constraint::FOREIGN_COLUMN => "tender_id",
            Constraint::JOIN_TYPE => null,
            Constraint::BASE_CLASS => Tender::class,
        ]);

        self::assertNotNull($constraint);
        self::assertInstanceOf(Constraint::class, $constraint);
    }

    public function testEntity()
    {
        $entity = new User();

        self::assertInstanceOf(FullEntity::class, $entity);
        self::assertInstanceOf(Entity::class, $entity);

        self::assertNotNull(new User(Data::getUserData()));
    }
}
