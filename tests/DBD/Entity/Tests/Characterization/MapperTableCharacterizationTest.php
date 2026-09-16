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
use DBD\Entity\Constraint;
use DBD\Entity\MapperCache;
use DBD\Entity\Table;
use DBD\Entity\Tests\Entities\Attributed;
use DBD\Entity\Tests\Entities\Constraint\User;
use DBD\Entity\Tests\Entities\Constraint\UserMap;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Characterization of MapperTrait::getTable() and of the Table instances stored in Constraint::$localTable.
 *
 * MapperTrait::getTable() checks MapperCache::$tables but writes MapperCache::$table, so the cache never hits
 * and every call builds a fresh Table. Changing that is NOT a drop-in fix: Constraint::$localTable is filled by
 * calling getTable() while constraints are still being registered, so a cached Table would freeze a partial
 * constraint list, and sharing the instance would make Table <-> Constraint cyclic for legacy mappers.
 * These tests pin the current observable structure so any future change is a conscious one.
 */
class MapperTableCharacterizationTest extends TestCase
{
    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testGetTableBuildsFreshEqualInstanceOnEveryCall(): void
    {
        $map = PersonBaseMap::me();

        $first = $map->getTable();
        $second = $map->getTable();

        self::assertInstanceOf(Table::class, $first);
        self::assertNotSame($first, $second);
        self::assertEquals($first, $second);

        // Only the Table wrapper is rebuilt, the Column instances are shared with the mapper
        self::assertSame($map->getColumns()['id'], $second->columns['id']);

        self::assertSame(PersonBase::TABLE, $second->name);
        self::assertSame(PersonBase::SCHEME, $second->scheme);
        self::assertSame(PersonBaseMap::ANNOTATION, $second->annotation);
        self::assertSame(['id'], array_keys($second->keys));
        self::assertSame([], $second->constraints);
    }

    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testMapperCacheKeepsOnlyTheLastBuiltTable(): void
    {
        $map = PersonBaseMap::me();

        $table = $map->getTable();

        self::assertFalse(property_exists(MapperCache::me(), 'tables'));
        self::assertArrayHasKey($map->name(), MapperCache::me()->table);
        self::assertSame($table, MapperCache::me()->table[$map->name()]);

        $newer = $map->getTable();
        self::assertSame($newer, MapperCache::me()->table[$map->name()]);
        self::assertNotSame($table, MapperCache::me()->table[$map->name()]);
    }

    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testLegacyMapperConstraintsCarryPartialTableSnapshots(): void
    {
        self::assertTrue(class_exists(User::class)); // User.php declares UserMap as well
        $map = UserMap::me();

        $constraints = $map->getConstraints();
        self::assertSame(['Company', 'Person'], array_keys($constraints));

        $table = $map->getTable();
        self::assertCount(2, $table->constraints);
        self::assertSame($constraints['Company'], $table->constraints['Company']);
        self::assertSame($constraints['Person'], $table->constraints['Person']);

        // Every constraint received its own Table, built at the moment the constraint was registered
        self::assertInstanceOf(Table::class, $constraints['Company']->localTable);
        self::assertNotSame($table, $constraints['Company']->localTable);
        self::assertNotSame($constraints['Company']->localTable, $constraints['Person']->localTable);

        // ... therefore the snapshots contain only the constraints registered before them
        self::assertSame([], array_keys($constraints['Company']->localTable->constraints));
        self::assertSame(['Company'], array_keys($constraints['Person']->localTable->constraints));

        // ... and the structure has no cycles
        self::assertNotFalse(json_encode($table));
        self::assertNotFalse(json_encode($constraints['Person']));
    }

    /**
     * @throws EntityException
     * @throws ReflectionException
     */
    public function testAttributedMapperConstraintsReferenceThemselvesThroughLocalTable(): void
    {
        $map = Attributed::map();

        $constraints = $map->getConstraints();
        self::assertArrayHasKey('Company', $constraints);
        $company = $constraints['Company'];
        self::assertInstanceOf(Constraint::class, $company);

        // MapperAttributed registers all constraints first, so the snapshot already contains the constraint itself
        self::assertSame($company, $company->localTable->constraints['Company']);
        self::assertNotSame($map->getTable(), $company->localTable);

        // ... which makes the structure cyclic
        self::assertFalse(json_encode($map->getTable()));
        self::assertSame(JSON_ERROR_RECURSION, json_last_error());
    }
}
