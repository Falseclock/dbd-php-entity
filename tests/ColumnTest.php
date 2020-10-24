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

use DBD\Entity\Column;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Primitive;
use PHPUnit\Framework\TestCase;
use stdClass;

class ColumnTest extends TestCase
{
    public function testArrayInstanceUsage()
    {
        $column = new Column([
            Column::NAME => "person_id",
            Column::PRIMITIVE_TYPE => Primitive::Int32,
            Column::IS_AUTO => true,
            Column::NULLABLE => false,
            Column::ANNOTATION => "Unique ID",
            Column::KEY => true,
            Column::ORIGIN_TYPE => "int4",
        ]);

        self::assertNotNull($column);
    }

    public function testStringInstanceUsage()
    {
        $columnName = "table_column_name";
        $column = new Column($columnName);

        self::assertInstanceOf(Column::class, $column);
        self::assertEquals($columnName, $column->name);
    }

    public function testNullInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(null);
    }

    public function testBoolInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(true);
    }

    public function testObjectInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(new stdClass());
    }
}
