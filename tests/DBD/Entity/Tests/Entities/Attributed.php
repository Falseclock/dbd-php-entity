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

namespace DBD\Entity\Tests\Entities;

use DBD\Entity\Columns\BigIntColumn;
use DBD\Entity\Columns\IntColumn;
use DBD\Entity\Columns\JsonbColumn;
use DBD\Entity\Columns\JsonColumn;
use DBD\Entity\Columns\ShortIntColumn;
use DBD\Entity\Columns\StringColumn;
use DBD\Entity\Columns\TextColumn;
use DBD\Entity\Columns\TimeColumn;
use DBD\Entity\Columns\TimeStampColumn;
use DBD\Entity\Columns\TimeStampTZColumn;
use DBD\Entity\Entity;
use DBD\Entity\EntityTable;
use DBD\Entity\Interfaces\FullEntity;

#[EntityTable('public', 'attributed', 'Annotation')]
class Attributed extends Entity implements FullEntity
{
    const SCHEME = 'public';
    const TABLE = 'attributed';

    #[BigIntColumn(name: 'BigIntColumn', auto: true, primary: true, annotation: 'BigIntColumn')]
    public ?int $BigIntColumn = null;

    #[IntColumn(name: 'IntColumn', annotation: 'IntColumn')]
    public ?string $IntColumn = null;

    #[JsonbColumn(name: 'JsonbColumn', annotation: 'JsonbColumn')]
    public ?string $JsonbColumn = null;

    #[JsonColumn(name: 'JsonColumn', annotation: 'JsonColumn')]
    public ?string $JsonColumn = null;

    #[ShortIntColumn(name: 'ShortIntColumn', auto: true, annotation: 'ShortIntColumn')]
    public ?int $ShortIntColumn = null;

    #[StringColumn(name: 'StringColumn', length: 255, annotation: 'StringColumn')]
    public ?string $StringColumn = null;

    #[TextColumn(name: 'TextColumn', annotation: 'TextColumn')]
    public ?string $TextColumn = null;

    #[TimeColumn(name: 'TimeColumn', annotation: 'TimeColumn')]
    public ?string $TimeColumn = null;

    #[TimeStampColumn(name: 'TimeStampColumn', annotation: 'TimeStampColumn')]
    public ?string $TimeStampColumn = null;

    #[TimeStampTZColumn(name: 'TimeStampTZColumn', annotation: 'TimeStampTZColumn')]
    public ?string $TimeStampTZColumn = null;
}
