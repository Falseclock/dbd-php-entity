# DBD-PHP-Entity

[![Build Status](https://travis-ci.org/Falseclock/dbd-php-entity.svg?branch=master)](https://travis-ci.org/Falseclock/dbd-php-entity)
[![Coverage Status](https://coveralls.io/repos/github/Falseclock/dbd-php-entity/badge.svg?branch=master)](https://coveralls.io/github/Falseclock/dbd-php-entity?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Falseclock/dbd-php-entity/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Falseclock/dbd-php-entity/?branch=master)
[![PHP 7 ready](https://php7ready.timesplinter.ch/Falseclock/dbd-php-entity/master/badge.svg)](https://travis-ci.org/Falseclock/dbd-php-entity)

[![Latest Stable Version](https://poser.pugx.org/falseclock/dbd-php-entity/v)](//packagist.org/packages/falseclock/dbd-php-entity)
[![Total Downloads](https://poser.pugx.org/falseclock/dbd-php-entity/downloads)](//packagist.org/packages/falseclock/dbd-php-entity)
[![Latest Unstable Version](https://poser.pugx.org/falseclock/dbd-php-entity/v/unstable)](//packagist.org/packages/falseclock/dbd-php-entity)
[![License](https://poser.pugx.org/falseclock/dbd-php-entity/license)](//packagist.org/packages/falseclock/dbd-php-entity)

Данная библиотека позволяет легко преобразовывать ассоциативные массивы в объекты по заранее предопределенным моделям
(мапингу), иными словами реализует DTO. Изначально проект реализовывался как ORM библиотека, но по факту можно описывать
любые структурированные данные.

## Установка

```bash
composer require falseclock/dbd-php-entity
```

## Оглавление

#### Основные классы

* [Mapper](#Mapper)
* [Entity](#Entity)
* [View](#View)

#### Описание полей

* [Column](#Column)
* [Complex](#Complex)
* [Constraint](#Constraint)
* [Embedded](#Embedded)

#### Вспомогательные классы

* [Primitive](#Primitive)
* [Type](#Type)

#### Вспомогательные классы

* [FullEntity](#FullEntity)
* [FullMapper](#FullMapper)
* [OnlyDeclaredPropertiesEntity](#OnlyDeclaredPropertiesEntity)
* [StrictlyFilledEntity](#StrictlyFilledEntity)
* [SyntheticEntity](#SyntheticEntity)
* [MapperVariables](#MapperVariables)

* * *

# **Mapper**

Любое описание модели начинается с наследования этого абстрактного класса. Дочерний класс должен отвечать нескольким
простым правилам:

1. Все переменные с типом `Column` объявляются как `public`.
2. Любые другие переменные кроме `Column`, объявляются как `protected`.
3. Класс не должен содержать `private` переменных.
4. Класс не должен иметь методы.
5. Константа `ANNOTATION` должна быть переопределена.
6. Название класса должно иметь тоже самое название что и основной класс `Entity` с постфиксом `Map`.

#### Пример

```php
class City extends Entity {

}

class CityMap extends Mapper
{
    const ANNOTATION = "Data description";
}
```

Постфикс `Map`, при желании, можно переопределить через константу `Maper::POSTFIX`

Все потомки `Mapper` класса является синглтонами и вызываются через статичный метод [`me`](#me).

## Публичные методы

* [__get](#__get)
* [findColumnByOriginName](#findColumnByOriginName)
* [getAllVariables](#getAllVariables)
* [getAnnotation](#getAnnotation)
* [getBaseColumns](#getBaseColumns)
* [getColumns](#getColumns)
* [getComplex](#getComplex)
* [getConstraints](#getConstraints)
* [getEmbedded](#getEmbedded)
* [getEntityClass](#getEntityClass)
* [getOriginFieldNames](#getOriginFieldNames)
* [getPrimaryKey](#getPrimaryKey)
* [getTable](#getTable)
* [getVarNameByColumn](#getVarNameByColumn)
* [me](#me)
* [meWithoutEnforcer](#meWithoutEnforcer)
* [name](#name)

* * *

### **__get**

__get — магический метод для доступ к `protected` переменным класса.

#### Описание

```php
public __get(string $property): mixed
```

В некоторых случаях, при необходимости обращения к непубличным переменным в IDE, можно через phpdoc в заголовке класса
прописать переменную через `@property`, которая доступна через магический метод. Если обратиться к несуществующей
переменной, будет выброшено исключение.

#### Пример

```php
/**
 * Class MapperGet
 * @property Embedded $Regions
 * @property Complex $Address
 */
class MapperGet extends Mapper
{
    const ANNOTATION = "Data description";

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
```

* * *

### **findColumnByOriginName**

findColumnByOriginName — получение экземпляра класса Column через ключ ассоциативного массива.

#### Описание

```php
public findColumnByOriginName(string $originName): Column
```

Может быть использован, если необходимо получить информацию о том как описывается то или иное `public` поле в `Mapper`
классе.

* * *

### **getAllVariables**

getAllVariables — получение всех определенных переменных класса

#### Описание

```php
public getAllVariables(): MapperVariables
```

Полезная функция, если имеется необходимость на основании описания полей сформировать скрипт создания таблицы в
реляционной базе.

* * *

### **getAnnotation**

getAnnotation — получение константы `ANNOTATION`

#### Описание

```php
public getAnnotation(): string
```

