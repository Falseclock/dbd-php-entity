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

namespace DBD\Entity\Tests\Columns;

use DBD\Entity\Columns\BigIntColumn;
use DBD\Entity\Columns\BooleanColumn;
use DBD\Entity\Columns\CustomColumn;
use DBD\Entity\Columns\DateColumn;
use DBD\Entity\Columns\NumericColumn;
use DBD\Entity\Columns\TimeTZColumn;
use DBD\Entity\Entity;
use DBD\Entity\EntityTable;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Primitive;
use PHPUnit\Framework\TestCase;

/**
 * Attribute-mapped entities declared inline, one column attribute per test.
 * Consolidated from the tests proposed in PR #6 (dc-nispandiarov).
 */
class AttributedColumnsHydrationTest extends TestCase
{
    public function testBigIntColumn(): void
    {
        $data = ['test_id' => 1000000];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[BigIntColumn(name: 'test_id', auto: true, nullable: false, primary: true, annotation: 'Test id')]
            public int $id;
        };

        self::assertSame($data['test_id'], $entity->id);

        $column = $entity::map()->getColumns()['id'];
        self::assertTrue($column->key);
        self::assertTrue($column->isAuto);
        self::assertSame('int8', $column->originType);
    }

    public function testBooleanColumn(): void
    {
        $data = ['test_value' => true];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[BooleanColumn(name: 'test_value', nullable: true, annotation: 'Test boolean')]
            public ?bool $value;
        };

        self::assertSame($data['test_value'], $entity->value);
    }

    public function testCustomColumnWithPrecision(): void
    {
        $data = ['test_value' => 12.101];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[CustomColumn(name: 'test_value', primitiveType: Primitive::Single, originType: 'float4', length: 10, precision: 2, annotation: 'Test value')]
            public ?float $value;
        };

        self::assertSame($data['test_value'], $entity->value);

        $column = $entity::map()->getColumns()['value'];
        self::assertSame(10, $column->maxLength);
        self::assertSame(2, $column->precision);
        self::assertSame(Primitive::Single, $column->type->getValue());
    }

    public function testDateColumn(): void
    {
        $data = ['test_value' => '1999-12-31'];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[DateColumn(name: 'test_value', nullable: true, annotation: 'Test date')]
            public ?string $value;
        };

        self::assertSame($data['test_value'], $entity->value);
    }

    public function testNumericColumnWithoutFacets(): void
    {
        $data = ['test_value' => '0.00'];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[NumericColumn(name: 'test_value', nullable: true, primary: true, defaultValue: '0.00', annotation: 'Test numeric')]
            public ?string $value;
        };

        self::assertSame($data['test_value'], $entity->value);

        $column = $entity::map()->getColumns()['value'];
        self::assertNull($column->maxLength);
        self::assertNull($column->precision);
        self::assertSame('0.00', $column->defaultValue);
        self::assertTrue($column->key);
    }

    public function testTimeTzColumn(): void
    {
        $data = ['test_value' => '15:30:00+02'];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[TimeTZColumn(name: 'test_value', nullable: true, annotation: 'Test time')]
            public ?string $value;
        };

        self::assertSame($data['test_value'], $entity->value);
        self::assertSame('timetz', $entity::map()->getColumns()['value']->originType);
    }
}
