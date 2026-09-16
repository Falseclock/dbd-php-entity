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

namespace DBD\Entity\Tests\Entities\Characterization;

use DBD\Entity\Columns\IntColumn;
use DBD\Entity\Columns\TextColumn;
use DBD\Entity\Entity;
use DBD\Entity\EntityTable;
use DBD\Entity\Interfaces\FullEntity;

/**
 * Attribute-mapped entity that is used by DynamicPropertiesTest only, so that the first construction
 * of its MapperAttributed instance is guaranteed to happen inside that test.
 */
#[EntityTable('public', 'attributed_plain', 'Attribute mapped fixture')]
class AttributedPlain extends Entity implements FullEntity
{
    const SCHEME = 'public';
    const TABLE = 'attributed_plain';

    #[IntColumn(name: 'attributed_plain_id', auto: true, primary: true, annotation: 'Identifier')]
    public ?int $id = null;

    #[TextColumn(name: 'attributed_plain_title', annotation: 'Title')]
    public ?string $title = null;
}
