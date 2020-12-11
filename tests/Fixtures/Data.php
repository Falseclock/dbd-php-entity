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

namespace DBD\Entity\Tests\Fixtures;

use DBD\Entity\Common\EntityException;
use DBD\Entity\Tests\Entities\AddressMap;
use DBD\Entity\Tests\Entities\Constraint\CompanyMap;
use DBD\Entity\Tests\Entities\Constraint\LevelOneMap;
use DBD\Entity\Tests\Entities\Constraint\LevelTwoMap;
use DBD\Entity\Tests\Entities\Constraint\PersonMap;
use DBD\Entity\Tests\Entities\Constraint\UserMap;
use DBD\Entity\Tests\Entities\DeclarationChain\AMap;
use DBD\Entity\Tests\Entities\Embedded\CountryMap;
use DBD\Entity\Tests\Entities\Embedded\CountryWithRegionsMap;
use DBD\Entity\Tests\Entities\Embedded\RegionMap;
use DBD\Entity\Tests\Entities\Embedded\StreetMap;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCodeMap;
use DBD\Entity\Tests\Entities\Embedded\ZipCodeMap;
use DBD\Entity\Tests\Entities\JsonTypeColumnMap;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use DBD\Entity\Tests\Entities\UnUsedPropertyInMapperMap;

/**
 * Class Data
 * @package DBD\Entity\Tests\Fixtures
 */
