<?php

use DBD\Entity\Complex;
use DBD\Entity\Embedded;
use DBD\Entity\Mapper;
use DBD\Entity\Tests\Entities\Address;
use DBD\Entity\Tests\Entities\Embedded\Region;

/**
 * Class MapperGet
 * @property Embedded $Regions
 * @property Complex $Address
 */
class MapperGet extends Mapper
{
    const ANNOTATION = "Table description";

    /** @var Embedded */
    protected $Regions = [
        Embedded::NAME => "country_regions",
        Embedded::ENTITY_CLASS => Region::class,
    ];

    /**  @var Complex */
    protected $Address = [
        Complex::TYPE => Address::class,
    ];
}
