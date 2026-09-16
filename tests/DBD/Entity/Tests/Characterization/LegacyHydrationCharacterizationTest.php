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

namespace DBD\Entity\Tests\Characterization;

use DateTime;
use DBD\Entity\Common\EntityException;
use DBD\Entity\Tests\Entities\Characterization\WithPostProcessing;
use DBD\Entity\Tests\Entities\Constraint\UserWithSetter;
use DBD\Entity\Tests\Entities\Embedded\CountryWithRegions;
use DBD\Entity\Tests\Entities\Embedded\Region;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCode;
use DBD\Entity\Tests\Entities\Embedded\StreetWithZipCodeNotEntity;
use DBD\Entity\Tests\Entities\Embedded\ZipCode;
use DBD\Entity\Tests\Entities\EntityWithDefaults;
use DBD\Entity\Tests\Entities\PersonBase;
use DBD\Entity\Tests\Entities\PersonBaseMap;
use DBD\Entity\Tests\Entities\PersonBaseSetters;
use DBD\Entity\Tests\Entities\SelfReference\FourComplex;
use DBD\Entity\Tests\Entities\SelfReference\OneComplex;
use DBD\Entity\Tests\Entities\SelfReference\OneEmbedded;
use DBD\Entity\Tests\Entities\SelfReference\TwoComplex;
use DBD\Entity\Tests\Fixtures\Data;
use Error;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Behavioral baseline of the legacy Entity hydration (Entity::__construct() -> setModelData()).
 *
 * These tests describe what the implementation does today, including quirks (reference aliasing with rawData,
 * distinct instances for the same logical entity, maxLevels cut-off semantics). They are the compatibility
 * contract a future hydration policy must be able to reproduce.
 */
class LegacyHydrationCharacterizationTest extends TestCase
{
    private const COMPLEX_ROW = ['one_id' => 1, 'two_id' => 2, 'three_id' => 3, 'four_id' => 4];

    /**
     * @throws EntityException
     */
    public function testColumnValuesAreCopiedWithoutTypeCoercion(): void
    {
        $person = new PersonBase(Data::getPersonFullEntityData());

        self::assertSame('1', $person->id);
        self::assertSame('Alfa', $person->name);
        self::assertSame('t', $person->isActive);
        self::assertSame('2020-09-21 20:48:28.918366+06', $person->registrationDate);
        self::assertSame(Data::getPersonFullEntityData(), $person->raw());
    }

    /**
     * @throws EntityException
     */
    public function testNullRawDataSkipsHydrationEntirely(): void
    {
        $person = new PersonBase(null);

        self::assertNull($person->raw());
        self::assertNull($person->id);
        self::assertNull($person->name);
    }

    /**
     * @throws EntityException
     */
    public function testUnmappedRawColumnsAreIgnoredButKeptInRaw(): void
    {
        $data = Data::getPersonFullEntityData() + ['unknown_column' => 'x'];

        $person = new PersonBase($data);

        self::assertFalse(property_exists($person, 'unknown_column'));
        self::assertSame('x', $person->raw()['unknown_column']);
    }

    /**
     * Hydrated (non-null, setter-less, non-json) columns are bound BY REFERENCE to the rawData slot.
     *
     * @throws EntityException
     */
    public function testHydratedPropertiesAliasRawData(): void
    {
        $person = new PersonBase(Data::getPersonFullEntityData());

        $person->name = 'changed';
        self::assertSame('changed', $person->raw()['person_name']);

        // the array returned by raw() shares the reference slots, so writes to it leak back into the entity
        $raw = $person->raw();
        $raw['person_email'] = 'leaked@example.com';
        self::assertSame('leaked@example.com', $person->email);

        // the caller's source array is detached once the constructor has copied it
        $source = Data::getPersonFullEntityData();
        $other = new PersonBase($source);
        $source['person_name'] = 'outside';
        self::assertSame('Alfa', $other->name);
    }

