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

namespace DBD\Entity;

use DBD\Common\Singleton;

/**
 * Class MapperCache used to avoid interfering with local variables in child classes
 *
 * @package Falseclock\DBD\Entity
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
    /** @var array $otherColumns */
    public $otherColumns = [];
    /** @var array $table */
    public $table = [];
}
