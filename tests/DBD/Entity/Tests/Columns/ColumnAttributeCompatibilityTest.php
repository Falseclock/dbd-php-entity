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

use DBD\Entity\Columns\CustomColumn;
use DBD\Entity\Columns\NumericColumn;
use DBD\Entity\Columns\TimeTZColumn;
use DBD\Entity\EntityTable;
use DBD\Entity\Primitive;
use DBD\Entity\Primitives\TimePrimitives;
use PHPUnit\Framework\TestCase;

/**
 * Public constructors of the column attributes are API: both positional and named usage must keep working.
 * Covers the backward-compatible parts of PR #6 (optional numeric facets, CustomColumn precision,
 * EntityTable annotation default) and the TimeTZColumn constructor.
 */
class ColumnAttributeCompatibilityTest extends TestCase
{
    public function testEntityTableAnnotationDefaultsToEmptyString(): void
    {
        $table = new EntityTable('public', 'items');

        self::assertSame('public', $table->scheme);
        self::assertSame('items', $table->name);
        self::assertSame('', $table->annotation);

        self::assertSame('Items', (new EntityTable('public', 'items', 'Items'))->annotation);
        self::assertSame('Items', (new EntityTable(scheme: 'public', name: 'items', annotation: 'Items'))->annotation);
    }

    public function testCustomColumnKeepsPositionalSignature(): void
    {
        $column = new CustomColumn('amount', Primitive::Decimal, 'numeric', 12, true, false, '0', 'Amount');

        self::assertSame('amount', $column->name);
        self::assertSame(Primitive::Decimal, $column->type->getValue());
        self::assertSame('numeric', $column->originType);
        self::assertSame(12, $column->maxLength);
        self::assertTrue($column->nullable);
        self::assertFalse($column->key);
        self::assertSame('0', $column->defaultValue);
        self::assertSame('Amount', $column->annotation);
        self::assertNull($column->precision);
    }

    public function testCustomColumnAcceptsPrecision(): void
    {
        $named = new CustomColumn(name: 'amount', primitiveType: Primitive::Decimal, originType: 'numeric', length: 12, precision: 2);

        self::assertSame(12, $named->maxLength);
        self::assertSame(2, $named->precision);
        self::assertFalse($named->nullable);

        // precision is appended after the existing parameters, so positional callers are unaffected
        $positional = new CustomColumn('amount', Primitive::Decimal, 'numeric', 12, true, false, null, 'Amount', 2);

        self::assertSame(2, $positional->precision);
        self::assertTrue($positional->nullable);
        self::assertSame('Amount', $positional->annotation);
    }

    public function testNumericColumnKeepsPositionalSignature(): void
    {
        $column = new NumericColumn('price', 10, 2, 'Price', false, 0.5, false, true);

        self::assertSame('price', $column->name);
        self::assertSame(Primitive::Decimal, $column->type->getValue());
        self::assertSame('numeric', $column->originType);
        self::assertSame(10, $column->maxLength);
        self::assertSame(2, $column->precision);
        self::assertSame('Price', $column->annotation);
        self::assertFalse($column->nullable);
        self::assertSame(0.5, $column->defaultValue);
        self::assertFalse($column->isAuto);
        self::assertTrue($column->key);
    }

    public function testNumericColumnLengthAndPrecisionAreOptional(): void
    {
        $column = new NumericColumn(name: 'price');

        self::assertNull($column->maxLength);
        self::assertNull($column->precision);
        self::assertTrue($column->nullable);
        self::assertNull($column->defaultValue);
        self::assertFalse($column->isAuto);
        self::assertFalse($column->key);

        $withFacets = new NumericColumn(name: 'price', length: 10, precision: 2, nullable: false, primary: true, annotation: 'Price');

        self::assertSame(10, $withFacets->maxLength);
        self::assertSame(2, $withFacets->precision);
        self::assertFalse($withFacets->nullable);
        self::assertTrue($withFacets->key);
        self::assertSame('Price', $withFacets->annotation);
    }

    public function testNumericColumnAcceptsStringAndFloatDefaultValue(): void
    {
        self::assertSame('0.00', (new NumericColumn(name: 'price', defaultValue: '0.00'))->defaultValue);
        self::assertSame(0.5, (new NumericColumn(name: 'price', defaultValue: 0.5))->defaultValue);
        self::assertNull((new NumericColumn(name: 'price', defaultValue: null))->defaultValue);
    }

    public function testTimeTzColumnPositionalAndNamedConstructor(): void
    {
        $positional = new TimeTZColumn('opened_at', true, false, null, 'Opened at');
        $named = new TimeTZColumn(name: 'opened_at', nullable: true, isAuto: false, defaultValue: null, annotation: 'Opened at');

        foreach ([$positional, $named] as $column) {
            self::assertSame('opened_at', $column->name);
            self::assertSame('timetz', $column->originType);
            self::assertSame(TimePrimitives::TimeOfDay, $column->type->getValue());
            self::assertTrue($column->nullable);
            self::assertFalse($column->isAuto);
            self::assertNull($column->defaultValue);
            self::assertSame('Opened at', $column->annotation);
        }

        $defaults = new TimeTZColumn('opened_at');

        self::assertFalse($defaults->nullable);
        self::assertFalse($defaults->isAuto);
        self::assertNull($defaults->defaultValue);
        self::assertNull($defaults->annotation);

        $auto = new TimeTZColumn(name: 'opened_at', isAuto: true, defaultValue: 'now()');

        self::assertTrue($auto->isAuto);
        self::assertSame('now()', $auto->defaultValue);
        self::assertFalse($auto->nullable);
    }
}
