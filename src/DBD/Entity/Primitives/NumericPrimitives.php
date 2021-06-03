<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2021] [Nurlan Mukhanov <nurike@gmail.com>]                      *
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

namespace DBD\Entity\Primitives;


interface NumericPrimitives
{
    const FLOAT = "float";

    const INTEGER = "int";

    /** @var string Numeric values with decimal representation */
    public const Decimal = "Decimal";

    /** @var string IEEE 754 binary64 floating-point number (15-17 decimal digits) */
    public const Double = "Double";

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

    /** @var string Unsigned 8-bit integer */
    public const Byte = "Byte";
}
