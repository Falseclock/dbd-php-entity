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

namespace DBD\Entity\Tests\Fixtures;

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

class Data
{
    public static function getJsonTypeColumnData()
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

    public static function getDeclarationChainData()
    {
        return [
            AMap::meWithoutEnforcer()->a1->name => true,
            AMap::meWithoutEnforcer()->a2->name => true,
            AMap::meWithoutEnforcer()->a3->name => true,
        ];
    }

    public static function getUnUsedPropertyInMapperData()
    {
        return [
            UnUsedPropertyInMapperMap::meWithoutEnforcer()->id->name => 1,
        ];
    }

    public static function getJustComplexData()
    {
        return array_merge(self::getPersonFullEntityData(), self::getAddressData());
    }

    public static function getPersonFullEntityData()
    {
        return [
            PersonBaseMap::me()->name->name => 'Alfa',
            PersonBaseMap::me()->id->name => '1',
            PersonBaseMap::me()->email->name => 'alfa@at.com',
            PersonBaseMap::me()->registrationDate->name => '2020-09-21 20:48:28.918366+06',
            PersonBaseMap::me()->isActive->name => 't',
        ];
    }

    public static function getAddressData()
    {
        return [
            AddressMap::me()->id->name => 111,
            AddressMap::me()->street->name => "12 Downing street",
        ];
    }

    public static function getUserFullData()
    {
        return array_merge(self::getUserData(), self::getPersonData(), self::getCompanyData());
    }

    public static function getUserData()
    {
        return [
            UserMap::me()->id->name => 1,
            UserMap::me()->personId->name => 2,
            UserMap::me()->companyId->name => 3,
        ];
    }

    public static function getPersonData()
    {
        return [
            PersonMap::me()->id->name => 2,
            PersonMap::me()->firstName->name => "FirstName",
        ];
    }

    public static function getCompanyData()
    {
        return [
            CompanyMap::me()->id->name => 3,
            CompanyMap::me()->name->name => "Company Name",
        ];
    }

    public static function getUserNonFullData()
    {
        return self::getUserData();
    }

    public static function getLongChainData()
    {
        return array_merge(self::getUserData(), self::getLevelOneData(), self::getLevelTwoData());
    }

    public static function getLevelOneData()
    {
        return [
            LevelOneMap::me()->id->name => 111,
            LevelOneMap::me()->levelTwoId->name => 222,
        ];
    }

    public static function getLevelTwoData()
    {
        return [
            LevelTwoMap::me()->id->name => 222,
            LevelTwoMap::me()->levelOneId->name => 111,
            LevelTwoMap::me()->levelThreeId->name => 333,
        ];
    }

    public static function getCountryWithRegionsData()
    {
        return array_merge(self::getCountryData(), [CountryWithRegionsMap::me()->Regions->name => self::getRegionsJsonData()]);
    }

    public static function getCountryData()
    {
        return [
            CountryMap::me()->id->name => 9,
            CountryMap::me()->name->name => "Kazakhstan",
        ];
    }

    public static function getRegionsJsonData()
    {
        return json_encode(self::getRegionsData(), JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public static function getRegionsData()
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

    public static function getStreetWithZipCodeJsonData()
    {
        return array_merge(self::getStreetData(), [StreetWithZipCodeMap::me()->ZipCode->name => self::getZipCodeJsonData()]);
    }

    public static function getStreetData()
    {
        return [
            StreetMap::me()->id->name => 123,
            StreetMap::me()->name->name => "Nazarbayev",
        ];
    }

    public static function getZipCodeJsonData()
    {
        return json_encode(self::getZipCodeData(), JSON_UNESCAPED_UNICODE);
    }

    public static function getZipCodeData()
    {
        return [
            ZipCodeMap::me()->id->name => 480000,
            ZipCodeMap::me()->value->name => "050000",
        ];
    }

    public static function getStreetWithZipCodeNotJsonData()
    {
        return array_merge(self::getStreetData(), [StreetWithZipCodeMap::me()->ZipCode->name => self::getZipCodeData()]);
    }
}
