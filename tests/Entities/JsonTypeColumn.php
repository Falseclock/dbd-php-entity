<?php
/**
 * <description should be written here>
 *
 * @package      DBD\Entity\Tests\Entities
 * @copyright    Copyright © Real Time Engineering, LLP - All Rights Reserved
 * @license      Proprietary and confidential
 * Unauthorized copying or using of this file, via any medium is strictly prohibited.
 * Content can not be copied and/or distributed without the express permission of Real Time Engineering, LLP
 *
 * @author       Written by Nurlan Mukhanov <nmukhanov@mp.kz>, октябрь 2020
 */

namespace DBD\Entity\Tests\Entities;


use DBD\Entity\Column;
use DBD\Entity\Entity;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitive;

class JsonTypeColumn extends Entity implements SyntheticEntity
{
    /**
     * @var array $json
     * @see JsonTypeColumnMap $json
     */
    public $json;
}

class JsonTypeColumnMap extends Mapper
{
    /**
     * @var Column $json
     * @see JsonTypeColumn::$json
     */
    public $json = [
        Column::NAME => "json_value",
        Column::PRIMITIVE_TYPE => Primitive::String,
        Column::ORIGIN_TYPE => "json"
    ];
}
