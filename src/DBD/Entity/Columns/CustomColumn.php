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
use DBD\Entity\Primitives\StringPrimitives;

/**
 * Class CustomColumn
 *
 * @package DBD\Entity\Columns
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class CustomColumn extends Column
{
    public function __construct(
        string  $name,
        string  $primitiveType,
        string  $originType,
        int     $length = null,
        int     $precision = null,
        bool    $nullable = false,
        bool    $primary = false,
        ?string $defaultValue = null,
        ?string $annotation = null,
    )
    {
        parent::__construct([
            Column::NAME => $name,
            Column::PRIMITIVE_TYPE => $primitiveType,
            Column::ORIGIN_TYPE => $originType,
            Column::MAXLENGTH => $length,
            Column::PRECISION => $precision,
            Column::NULLABLE => $nullable,
            Column::KEY => $primary,
            Column::DEFAULT => $defaultValue,
            Column::ANNOTATION => $annotation
        ]);
    }
}
