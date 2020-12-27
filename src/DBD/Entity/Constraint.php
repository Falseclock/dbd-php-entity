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

namespace DBD\Entity;

/**
 * Class Constraint
 *
 * @package DBD\Entity
 */
class Constraint
{
    const BASE_CLASS = "class";
    const FOREIGN_COLUMN = "foreignColumn";
    const FOREIGN_SCHEME = "foreignScheme";
    const FOREIGN_TABLE = "foreignTable";
    const JOIN_TYPE = "join";
    const LOCAL_COLUMN = "localColumn";
    const LOCAL_TABLE = "localTable";
    /** @var Column|string $localColumn */
    public $localColumn;
    /** @var Table $localTable */
    public $localTable;
    /** @var Table $foreignTable */
    public $foreignTable;
    /** @var Column $foreignColumn */
    public $foreignColumn;
    /** @var Join $joinType */
    public $join;
    /** @var string $class */
    public $class;
    /** @var string $foreignScheme */
    public $foreignScheme;

    /**
     * Constraint constructor.
     *
     * @param array|null $constraintParams
     */
    public function __construct(?array $constraintParams = null)
    {
        if (isset($constraintParams) and is_array($constraintParams)) {
            foreach ($constraintParams as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
