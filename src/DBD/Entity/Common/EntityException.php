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

namespace DBD\Entity\Common;

use Exception;

/**
 * Class EntityException
 *
 * @package DBD\Entity\Common
 */
class EntityException extends Exception
{
    /**
     * EntityException constructor.
     * Переопределим исключение так, что параметр message станет обязательным
     *
     * @param                $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
