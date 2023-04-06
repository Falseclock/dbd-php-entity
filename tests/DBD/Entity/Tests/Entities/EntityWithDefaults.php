<?php
declare(strict_types=1);

namespace DBD\Entity\Tests\Entities;

use DBD\Entity\Column;
use DBD\Entity\Interfaces\SyntheticEntity;
use DBD\Entity\Mapper;
use DBD\Entity\Primitives\StringPrimitives;
use DBD\Entity\View;

class EntityWithDefaults extends View implements SyntheticEntity
{
    public const PREFILL = "shouldn't be changed";

    public ?string $prefiled = self::PREFILL;

    public ?string $unfiled;
}


class EntityWithDefaultsMap extends Mapper
{
    public $prefiled = [
        Column::NAME => "prefilled",
        Column::PRIMITIVE_TYPE => StringPrimitives::String,
        Column::NULLABLE => true
    ];

    public $unfiled = [
        Column::NAME => "unfilled",
        Column::PRIMITIVE_TYPE => StringPrimitives::String,
        Column::NULLABLE => true
    ];
}
