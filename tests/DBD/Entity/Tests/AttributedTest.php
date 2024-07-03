<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2024] [Nick Ispandiarov <nikolay.i@maddevs.io>]                      *
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
use DBD\Entity\Tests\Entities\Attributed;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class AttributedTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstruction(): void
    {
        new Attributed();

        self::assertTrue(true);
    }

    /**
     * @throws ReflectionException
     * @throws EntityException
     */
    public function testAttribute(): void
    {
        $map = Attributed::map();

        $columns= $map->getColumns();

        foreach ($columns as $column) {
            self::assertInstanceOf(Column::class, $column);
            self::assertSame($column->name, $column->annotation);
        }

        $table = $map->getTable();
        self::assertSame($table->name, Attributed::TABLE);
        self::assertSame($table->scheme, Attributed::SCHEME);

    }
}
