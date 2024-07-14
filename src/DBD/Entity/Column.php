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

use Attribute;
use DBD\Entity\Common\EntityException;

/**
 * Class Column
 *
 * @package DBD\Entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
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
    /** @var string $annotation */
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
     * @param array|string|null $columnNameOrArray
     * @throws EntityException
     */
    public function __construct(array|string|null $columnNameOrArray)
    {
        if (isset($columnNameOrArray)) {
            if (is_string($columnNameOrArray)) {
                $this->name = $columnNameOrArray;
            } else if (is_array($columnNameOrArray)) {
                foreach ($columnNameOrArray as $key => $value) {
                    if ($key == self::PRIMITIVE_TYPE) {
                        $this->type = new Primitive($value);
                    } else {
                        $this->$key = $value;
                    }
                }
            }
        }

        if (!isset($this->name)) {
            throw new EntityException(sprintf("columns not set: %s. Check that Embedded and Complex fields are protected type.", json_encode($columnNameOrArray)));
        }
    }
}
