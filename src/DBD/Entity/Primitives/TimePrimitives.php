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

interface TimePrimitives
{
    /** @var string Date without a time-zone offset */
    public const Date = "Date";

    /** @var string Date and time with a time-zone offset, no leap seconds */
    public const DateTimeOffset = "DateTimeOffset";

    /** @var string Signed duration in days, hours, minutes, and (sub)seconds */
    public const Duration = "Duration";

    /** @var string Clock time 00:00-23:59:59.999999999999 */
    public const TimeOfDay = "TimeOfDay";
}
