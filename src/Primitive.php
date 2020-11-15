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

use MyCLabs\Enum\Enum;

/**
 * Class Primitive
 *
 * @see     https://docs.oasis-open.org/odata/odata-csdl-xml/v4.01/csprd05/odata-csdl-xml-v4.01-csprd05.html#sec_PrimitiveTypes
 * @method static Primitive Binary()
 * @method static Primitive Boolean()
 * @method static Primitive Byte()
 * @method static Primitive Date()
 * @method static Primitive DateTimeOffset()
 * @method static Primitive Decimal()
 * @method static Primitive Double()
 * @method static Primitive Duration()
 * @method static Primitive Guid()
 * @method static Primitive Int16()
 * @method static Primitive Int32()
 * @method static Primitive Int64()
 * @method static Primitive SByte()
 * @method static Primitive Single()
 * @method static Primitive Stream()
 * @method static Primitive String()
 * @method static Primitive TimeOfDay()
 * @package DBD\Entity
 */
class Primitive extends Enum
{
    private const BOOLEAN = "boolean";
    /** @var string Binary data */
    public const Binary = "Binary";
    /** @var string Binary-valued logic */
    public const Boolean = "Boolean";
    /** @var string Unsigned 8-bit integer */
    public const Byte = "Byte";
    /** @var string Date without a time-zone offset */
    public const Date = "Date";
    /** @var string Date and time with a time-zone offset, no leap seconds */
    public const DateTimeOffset = "DateTimeOffset";
    /** @var string Numeric values with decimal representation */
    public const Decimal = "Decimal";
    /** @var string IEEE 754 binary64 floating-point number (15-17 decimal digits) */
    public const Double = "Double";
    /** @var string Signed duration in days, hours, minutes, and (sub)seconds */
    public const  Duration = "Duration";
    private const FLOAT = "float";
    /** @var string 16-byte (128-bit) unique identifier */
    public const  Guid = "Guid";
    private const INTEGER = "int";
    /** @var string Signed 16-bit integer */
    public const Int16 = "Int16";
    /** @var string Signed 32-bit integer */
    public const Int32 = "Int32";
    /** @var string Signed 64-bit integer */
    public const Int64 = "Int64";
    /** @var string Signed 8-bit integer */
    public const  SByte = "SByte";
    private const STRING = "string";
    /** @var string IEEE 754 binary32 floating-point number (6-9 decimal digits) */
    public const Single = "Single";
    /** @var string Binary data stream */
    public const Stream = "Stream";
    /** @var string Sequence of UTF-8 characters */
    public const String = "String";
    /** @var string Clock time 00:00-23:59:59.999999999999 */
    public const TimeOfDay = "TimeOfDay";

    /**
     * If you need to set your own types - just extend this class and override this method
     *
     * @return string
     */
    public function getPhpVarType(): string
    {
        switch ($this->value) {
            case self::Int16:
            case self::Int32:
            case self::Int64:
                return self::INTEGER;

            case self::Boolean;
                return self::BOOLEAN;

            case self::Double:
            case self::Single;
                return self::FLOAT;
            default:
                return self::STRING;
        }
    }
}
