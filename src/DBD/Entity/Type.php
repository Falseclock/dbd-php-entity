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

use MyCLabs\Enum\Enum;

/**
 * Class Type describes DB types for Embedded properties
 *
 * @package DBD\Entity
 * @method static Type Json()
 * @method static Type Varchar()
 * @method static Type Char()
 * @method static Type BigInt()
 * @method static Type Double()
 * @method static Type Int()
 * @method static Type Array()
 * @method static Type Object()
 */
class Type extends Enum
{
    public const Array = "array";
    public const BigInt = "bigint";
    public const Boolean = "boolean";
    public const Char = "char";
    public const Double = "double";
    public const Int = "int";
    public const Json = "json";
    public const Varchar = "varchar";
    public const Object = "object";
}
