<?php
/*************************************************************************************
 *   MIT License                                                                     *
 *                                                                                   *
 *   Copyright (C) 2020 by Nurlan Mukhanov <nurike@gmail.com>                        *
 *                                                                                   *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy    *
 *   of this software and associated documentation files (the "Software"), to deal   *
 *   in the Software without restriction, including without limitation the rights    *
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell       *
 *   copies of the Software, and to permit persons to whom the Software is           *
 *   furnished to do so, subject to the following conditions:                        *
 *                                                                                   *
 *   The above copyright notice and this permission notice shall be included in all  *
 *   copies or substantial portions of the Software.                                 *
 *                                                                                   *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR      *
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,        *
 *   FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE    *
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER          *
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,   *
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE   *
 *   SOFTWARE.                                                                       *
 ************************************************************************************/

namespace DBD\Entity;

use ReflectionProperty;

final class MapperVariables
{
    public $columns;
    public $complex;
    public $constraints;
    public $embedded;
    public $otherColumns;

    /**
     * MapperVariables constructor.
     *
     * @param $columns
     * @param $constraints
     * @param $otherColumns
     * @param $embedded
     * @param $complex
     */
    public function __construct($columns, $constraints, $otherColumns, $embedded, $complex)
    {
        $this->columns = $this->filter($columns);
        $this->constraints = $this->filter($constraints);
        $this->otherColumns = $this->filter($otherColumns);
        $this->embedded = $this->filter($embedded);
        $this->complex = $this->filter($complex);
    }

    /**
     * @param ReflectionProperty[] $vars
     *
     * @return array
     */
    private function filter(array $vars)
    {
        $list = [];
        foreach ($vars as $varName => $varValue) {
            $list[] = $varName;
        }

        return $list;
    }
}
