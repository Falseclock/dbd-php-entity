<?php

namespace Falseclock\DBD\Entity;

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
 */
class Primitive extends Enum
{
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
	public const Duration = "Duration";
	/** @var string 16-byte (128-bit) unique identifier */
	public const Guid = "Guid";
	/** @var string Signed 16-bit integer */
	public const Int16 = "Int16";
	/** @var string Signed 32-bit integer */
	public const Int32 = "Int32";
	/** @var string Signed 64-bit integer */
	public const Int64 = "Int64";
	/** @var string Signed 8-bit integer */
	public const SByte = "SByte";
	/** @var string IEEE 754 binary32 floating-point number (6-9 decimal digits) */
	public const Single = "Single";
	/** @var string Binary data stream */
	public const Stream = "Stream";
	/** @var string Sequence of UTF-8 characters */
	public const String = "String";
	/** @var string Clock time 00:00-23:59:59.999999999999 */
	public const TimeOfDay = "TimeOfDay";
}