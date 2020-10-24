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

    /**
     * @param string $class
     *
     * @return array
     */
    public static function getClassVars(string $class)
    {
        return get_class_vars($class);
    }

    /**
     * @param $object
     *
     * @return array
     */
    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }
}
