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

namespace DBD\Entity;

use ReflectionProperty;

/**
 * Class MapperVariables
 *
 * @package DBD\Entity
 */
final class MapperVariables
{
    public $columns;
    public $complex;
    public $constraints;
    public $embedded;
    //public $otherColumns;

    /**
     * MapperVariables constructor.
     *
     * @param $columns
     * @param $constraints
     * @param $embedded
     * @param $complex
     */
    public function __construct($columns, $constraints, $embedded, $complex) // $otherColumns,
    {
        $this->columns = $this->filter($columns);
        $this->constraints = $this->filter($constraints);
        $this->embedded = $this->filter($embedded);
        $this->complex = $this->filter($complex);
        //$this->otherColumns = $this->filter($otherColumns);
    }

    /**
     * @param ReflectionProperty[] $vars
     *
     * @return array
     */
    private function filter(array $vars): array
    {
        $list = [];
        foreach ($vars as $varName => $varValue) {
            $list[] = $varName;
        }

        return $list;
    }
}
