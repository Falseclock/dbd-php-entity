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

use MyCLabs\Enum\Enum;

/**
 * Class Primitive
 *
 * @see https://docs.oasis-open.org/odata/odata-csdl-xml/v4.01/csprd05/odata-csdl-xml-v4.01-csprd05.html#sec_PrimitiveTypes
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
    public function getPhpVarType()
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