class Data
{
    /**
     * @return string[]
     */
    public static function getTableStructureData(): array
    {
        return [
            "table_schema" => "tender",
            "table_name" => "tender_lots",
            "table_type" => "BASE TABLE",
            "table_comment" => "Таблица лотов, которые принадлежат определенному тендеру.\\n\\nВниание на опции тендера. Через coalesce проверяется есть ли кастомная опция для лота, затем проверяется настройка для компании и потом только берется стандартная настройка",
            "table_columns" => "[{\"column_name\": \"category_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на категорию в которой открыт лот.\", \"column_position\": 16, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"currency_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на валюту\", \"column_position\": 17, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"delivery_entity_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на регион поставки конкретного лота\", \"column_position\": 15, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"measure_unit_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на единицу измерения\", \"column_position\": 18, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на тендер, под которым открывали лот\", \"column_position\": 2, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_budget\", \"column_type\": \"numeric\", \"column_comment\": \"Бюджет лота за единицу товара или услуги\", \"column_position\": 5, \"column_udt_type\": \"numeric\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"0\", \"column_numeric_scale\": 15, \"column_character_length\": null, \"column_numeric_precision\": 30, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_date_commit\", \"column_type\": \"timestamp with time zone\", \"column_comment\": null, \"column_position\": 19, \"column_udt_type\": \"timestamptz\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": 6, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_date_start\", \"column_type\": \"timestamp with time zone\", \"column_comment\": \"Дата начала приема заявок по лоту\", \"column_position\": 7, \"column_udt_type\": \"timestamptz\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"now()\", \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": 6, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_date_stop\", \"column_type\": \"timestamp with time zone\", \"column_comment\": \"Дата окончания приема заявок. Лот может автоматически продлятся и эта дата ответственна за это\", \"column_position\": 8, \"column_udt_type\": \"timestamptz\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": 6, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_description\", \"column_type\": \"text\", \"column_comment\": \"Более расширенное описание лота\", \"column_position\": 4, \"column_udt_type\": \"text\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_fts\", \"column_type\": \"tsvector\", \"column_comment\": \"Полнотекстовый поиск по лоту\\n\\nFIXME: изменить на not null после ввода новой страницы создания лота\", \"column_position\": 9, \"column_udt_type\": \"tsvector\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_id\", \"column_type\": \"integer\", \"column_comment\": \"Номер тендерного лота, уникальный, серийный\", \"column_position\": 1, \"column_udt_type\": \"int4\", \"column_is_primary\": true, \"column_is_nullable\": false, \"column_default_value\": \"nextval('auctions_auctionid_seq'::regclass)\", \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_is_active\", \"column_type\": \"boolean\", \"column_comment\": \"Статус достуности лота к публичному показу. По сути это статус удаления\", \"column_position\": 10, \"column_udt_type\": \"bool\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"true\", \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_is_open_for_bid\", \"column_type\": \"boolean\", \"column_comment\": \"Триггерное поле, становится TRUE если статус OPEN_FOR_BID или PUBLISHED. Нужно для сортировки по сперва завершающимся.\\r\\n\\r\\n\\r\\nСмотри функцию tender.tender_lot_open_for_bid_states()\", \"column_position\": 20, \"column_udt_type\": \"bool\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"false\", \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_is_public\", \"column_type\": \"boolean\", \"column_comment\": \"Доступ к лоту\", \"column_position\": 14, \"column_udt_type\": \"bool\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"true\", \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_is_searchable\", \"column_type\": \"boolean\", \"column_comment\": \"Триггерное поле. Если лот не активный или статус не публичный, то false, иначе true\", \"column_position\": 21, \"column_udt_type\": \"bool\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"false\", \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_name\", \"column_type\": \"character varying\", \"column_comment\": \"Наименовение лота\", \"column_position\": 3, \"column_udt_type\": \"varchar\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": 512, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_next_tender_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на следующий тендер. Например в лоте квал отбора объявили победителей и на основании него создали новый тендер - второй этап.\", \"column_position\": 22, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_quantity\", \"column_type\": \"numeric\", \"column_comment\": \"Количество поставляемых товаров или услуг\", \"column_position\": 6, \"column_udt_type\": \"numeric\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": \"0\", \"column_numeric_scale\": 5, \"column_character_length\": null, \"column_numeric_precision\": 22, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_state_explanation\", \"column_type\": \"text\", \"column_comment\": \"Комментарий к статусу лота. Обычно устанавливается, когда статус изменяется на невозможный к дальнейшему участию в лоте\", \"column_position\": 11, \"column_udt_type\": \"text\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": null, \"column_character_length\": null, \"column_numeric_precision\": null, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_state_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на статус лота в тендерах\", \"column_position\": 13, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": true, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}, {\"column_name\": \"tender_lot_type_id\", \"column_type\": \"integer\", \"column_comment\": \"Ссылка на тип лота\", \"column_position\": 12, \"column_udt_type\": \"int4\", \"column_is_primary\": false, \"column_is_nullable\": false, \"column_default_value\": null, \"column_numeric_scale\": 0, \"column_character_length\": null, \"column_numeric_precision\": 32, \"column_datetime_precision\": null, \"column_interval_precision\": null}]",
            "table_constraints" => "[{\"constraint_name\": \"tender_lots_currencies_ref_currencies\", \"constraint_local_column_name\": \"currency_id\", \"constraint_foreign_table_name\": \"currencies\", \"constraint_foreign_column_name\": \"currency_id\", \"constraint_foreign_table_schema\": \"handbook\"}, {\"constraint_name\": \"tender_lots_ref_categories\", \"constraint_local_column_name\": \"category_id\", \"constraint_foreign_table_name\": \"categories\", \"constraint_foreign_column_name\": \"category_id\", \"constraint_foreign_table_schema\": \"handbook\"}, {\"constraint_name\": \"tender_lots_ref_delivery_entities\", \"constraint_local_column_name\": \"delivery_entity_id\", \"constraint_foreign_table_name\": \"delivery_entities\", \"constraint_foreign_column_name\": \"delivery_entity_id\", \"constraint_foreign_table_schema\": \"handbook\"}, {\"constraint_name\": \"tender_lots_ref_measure_units\", \"constraint_local_column_name\": \"measure_unit_id\", \"constraint_foreign_table_name\": \"measure_units\", \"constraint_foreign_column_name\": \"measure_unit_id\", \"constraint_foreign_table_schema\": \"handbook\"}, {\"constraint_name\": \"tender_lots_ref_tender_lot_states\", \"constraint_local_column_name\": \"tender_lot_state_id\", \"constraint_foreign_table_name\": \"tender_lot_states\", \"constraint_foreign_column_name\": \"tender_lot_state_id\", \"constraint_foreign_table_schema\": \"tender\"}, {\"constraint_name\": \"tender_lots_ref_tender_lot_types\", \"constraint_local_column_name\": \"tender_lot_type_id\", \"constraint_foreign_table_name\": \"tender_lot_types\", \"constraint_foreign_column_name\": \"tender_lot_type_id\", \"constraint_foreign_table_schema\": \"tender\"}, {\"constraint_name\": \"tender_lots_ref_tenders\", \"constraint_local_column_name\": \"tender_id\", \"constraint_foreign_table_name\": \"tenders_new\", \"constraint_foreign_column_name\": \"tender_id\", \"constraint_foreign_table_schema\": \"tender\"}, {\"constraint_name\": \"tender_lots_ref_tenders_next\", \"constraint_local_column_name\": \"tender_lot_next_tender_id\", \"constraint_foreign_table_name\": \"tenders_new\", \"constraint_foreign_column_name\": \"tender_id\", \"constraint_foreign_table_schema\": \"tender\"}]"
        ];
    }