    /**
     * Because of the reference binding, clone does not produce an independent copy of hydrated columns.
     *
     * @throws EntityException
     */
    public function testCloneSharesHydratedColumnValuesWithOriginal(): void
    {
        $original = new PersonBaseSetters(Data::getPersonFullEntityData());
        $copy = clone $original;

        $copy->name = 'clone-changed';
        self::assertSame('clone-changed', $original->name);
        self::assertSame('clone-changed', $original->raw()['person_name']);

        // values assigned through a setter are plain values and stay independent
        $copy->isActive = false;
        self::assertTrue($original->isActive);
    }

    /**
     * @throws EntityException
     */
    public function testSettersReceiveRawValueAndTakePrecedence(): void
    {
        $person = new PersonBaseSetters(Data::getPersonFullEntityData());

        self::assertInstanceOf(DateTime::class, $person->registrationDate);
        self::assertTrue($person->isActive);
    }

    /**
     * @throws EntityException
     */
    public function testNullabilityIsCheckedBeforeSetterIsInvoked(): void
    {
        $data = Data::getPersonFullEntityData();
        $data[PersonBaseMap::me()->isActive->name] = null;

        $this->expectException(EntityException::class);
        $this->expectExceptionMessage("shouldn't accept null values");

        new PersonBaseSetters($data);
    }

    /**
     * @throws EntityException
     */
    public function testDefaultValuesSurviveNullAndMissingRawValues(): void
    {
        $withNulls = new EntityWithDefaults(['prefilled' => null, 'unfilled' => null]);
        self::assertSame(EntityWithDefaults::PREFILL, $withNulls->prefiled);
        self::assertNull($withNulls->unfiled);
        self::assertArrayHasKey('unfiled', get_object_vars($withNulls));

        $withValues = new EntityWithDefaults(['prefilled' => 'x', 'unfilled' => 'y']);
        self::assertSame('x', $withValues->prefiled);
        self::assertSame('y', $withValues->unfiled);

        // a typed property without default that is absent from the row is left uninitialized
        $withoutColumns = new EntityWithDefaults([]);
        self::assertSame(EntityWithDefaults::PREFILL, $withoutColumns->prefiled);
        self::assertArrayNotHasKey('unfiled', get_object_vars($withoutColumns));
        $this->expectException(Error::class);
        /** @noinspection PhpExpressionResultUnusedInspection */
        $withoutColumns->unfiled;
    }

    /**
     * @throws EntityException
     */
    public function testEmbeddedJsonIsDecodedInPlaceAndHydratedAsEntities(): void
    {
        $country = new CountryWithRegions(Data::getCountryWithRegionsData());

        self::assertIsArray($country->Regions);
        self::assertCount(count(Data::getRegionsData()), $country->Regions);
        self::assertContainsOnlyInstancesOf(Region::class, $country->Regions);
        self::assertCount(count($country->Regions), array_unique(array_map('spl_object_id', $country->Regions)));

        // the JSON string in rawData is replaced by its decoded array
        self::assertIsArray($country->raw()['country_regions']);
        self::assertSame(Data::getRegionsData(), $country->raw()['country_regions']);
    }

    /**
     * @throws EntityException
     */
    public function testEmbeddedEntityGetsOwnCopyOfSubArray(): void
    {
        $street = new StreetWithZipCode(Data::getStreetWithZipCodeJsonData());

        self::assertInstanceOf(ZipCode::class, $street->ZipCode);
        self::assertSame(Data::getZipCodeData(), $street->ZipCode->raw());

        // the child works on a copy: its writes do not reach the parent's rawData
        $street->ZipCode->value = '999999';
        self::assertSame('050000', $street->raw()['street_zip_code']['zip_code_value']);

        $plain = new StreetWithZipCodeNotEntity(Data::getStreetWithZipCodeNotJsonData());
        self::assertIsArray($plain->ZipCode);
        self::assertSame(Data::getZipCodeData(), $plain->ZipCode);
    }

