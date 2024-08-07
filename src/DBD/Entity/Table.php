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

namespace DBD\Entity;

/**
 * Class Table useless still
 *
 * @package DBD\Entity
 */
class Table
{
    /** @var string $name */
    public string $name;
    /** @var string $scheme */
    public string $scheme;
    /** @var Column[] $columns */
    public array $columns = [];
    /** @var Key[] $keys */
    public array $keys = [];
    /** @var string $annotation */
    public string $annotation;
    /** @var Constraint[] $constraints */
    public array $constraints = [];

    /**
     * @param Mapper $mapper
     *
     * @return Table
     */
    public static function getFromMapper(Mapper $mapper): Table
    {
        $table = new Table();

        /** @var Entity $entityClass Getting class name from Mapper instance, which can be used as class instance */
        $entityClass = $mapper->getEntityClass();

        $table->name = $entityClass::TABLE;
        $table->scheme = $entityClass::SCHEME;
        $table->annotation = $mapper->getAnnotation();

        foreach ($mapper->getColumns() as $column) {
            $table->columns[] = $column;
            if ($column->key) {
                $table->keys[] = $column;
            }
        }

        foreach ($mapper->getConstraints() as $constraint) {
            $table->constraints[] = $constraint;
        }

        return $table;
    }
}
