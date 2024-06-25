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

namespace DBD\Entity\Interfaces;

use DBD\Entity\Column;
use DBD\Entity\Complex;
use DBD\Entity\Constraint;
use DBD\Entity\Embedded;
use DBD\Entity\MapperVariables;
use DBD\Entity\Table;


/**
 * Interface EntityMapper
 *
 * @package DBD\Entity\Interfaces
 */
interface EntityMapper
{
    /**
     * Get simple Mapper class name without namespace
     */
    public function name(): string;

    /**
     * Returns Entity class name which uses this Mapper
     *
     * @return string
     */
    public function getEntityClass(): string;

    public function getScheme(): string;

    public function getTableName(): string;

    /**
     * Returns table comment
     *
     * @return string
     */
    public function getAnnotation(): string;

    public function getAllVariables(): MapperVariables;

    /**
     * @return Complex[]
     */
    public function getComplex(): array;

    /**
     * @return Column[]
     */
    public function getColumns(): array;

    /**
     * @param string $originName
     * @return Column
     */
    public function findColumnByOriginName(string $originName): Column;

    /**
     * @param Column $column
     * @return int|string
     */
    public function getVarNameByColumn(Column $column): int|string;

    /**
     * @return Column[]
     */
    public function getPrimaryKey(): array;

    /**
     * @return array<string, string>
     */
    public function getOriginFieldNames(): array;

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array;

    /**
     * @return Embedded[]
     */
    public function getEmbedded(): array;

    /**
     * @return Table
     */
    public function getTable(): Table;
}
