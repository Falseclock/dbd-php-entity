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

namespace DBD\Entity\Common;

class Utils
{
    /**
     * @param array $bigArray
     * @param array $smallArray
     *
     * @return array
     */
    public static function arrayDiff(array $bigArray, array $smallArray)
    {
        foreach ($smallArray as $key => $value) {
            if (isset($bigArray[$key])) {
                unset($bigArray[$key]);
            }
        }

        return $bigArray;
    }

    /**
     * Returns value as a boolean.
     *
     * @param $variable
     *
     * @return bool
     */
    public static function convertBoolVar($variable)
    {
        if (is_scalar($variable))
            $variable = strtolower(trim($variable));

        switch ($variable) {
            case 't':
                return true;
            case 'f':
                return false;
            default:
                return filter_var($variable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
    }
}
