<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2024] [Nick Ispandiarov <nikolay.i@maddevs.io>]                 *
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
use DBD\Entity\Entity;
use DBD\Entity\EntityTable;
use DBD\Entity\Interfaces\FullEntity;
use DBD\Entity\Primitive;
use PHPUnit\Framework\TestCase;

class CustomColumnTest extends TestCase
{
    /**
     * @return void
     */
    public function testInEntity(): void
    {
        $data = [
            'test_value'         => 12.101
        ];

        $entity = new #[EntityTable('public', 'test')] class($data) extends Entity implements FullEntity {
            const SCHEME = 'public';
            const TABLE = 'test';

            #[CustomColumn(
                name: 'test_value',
                primitiveType: Primitive::Single,
                originType: 'float4',
                length: 10,
                precision: 2,
                annotation: 'Test id'
            )]
            public ?float $value;
        };

        self::assertSame($data['test_value'], $entity->value);
    }
}