    /**
     * Complex passes the WHOLE row to the nested entity. Nested entities are always new instances, even when the
     * same logical entity (same class, same row) appears several times in the graph.
     *
     * @throws EntityException
     */
    public function testComplexBuildsDistinctInstancesForTheSameLogicalEntity(): void
    {
        $root = new OneComplex(self::COMPLEX_ROW);

        self::assertInstanceOf(TwoComplex::class, $root->TwoComplex);
        self::assertInstanceOf(FourComplex::class, $root->FourComplex);
        self::assertInstanceOf(OneComplex::class, $root->TwoComplex->OneComplex);
        self::assertInstanceOf(OneComplex::class, $root->FourComplex->OneComplex);

        self::assertNotSame($root, $root->TwoComplex->OneComplex);
        self::assertNotSame($root, $root->FourComplex->OneComplex);
        self::assertNotSame($root->TwoComplex->OneComplex, $root->FourComplex->OneComplex);

        self::assertSame(1, $root->id);
        self::assertSame(1, $root->TwoComplex->OneComplex->id);
        self::assertSame(1, $root->FourComplex->OneComplex->id);
        self::assertSame(self::COMPLEX_ROW, $root->TwoComplex->raw());
    }

    /**
     * Columns hydrated by an ancestor are shared BY REFERENCE with every descendant of the same class, because the
     * ancestor's rawData (already bound to its properties) is what Complex hands down.
     *
     * @throws EntityException
     */
    public function testComplexDescendantsOfSameClassAliasAncestorColumns(): void
    {
        $root = new OneComplex(self::COMPLEX_ROW);

        $root->TwoComplex->OneComplex->id = 99;
        self::assertSame(99, $root->id);
        self::assertSame(99, $root->FourComplex->OneComplex->id);
        self::assertSame(99, $root->raw()['one_id']);

        $other = new OneComplex(self::COMPLEX_ROW);
        $other->id = 555;
        self::assertSame(555, $other->TwoComplex->OneComplex->id);
        self::assertSame(555, $other->TwoComplex->raw()['one_id']);

        // columns first hydrated below the root are NOT shared between siblings
        $third = new OneComplex(self::COMPLEX_ROW);
        $third->TwoComplex->id = 777;
        self::assertSame(2, $third->FourComplex->TwoComplex->id);
        self::assertSame(2, $third->raw()['two_id']);
    }

    /**
     * @return array<string, array{int, array<int, array<int, string>|string>}>
     */
    public static function complexMaxLevels(): array
    {
        $root = ['id', 'TwoComplex', 'FourComplex'];
        $two = ['id', 'ThreeComplex', 'OneComplex'];

        return [
            'maxLevels=0' => [0, [['id'], 'UNSET', 'UNSET', 'UNSET']],
            'maxLevels=1' => [1, [$root, ['id'], 'UNSET', 'UNSET']],
            'maxLevels=2 (default)' => [2, [$root, $two, ['id'], 'UNSET']],
            'maxLevels=3' => [3, [$root, $two, ['FourComplex', 'OneComplex', 'TwoComplex', 'id'], ['id']]],
        ];
    }

    /**
     * maxLevels = N means N + 1 hydrated levels: the entity at depth N still gets its columns, its nested
     * Complex/Embedded properties are unset().
     *
     * @param array<int, array<int, string>|string> $expected
     * @throws EntityException
     */
    #[DataProvider('complexMaxLevels')]
    public function testMaxLevelsForComplex(int $maxLevels, array $expected): void
    {
        $root = new OneComplex(self::COMPLEX_ROW, $maxLevels);

        self::assertSame($expected[0], self::visibleProperties($root, []));
        self::assertSame($expected[1], self::visibleProperties($root, ['TwoComplex']));
        self::assertSame($expected[2], self::visibleProperties($root, ['TwoComplex', 'ThreeComplex']));
        self::assertSame($expected[3], self::visibleProperties($root, ['TwoComplex', 'ThreeComplex', 'FourComplex']));
    }

