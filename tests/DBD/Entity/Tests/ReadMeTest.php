<?php
/********************************************************************************
 *   Apache License, Version 2.0                                                *
 *                                                                              *
 *   Copyright [2022] [Nurlan Mukhanov <nurike@gmail.com>]                      *
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

namespace DBD\Entity\Tests;

use DBD\Entity\Tests\Entities\Currency;
use PHPUnit\Framework\TestCase;

class ReadMeTest extends TestCase
{
    /**
     * @throws \DBD\Entity\Common\EntityException
     */
    public function testReadMe()
    {

        $array = [
            [
                "currency_id" => 505,
                "currency_name" => "Доллар США",
                "currency_short_name" => "Доллар",
                "currency_abbreviation" => "долл",
                "currency_symbol" => "$",
                "currency_code" => "USD"
            ],
            [
                "currency_id" => 36,
                "currency_name" => "Казахстанский Тенге",
                "currency_short_name" => "Тенге",
                "currency_abbreviation" => "тг",
                "currency_symbol" => "₸",
                "currency_code" => "KZT"
            ],
            [
                "currency_id" => 506,
                "currency_name" => "Российский Рубль",
                "currency_short_name" => "Рубль",
                "currency_abbreviation" => "руб",
                "currency_symbol" => "₽",
                "currency_code" => "RUB"
            ],
            [
                "currency_id" => 548,
                "currency_name" => "Евро",
                "currency_short_name" => "Евро",
                "currency_abbreviation" => "евро",
                "currency_symbol" => "€",
                "currency_code" => "EUR"
            ]
        ];
        $objects = [];
        foreach ($array as $item) {
            $objects[] = new Currency($item);
        }
        self::assertCount(count($array), $objects);
        self::assertTrue(true);
    }
}
