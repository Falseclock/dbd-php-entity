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

use DBD\Common\DBDException;
use ReflectionClass;
use ReflectionException;

class Join
{
    const MANY_TO_MANY = "manyToMany";
    const MANY_TO_ONE = "manyToOne";
    const ONE_TO_MANY = "oneToMany";
    const ONE_TO_ONE = "oneToOne";
    /** @var string $type */
    public $type;

    /**
     * Join constructor.
     *
     * @param $type
     *
     * @throws DBDException
     * @throws ReflectionException
     */
    public function __construct($type)
    {
        foreach ($this->getConstants() as $name => $value) {
            if ($value == $type) {
                $this->type = $type;

                return;
            }
        }
        throw new DBDException("Unknown join type {$type}");
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function getConstants(): iterable
    {
        $r = new ReflectionClass(self::class);

        return $r->getConstants();
    }

    /**
     * @return string
     * @throws DBDException
     * @throws ReflectionException
     */
    public function getConstantName(): string
    {
        foreach ($this->getConstants() as $name => $value) {
            if ($value == $this->type) {
                return $name;
            }
        }
        throw new DBDException("Something strange happen");
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
