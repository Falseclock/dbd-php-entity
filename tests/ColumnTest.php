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

use DBD\Entity\Column;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Primitive;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class ColumnTest
 * @package DBD\Entity\Tests
 */
class ColumnTest extends TestCase
{
    /**
     * @throws EntityException
     */
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

    /**
     * @throws EntityException
     */
    public function testStringInstanceUsage()
    {
        $columnName = "table_column_name";
        $column = new Column($columnName);

        self::assertInstanceOf(Column::class, $column);
        self::assertEquals($columnName, $column->name);
    }

    /**
     *
     */
    public function testNullInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(null);
    }

    /**
     *
     */
    public function testBoolInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(true);
    }

    /**
     * @throws EntityException
     */
    public function testObjectInstanceUsage()
    {
        $this->expectException(EntityException::class);
        new Column(new stdClass());
    }
}