    /**
     * @return string[]
     * @throws EntityException
     */
    public static function getJsonTypeColumnData(): array
    {
        return [
            JsonTypeColumnMap::me()->json->name => '{
                                                      "person_email":             "email",
                                                      "person_id":                "id",
                                                      "person_is_active":         "isActive",
                                                      "person_name":              "name",
                                                      "person_registration_date": "registrationDate"
                                                    }',
        ];
    }

    /**
     * @return bool[]
     * @throws EntityException
     */
    public static function getDeclarationChainData(): array
    {
        return [
            AMap::meWithoutEnforcer()->a1->name => true,
            AMap::meWithoutEnforcer()->a2->name => true,
            AMap::meWithoutEnforcer()->a3->name => true,
        ];
    }

    /**
     * @return int[]
     * @throws EntityException
     */
    public static function getUnUsedPropertyInMapperData(): array
    {
        return [
            UnUsedPropertyInMapperMap::meWithoutEnforcer()->id->name => 1,
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getJustComplexData(): array
    {
        return array_merge(self::getPersonFullEntityData(), self::getAddressData());
    }

    /**
     * @return string[]
     * @throws EntityException
     */
    public static function getPersonFullEntityData(): array
    {
        return [
            PersonBaseMap::me()->name->name => 'Alfa',
            PersonBaseMap::me()->id->name => '1',
            PersonBaseMap::me()->email->name => 'alfa@at.com',
            PersonBaseMap::me()->registrationDate->name => '2020-09-21 20:48:28.918366+06',
            PersonBaseMap::me()->isActive->name => 't',
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getAddressData(): array
    {
        return [
            AddressMap::me()->id->name => 111,
            AddressMap::me()->street->name => "12 Downing street",
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getUserFullData(): array
    {
        return array_merge(self::getUserData(), self::getPersonData(), self::getCompanyData());
    }

    /**
     * @return int[]
     * @throws EntityException
     */
    public static function getUserData(): array
    {
        return [
            UserMap::me()->id->name => 1,
            UserMap::me()->personId->name => 2,
            UserMap::me()->companyId->name => 3,
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getPersonData(): array
    {
        return [
            PersonMap::me()->id->name => 2,
            PersonMap::me()->firstName->name => "FirstName",
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getCompanyData(): array
    {
        return [
            CompanyMap::me()->id->name => 3,
            CompanyMap::me()->name->name => "Company Name",
        ];
    }

    /**
     * @return int[]
     * @throws EntityException
     */
    public static function getUserNonFullData(): array
    {
        return self::getUserData();
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getLongChainData(): array
    {
        return array_merge(self::getUserData(), self::getLevelOneData(), self::getLevelTwoData());
    }

    /**
     * @return int[]
     * @throws EntityException
     */
    public static function getLevelOneData(): array
    {
        return [
            LevelOneMap::me()->id->name => 111,
            LevelOneMap::me()->levelTwoId->name => 222,
        ];
    }

    /**
     * @return int[]
     * @throws EntityException
     */
    public static function getLevelTwoData(): array
    {
        return [
            LevelTwoMap::me()->id->name => 222,
            LevelTwoMap::me()->levelOneId->name => 111,
            LevelTwoMap::me()->levelThreeId->name => 333,
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getCountryWithRegionsData(): array
    {
        return array_merge(self::getCountryData(), [CountryWithRegionsMap::me()->Regions->name => self::getRegionsJsonData()]);
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getCountryData(): array
    {
        return [
            CountryMap::me()->id->name => 9,
            CountryMap::me()->name->name => "Kazakhstan",
        ];
    }

    /**
     * @return false|string
     * @throws EntityException
     */
    public static function getRegionsJsonData()
    {
        return json_encode(self::getRegionsData(), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array[]
     * @throws EntityException
     */
    public static function getRegionsData(): array
    {
        return [
            [
                RegionMap::me()->id->name => 1,
                RegionMap::me()->name->name => "North Region",
            ],
            [
                RegionMap::me()->id->name => 2,
                RegionMap::me()->name->name => "South Region",
            ],
            [
                RegionMap::me()->id->name => 3,
                RegionMap::me()->name->name => "East Region",
            ],
            [
                RegionMap::me()->id->name => 4,
                RegionMap::me()->name->name => "West Region",
            ],
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getStreetWithZipCodeJsonData(): array
    {
        return array_merge(self::getStreetData(), [StreetWithZipCodeMap::me()->ZipCode->name => self::getZipCodeJsonData()]);
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getStreetData(): array
    {
        return [
            StreetMap::me()->id->name => 123,
            StreetMap::me()->name->name => "Nazarbayev",
        ];
    }

    /**
     * @return false|string
     * @throws EntityException
     */
    public static function getZipCodeJsonData()
    {
        return json_encode(self::getZipCodeData(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getZipCodeData(): array
    {
        return [
            ZipCodeMap::me()->id->name => 480000,
            ZipCodeMap::me()->value->name => "050000",
        ];
    }

    /**
     * @return array
     * @throws EntityException
     */
    public static function getStreetWithZipCodeNotJsonData(): array
    {
        return array_merge(self::getStreetData(), [StreetWithZipCodeMap::me()->ZipCode->name => self::getZipCodeData()]);
    }
}
