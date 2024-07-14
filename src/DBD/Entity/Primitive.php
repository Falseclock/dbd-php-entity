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

use DBD\Entity\Common\EntityException;
use DBD\Entity\Primitives\NumericPrimitives;
use DBD\Entity\Primitives\StringPrimitives;
use DBD\Entity\Primitives\TimePrimitives;
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
class Primitive extends Enum implements StringPrimitives, NumericPrimitives, TimePrimitives
{
    public const BOOLEAN = "boolean";

    /** @var string Binary-valued logic */
    public const Boolean = "Boolean";

    /**
     * @param string $type
     *
     * @return Primitive
     * @throws EntityException
     */
    public static function fromType(string $type): Primitive
    {
        switch (strtolower(trim($type))) {

            case 'bytea':
                return Primitive::Binary();

            case 'boolean':
            case 'bool':
                return Primitive::Boolean();

            case 'date':
            case 'timestamp':
                return Primitive::Date();

            case 'time':
            case 'timetz':
                return Primitive::TimeOfDay();

            case 'timestamptz':
                return Primitive::DateTimeOffset();
            case 'numeric':
            case 'decimal':
                return Primitive::Decimal();

            case 'float8':
                return Primitive::Double();

            case 'interval':
                return Primitive::Duration();

            case 'uuid':
                return Primitive::Guid();

            case 'int2':
            case 'smallint':
            case 'smallserial':
            case 'serial2':
                return Primitive::Int16();

            case 'int':
            case 'int4':
            case 'integer':
            case 'serial4':
            case 'serial':
                return Primitive::Int32();

            case 'int8':
            case 'bigint':
            case 'bigserial':
            case 'serial8':
                return Primitive::Int64();

            case 'float4':
            case 'real':
                return Primitive::Single();

            case 'varchar':
            case 'text':
            case 'cidr':
            case 'inet':
            case 'json':
            case 'jsonb':
            case 'macaddr':
            case 'macaddr8':
            case 'char':
            case 'tsquery':
            case 'tsvector':
            case 'xml':
            case 'bpchar':
                return Primitive::String();
        }

        throw new EntityException("Not described type found: $type");
    }

    /**
     * If you need to set your own types - just extend this class and override this method
     *
     * @return string
     */
    public function getPhpVarType(): string
    {
        return match ($this->value) {
            self::Int16, self::Int32, self::Int64 => self::INTEGER,
            self::Boolean => self::BOOLEAN,
            self::Double, self::Single => self::FLOAT,
            default => self::STRING,
        };
    }
}
