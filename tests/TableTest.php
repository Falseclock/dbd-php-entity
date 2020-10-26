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

namespace DBD\Entity\Tests;

use DBD\Entity\Entity;
use DBD\Entity\Table;
use DBD\Entity\Tests\Entities\Constraint\TenderLot;
use DBD\Entity\Tests\Entities\TableStructure\TableEntity;
use DBD\Entity\Tests\Fixtures\Data;
use PHPUnit\Framework\TestCase;

/**
 * Class TableTest
 * @package DBD\Entity\Tests
 */
class TableTest extends TestCase
{
    public function testEntity()
    {
        $entity = new TableEntity(Data::getTableStructureData());

        self::assertInstanceOf(Entity::class, $entity);

        $table = new Table();
        $table->name = $entity->name;
        $table->scheme = $entity->schema;
        $table->annotation = $entity->comment;
        $table->columns = $entity->columns;
        $table->constraints = $entity->foreignKeys;

        foreach ($entity->columns as $column) {
            if ($column->key)
                $table->keys[] = $column;
        }

        $tableFromMap = Table::getFromMapper(TenderLot::map());

        self::assertNotNull($tableFromMap);
    }
}


/*
-- PostgreSQL table definition query
SELECT
    table_schema,
    table_name,
    table_type,
    obj_description(CONCAT(table_schema::TEXT, '.', table_name::TEXT)::REGCLASS) AS table_comment,
    jsonb_agg(DISTINCT
              jsonb_build_object(
                  'column_is_primary', column_is_primary,
                  'column_position', column_position,
                  'column_name', column_name,
                  'column_is_nullable', column_is_nullable,
                  'column_type', column_type,
                  'column_udt_type', column_udt_type,
                  'column_character_length', column_character_length,
                  'column_numeric_precision', column_numeric_precision,
                  'column_numeric_scale', column_numeric_scale,
                  'column_datetime_precision', column_datetime_precision,
                  'column_interval_precision', column_interval_precision,
                  'column_default_value', column_default_value,
                  'column_comment', column_comment
              ))                                                             AS table_columns,
    jsonb_agg(DISTINCT
    jsonb_build_object(
        'constraint_local_column_name', constraint_local_column_name,
        'constraint_foreign_table_schema', constraint_foreign_table_schema,
        'constraint_foreign_table_name', constraint_foreign_table_name,
        'constraint_foreign_column_name', constraint_foreign_column_name,
        'constraint_name', constraint_name
    )) FILTER (WHERE constraint_name IS NOT NULL)                            AS table_constraints
FROM
    information_schema.tables
    LEFT JOIN LATERAL (
    SELECT
            CASE WHEN ordinal_position = ANY (i.indkey) THEN TRUE ELSE FALSE END                                                   AS column_is_primary,
            ordinal_position                                                                                                       AS column_position,
            cols.column_name                                                                                                       AS column_name,
            CASE WHEN is_nullable = 'NO' THEN FALSE WHEN is_nullable = 'YES' THEN TRUE END                                         AS column_is_nullable,
            data_type                                                                                                              AS column_type,
            udt_name                                                                                                               AS column_udt_type,
            character_maximum_length                                                                                               AS column_character_length,
            numeric_precision                                                                                                      AS column_numeric_precision,
            numeric_scale                                                                                                          AS column_numeric_scale,
            datetime_precision                                                                                                     AS column_datetime_precision,
            interval_precision                                                                                                     AS column_interval_precision,
            column_default                                                                                                         AS column_default_value,
            pg_catalog.col_description(CONCAT(cols.table_schema, '.', cols.table_name)::REGCLASS::OID, cols.ordinal_position::INT) AS column_comment
        FROM
            information_schema.columns cols
            LEFT JOIN pg_index i ON i.indrelid = CONCAT(cols.table_schema, '.', cols.table_name)::REGCLASS::OID AND i.indisprimary
        WHERE
            cols.table_name = tables.table_name AND
            cols.table_schema = tables.table_schema
        ORDER BY
            ordinal_position
        ) columns ON TRUE
    LEFT JOIN LATERAL (
    SELECT
            kcu.column_name  AS constraint_local_column_name,
            ccu.table_schema AS constraint_foreign_table_schema,
            ccu.table_name   AS constraint_foreign_table_name,
            ccu.column_name  AS constraint_foreign_column_name,
            ccu.constraint_name
        FROM
            information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                 ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                 ON ccu.constraint_name = tc.constraint_name
        WHERE
            tc.constraint_type = 'FOREIGN KEY' AND
            tc.table_name = tables.table_name AND
            tc.table_schema = tables.table_schema
        ) constraints ON TRUE
WHERE
    table_schema = '?' AND
    table_name = '?'
GROUP BY
    table_schema,
    table_name,
    table_type
*/
