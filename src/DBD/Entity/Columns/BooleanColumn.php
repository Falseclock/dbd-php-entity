<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2024] [Nurlan Mukhanov <nurike@gmail.com>]                      *
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

namespace DBD\Entity\Columns;

use Attribute;
use DBD\Entity\Column;
use DBD\Entity\Primitive;

/**
 * Class BooleanColumn
 *
 * @package DBD\Entity\Columns
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BooleanColumn extends Column
{
    public function __construct(
        string  $name,
        bool    $nullable = true,
        ?string $annotation = null
    )
    {
        parent::__construct([
            Column::NAME => $name,
            Column::PRIMITIVE_TYPE => Primitive::Boolean,
            Column::ORIGIN_TYPE => 'boolean',
            Column::NULLABLE => $nullable,
            Column::ANNOTATION => $annotation
        ]);
    }
}
