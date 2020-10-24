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

/**
 * Class Embedded used when you generate value with view or with calculations
 * Should be always public when mapped in Mapper
 *
 * @package Falseclock\DBD\Entity
 */
class Embedded
{
    /** @var string MANDATORY option */
    public const DB_TYPE = "dbType";
    public const ENTITY_CLASS = "entityClass";
    public const IS_ITERABLE = "isIterable";
    public const NAME = "name";
    /** @var string $name name of the columns in view or selected with AS */
    public $name;
    /** @var bool $isIterable */
    public $isIterable = false;
    /** @var string $entityClass default empty. Will be converted to Entity if not null */
    public $entityClass;
    /** @var Type $dbType */
    public $dbType;

    /**
     * Embedded constructor.
     *
     * @param null $arrayOfValues
     */
    public function __construct($arrayOfValues = null)
    {
        if (isset($arrayOfValues)) {
            foreach ($arrayOfValues as $key => $value) {
                if (property_exists($this, $key))
                    $this->$key = $value;
            }
        }
    }
}
