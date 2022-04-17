# DBD-PHP-Entity

[![scrutinizer build](https://scrutinizer-ci.com/g/Falseclock/dbd-php-entity/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Falseclock/dbd-php-entity/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Falseclock/dbd-php-entity/badge.svg?branch=master)](https://coveralls.io/github/Falseclock/dbd-php-entity?branch=master)
[![PHP Version Require](http://poser.pugx.org/falseclock/dbd-php-entity/require/php)](https://packagist.org/packages/falseclock/dbd-php-entity)

[![Latest Stable Version](https://poser.pugx.org/falseclock/dbd-php-entity/v)](//packagist.org/packages/falseclock/dbd-php-entity)
[![Total Downloads](https://poser.pugx.org/falseclock/dbd-php-entity/downloads)](//packagist.org/packages/falseclock/dbd-php-entity)
[![Latest Unstable Version](https://poser.pugx.org/falseclock/dbd-php-entity/v/unstable)](//packagist.org/packages/falseclock/dbd-php-entity)
[![License](https://poser.pugx.org/falseclock/dbd-php-entity/license)](//packagist.org/packages/falseclock/dbd-php-entity)

**NOTICE**: readme находится в процессе написания.

Данная библиотека позволяет легко преобразовывать ассоциативные массивы в объекты по заранее предопределенным структурам
(мапингу), иными словами реализует DTO. Изначально проект реализовывался как ORM библиотека, но не ограничивается этим.
С помощью библиотеки можно описывать любые структурированные данные, а не только таблицы и их поля.

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
7. Оба класс `Entity` и `Mapper` должны быть в одном `namespace`.

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

__get — магический метод для доступа к `protected` переменным класса.

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

* * *

### **getColumns**

getColumns — получение массива стандартных полей

#### Описание

```php
public getColumns(): array
```

Данная функция возвращает массив `Columns`, которые определены в Mapper классе. Следует помнить, что комплексные поля
объявляются как `public`.

* * *

### **getComplex**

getComplex — получение массива комплексных полей

#### Описание

```php
public getComplex(): array
```

Данная функция возвращает массив `Complex`, которые определены в Mapper классе. Следует помнить, что комплексные поля
объявляются как `protected`.

* * *

### **getConstraints**

getConstraints — получение массива ограничений

#### Описание

```php
public getConstraints(): array
```

Данная функция возвращает массив `Constraint`, которые определены в Mapper классе. Следует помнить, что ограничения
объявляются как `protected`.

* * *

### **getEmbedded**

getEmbedded — получение массива встроенных полей

#### Описание

```php
public getEmbedded(): array
```

Данная функция возвращает массив `Embedded`, которые определены в Mapper классе. Следует помнить, что встроенные поля
объявляются как `protected`.

* * *

### **getEntityClass**

getEntityClass — получение класса, который использует данный маппинг

#### Описание

```php
public getEntityClass(): string
```

Следует осторожно использовать данную функцию, если вы не объявили `Entity` и `Mapper` в одном `namespace`

* * *

### **getOriginFieldNames**

getOriginFieldNames — получение массива объявленных `public` полей

#### Описание

```php
public getOriginFieldNames(): array
```

Данная функция возвращает ассоциативный массив, где ключ — наименование переменной класса `Mapper`, а значение —
наименование поля.