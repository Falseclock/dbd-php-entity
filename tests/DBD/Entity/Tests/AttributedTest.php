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
use DBD\Entity\Entity;
use DBD\Entity\Tests\Entities\Attributed;
use DBD\Entity\Tests\Entities\SelfReference\FourEmbedded;
use DBD\Entity\Tests\Entities\SelfReference\ThreeEmbedded;
use DBD\Entity\Tests\Entities\SelfReference\TwoEmbedded;
use PHPUnit\Framework\TestCase;

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
     * @return void
     * @throws \Throwable
     */
    public function testAttribute(): void
    {
        $map = Attributed::map();

        $columns = $map->getColumns();

        foreach ($columns as $column) {
            self::assertInstanceOf(Column::class, $column);
            self::assertSame($column->name, $column->annotation);
        }

        $table = $map->getTable();

        self::assertSame($table->name, Attributed::TABLE);
        self::assertSame($table->scheme, Attributed::SCHEME);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function testEmbedded(): void
    {
        $map = Attributed::map();

        $data = [
            $map->BigIntColumn->name  => 1,
            $map->IntColumn->name  => 1,
            $map->JsonbColumn->name  => null,
            $map->JsonColumn->name  => null,
            $map->ShortIntColumn->name  => 1,
            $map->StringColumn->name  => '1',
            $map->TextColumn->name  => '1',
            $map->TimeColumn->name  => date('Y-m-d H:i:s'),
            $map->TimeStampColumn->name  => ''.time(),
            $map->TimeStampTZColumn->name  => ''.time(),
            'two'   => [
                TwoEmbedded::map()->id->name => 2,
                'three'     => [
                    ThreeEmbedded::map()->id->name  => 3,
                    'four'      => [
                        FourEmbedded::map()->id->name   => 4,
                        'one'       => [
                            'one_id'    => 1
                        ]
                    ]
                ]
            ]
        ];

        $entity = new Attributed($data, 2);
        self::assertInstanceOf(Entity::class, $entity);
        self::assertCount(1, get_object_vars($entity->TwoEmbedded->ThreeEmbedded));

        $entity = new Attributed($data, 3);
        self::assertCount(2, get_object_vars($entity->TwoEmbedded->ThreeEmbedded));

        $this->expectException(EntityException::class);
        new Attributed($data, 4);
    }
}