    /**
     * @return array<string, array{int, array<int, array<int, string>|string>}>
     */
    public static function embeddedMaxLevels(): array
    {
        return [
            'maxLevels=0' => [0, [['id'], 'UNSET', 'UNSET', 'UNSET']],
            'maxLevels=1' => [1, [['id', 'TwoEmbedded'], ['id'], 'UNSET', 'UNSET']],
            'maxLevels=2 (default)' => [2, [['id', 'TwoEmbedded'], ['id', 'ThreeEmbedded'], ['id'], 'UNSET']],
            'maxLevels=3' => [3, [['id', 'TwoEmbedded'], ['id', 'ThreeEmbedded'], ['FourEmbedded', 'id'], ['id']]],
        ];
    }

    /**
     * @param array<int, array<int, string>|string> $expected
     * @throws EntityException
     */
    #[DataProvider('embeddedMaxLevels')]
    public function testMaxLevelsForEmbedded(int $maxLevels, array $expected): void
    {
        $root = new OneEmbedded(self::embeddedRow(), $maxLevels);

        self::assertSame($expected[0], self::visibleProperties($root, []));
        self::assertSame($expected[1], self::visibleProperties($root, ['TwoEmbedded']));
        self::assertSame($expected[2], self::visibleProperties($root, ['TwoEmbedded', 'ThreeEmbedded']));
        self::assertSame($expected[3], self::visibleProperties($root, ['TwoEmbedded', 'ThreeEmbedded', 'FourEmbedded']));
    }

    /**
     * @throws EntityException
     */
    public function testPropertiesBeyondMaxLevelsAreUnsetAndThrowOnRead(): void
    {
        $root = new OneComplex(self::COMPLEX_ROW, 0);

        self::assertTrue(property_exists($root, 'TwoComplex'));
        self::assertFalse(isset($root->TwoComplex));
        self::assertArrayNotHasKey('TwoComplex', get_object_vars($root));

        // reading an unset property goes through Entity::__get(), which has no getter to fall back to
        $this->expectException(EntityException::class);
        $this->expectExceptionMessage("Can't find property or getter method for '\$TwoComplex'");
        /** @noinspection PhpExpressionResultUnusedInspection */
        $root->TwoComplex;
    }

    /**
     * @throws EntityException
     */
    public function testConstraintsAreNeverHydrated(): void
    {
        $user = new UserWithSetter(Data::getUserFullData());

        self::assertSame(1, $user->id);
        self::assertSame(3, $user->companyId);
        self::assertSame(2, $user->personId);
        // even though setCompany()/setPerson() exist, Entity never touches constraint properties
        self::assertNull($user->Company);
        self::assertNull($user->Person);
    }

    /**
     * @throws EntityException
     */
    public function testPostProcessingRunsOncePerHydratedEntity(): void
    {
        WithPostProcessing::$postProcessingCalls = 0;
        new WithPostProcessing(null);
        self::assertSame(0, WithPostProcessing::$postProcessingCalls);

        WithPostProcessing::$postProcessingCalls = 0;
        new WithPostProcessing(['pp_id' => 1], 0);
        self::assertSame(1, WithPostProcessing::$postProcessingCalls);

        WithPostProcessing::$postProcessingCalls = 0;
        $root = new WithPostProcessing(['pp_id' => 1], 2);
        self::assertSame(3, WithPostProcessing::$postProcessingCalls);
        self::assertInstanceOf(WithPostProcessing::class, $root->Child->Child);
        self::assertFalse(isset($root->Child->Child->Child));
    }

    /**
     * @return array<string, mixed>
     */
    private static function embeddedRow(): array
    {
        return [
            'one_id' => 1,
            'two' => [
                'two_id' => 2,
                'three' => [
                    'three_id' => 3,
                    'four' => [
                        'four_id' => 4,
                        'one' => ['one_id' => 1],
                    ],
                ],
            ],
        ];
    }

    /**
     * Walks $path and returns the visible property names of the reached entity, or 'UNSET' if a step is not set.
     *
     * @param string[] $path
     * @return string[]|string
     */
    private static function visibleProperties(object $entity, array $path): array|string
    {
        foreach ($path as $step) {
            if (!isset($entity->$step)) {
                return 'UNSET';
            }
            $entity = $entity->$step;
        }

        return array_keys(get_object_vars($entity));
    }
}
