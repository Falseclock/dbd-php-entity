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

class ConstraintRaw extends Constraint
{
    const BASE_CLASS = "class";
    const FOREIGN_COLUMN = "foreignColumn";
    const FOREIGN_SCHEME = "foreignScheme";
    const FOREIGN_TABLE = "foreignTable";
    const JOIN_TYPE = "join";
    const LOCAL_COLUMN = "localColumn";
    const LOCAL_TABLE = "localTable";
    /** @var string $localColumn */
    public $localColumn;
    /** @var string $localTable */
    public $localTable;
    /** @var string $foreignTable */
    public $foreignTable;
    /** @var string $foreignColumn */
    public $foreignColumn;
    /** @var string $joinType */
    public $join;
    /** @var string $class */
    public $class;

    /**
     * ConstraintRaw constructor.
     *
     * @param array|null $constraint
     */
    public function __construct(?array $constraint = null)
    {
        if (isset($constraint)) {
            foreach ($constraint as $key => $value) {
                $this->$key = $value;
            }
        }
    }
}
