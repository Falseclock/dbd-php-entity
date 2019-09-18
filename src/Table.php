<?php

namespace Falseclock\DBD\Entity;

use Falseclock\DBD\Common\DBDException;
use Falseclock\DBD\Entity\Common\EntityException;
use Falseclock\DBD\Entity\Join\ManyToMany;
use Falseclock\DBD\Entity\Join\ManyToOne;
use Falseclock\DBD\Entity\Join\OneToMany;
use Falseclock\DBD\Entity\Join\OneToOne;
use ReflectionException;

class Table
{
	/** @var string $name */
	public $name;
	/** @var string $scheme */
	public $scheme;
	/** @var Column[] $columns */
	public $columns = [];
	/** @var Key[] $keys */
	public $keys = [];
	/** @var string $annotation */
	public $annotation;
	/** @var Constraint[] $constraints */
	public $constraints = [];
	/** @var Column[] $otherColumns */
	public $otherColumns = [];

	/**
	 * @param Mapper $mapper
	 *
	 * @return Table
	 * @throws DBDException
	 * @throws EntityException
	 * @throws ReflectionException
	 */
	public static function getFromMapper(Mapper $mapper) {
		$table = new Table();

		/** @var Entity $entityClass */
		$entityClass = $mapper->getEntityClass();

		$table->name = $entityClass::getTableName();
		$table->scheme = $entityClass::getSchemeName();

		self::convertVariables($table, $mapper);

		$table->annotation = $mapper->getAnnotation();
		$table->keys = self::getKeys($table);

		return $table;
	}

	/**
	 * @param $columnValue
	 *
	 * @return Column
	 */
	private static function convertToColumn($columnValue): Column {
		/** @var Column $columnValue Yes, we are 100% column annotation */
		$column = new Column();

		foreach($columnValue as $key => $value) {
			if($key == Column::TYPE)
				$column->$key = new Primitive($value);
			else
				$column->$key = $value;
		}

		return $column;
	}

	/**
	 * @param Table      $table
	 * @param Constraint $constraintValue
	 *
	 * @return Constraint
	 * @throws DBDException
	 * @throws EntityException
	 * @throws ReflectionException
	 */
	private static function convertToConstraint(Table &$table, $constraintValue): Constraint {
		$constraint = new Constraint();

		$constraintValue = (object) $constraintValue;

		/** @var Entity $foreignClass */
		$foreignClass = $constraintValue->class;

		$constraint->foreignTable = self::getFromMapper($foreignClass::mappingInstance());
		$constraint->foreignColumn = self::findColumnByOriginName($constraint->foreignTable, $constraintValue->foreignColumn);

		$constraint->localTable = $table;
		$constraint->localColumn = self::findColumnByOriginName($table, $constraintValue->localColumn);

		switch($constraintValue->join) {
			case Join::MANY_TO_ONE:
				$constraint->join = new ManyToOne();
				break;
			case Join::MANY_TO_MANY:
				$constraint->join = new ManyToMany();
				break;
			case Join::ONE_TO_ONE:
				$constraint->join = new OneToOne();
				break;
			case Join::ONE_TO_MANY:
				$constraint->join = new OneToMany();
				break;
		}
		$constraint->class = $constraintValue->class;

		return $constraint;
	}

	/**
	 * Converts vars to Column & Constraint
	 *
	 * @param Table  $table
	 * @param Mapper $mapper
	 *
	 * @throws EntityException
	 * @throws DBDException
	 * @throws ReflectionException
	 */
	private static function convertVariables(Table &$table, Mapper $mapper): void {

		$variables = $mapper->getAllVariables();

		// Read all variables and convert to Column and Constraint
		foreach($variables->columns as $columnName) {

			$columnValue = $mapper->$columnName;

			// This is fix for old annotation when we used only column name as variable; TODO: remove after migration
			if(is_string($columnValue)) {
				$table->columns[$columnName] = new Column($columnValue);
				continue;
			}
			// It should be array always? otherwise throw exception
			if(is_array($columnValue)) {
				$table->columns[$columnName] = self::convertToColumn($columnValue);
			}
			else {
				throw new EntityException("Unknown type of Mapper variable {$columnName} in {$mapper}");
			}
		}

		foreach($variables->otherColumns as $otherColumnName) {

			$otherColumnValue = $mapper->$otherColumnName;

			// This is fix for old annotation when we used only column name as variable;
			// TODO: remove after migration
			if(is_string($otherColumnValue)) {
				$table->otherColumns[$otherColumnName] = new Column($otherColumnValue);
				continue;
			}
			// It should be array always? otherwise throw exception
			if(is_array($otherColumnValue)) {
				$table->otherColumns[$otherColumnName] = self::convertToColumn($otherColumnValue);
			}
			else {
				throw new EntityException("Unknown type of Mapper variable {$otherColumnName} in {$mapper}");
			}
		}

		// now parse all constraints
		// All constraints should be processed after columns
		foreach($variables->constraints as $constraintName) {

			/** @var Constraint $constraintValue */
			$constraintValue = $mapper->$constraintName;

			$table->constraints[] = self::convertToConstraint($table, $constraintValue);
		}

		return;
	}

	/**
	 * @param Table  $table
	 * @param string $columnOriginName
	 *
	 * @return Column
	 * @throws EntityException
	 */
	private static function findColumnByOriginName(Table $table, string $columnOriginName): Column {
		foreach(array_merge($table->columns, $table->otherColumns) as $column) {
			if($column->name == $columnOriginName) {
				return $column;
			}
		}
		throw new EntityException("Can't find column {$columnOriginName}");
	}

	private static function getKeys(Table $table) {
		$keys = [];
		foreach(array_merge($table->columns, $table->otherColumns) as $column) {
			if($column->key === true) {
				$keys[] = new Key($column);
			}
		}

		return $keys;
	}
}