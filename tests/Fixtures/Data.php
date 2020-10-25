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
use DBD\Entity\Tests\Entities\DeclarationChain\AMap;
use DBD\Entity\Tests\Entities\JsonTypeColumnMap;
use DBD\Entity\Tests\Entities\PersonMap;
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
            PersonMap::me()->name->name => 'Alfa',
            PersonMap::me()->id->name => '1',
            PersonMap::me()->email->name => 'alfa@at.com',
            PersonMap::me()->registrationDate->name => '2020-09-21 20:48:28.918366+06',
            PersonMap::me()->isActive->name => 't',
        ];
    }

    public static function getAddressData()
    {
        return [
            AddressMap::me()->id->name => 111,
            AddressMap::me()->street->name => "12 Downing street",
        ];
    }
}
