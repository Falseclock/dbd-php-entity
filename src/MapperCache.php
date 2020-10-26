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

namespace DBD\Entity;

use DBD\Common\Singleton;

/**
 * Class MapperCache used to avoid interfering with local variables in child classes
 *
 * @package DBD\Entity
 */
class MapperCache extends Singleton
{
    /** @var array $allVariables */
    public $allVariables = [];
    /** @var array $baseColumns */
    public $baseColumns = [];
    /** @var array $columns */
    public $columns = [];
    /** @var array $complex */
    public $complex = [];
    /** @var array $constraints */
    public $constraints = [];
    /** @var array $embedded */
    public $embedded = [];
    /** @var array $fullyInstantiated */
    public $fullyInstantiated = [];
    /** @var array $originFieldNames */
    public $originFieldNames = [];
    /** @var array $table */
    public $table = [];
}
