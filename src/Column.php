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

class Column
{
    const ANNOTATION = "annotation";
    const DEFAULT = "defaultValue";
    const IS_AUTO = "isAuto";
    const KEY = "key";
    const MAXLENGTH = "maxLength";
    const NAME = "name";
    const NULLABLE = "nullable";
    const ORIGIN_TYPE = "originType";
    const PRECISION = "precision";
    /**
     * @see Primitive
     * @var string Primitive Type
     */
    const PRIMITIVE_TYPE = "type";
    const SCALE = "scale";
    /** @var string $annotation TODO: Annotation|Annotation[] */
    public $annotation;
    /** @var mixed $defaultValue */
    public $defaultValue;
    /** @var boolean $isAuto does column have auto increment or auto generated value? */
    public $isAuto = false;
    /** @var boolean $key Flag of Primary key */
    public $key;
    /** @var int $maxLength */
    public $maxLength;
    /** @var string $name name of column in database */
    public $name;
    /** @var bool $nullable */
    public $nullable;
    /** @var string $type type of column as written in database */
    public $originType;
    /** @var int $precision */
    public $precision;
    /** @var mixed $scale */
    public $scale;
    /** @var Primitive $type */
    public $type;

    /**
     * Column constructor.
     *
     * @param null $columnNameOrArray
     */
    public function __construct($columnNameOrArray = null)
    {
        if (isset($columnNameOrArray)) {
            if (is_string($columnNameOrArray)) {
                $this->name = $columnNameOrArray;
            }

            if (is_array($columnNameOrArray)) {
                foreach ($columnNameOrArray as $key => $value) {
                    if ($key == self::PRIMITIVE_TYPE) {
                        $this->type = new Primitive($value);
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }
    }
}
