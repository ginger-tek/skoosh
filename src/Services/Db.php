<?php

namespace App\Services;

use App\Data\Users;

class Db
{
	private static ?\PDO $inst = null;

	private static function open(): \PDO
	{
		if (!self::$inst) {
			self::$inst = new \PDO('sqlite:' . ROOT . '/data/skoosh.db', null, null, [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
			]);
			self::$inst->exec('PRAGMA foreign_keys = ON;');
		}
		return self::$inst;
	}

	public static function init()
	{
		self::open()->exec(file_get_contents(ROOT . '/schema.sql'));
	}

	public static function run(string $sql, ?array $values = []): \PDOStatement
	{
		$stmt = self::open()->prepare($sql);
		$stmt->execute($values);
		return $stmt;
	}

	public static function lastInsertId(): int
	{
		return (int) self::open()->lastInsertId() ?: 0;
	}

	public static function buildClauses(array $filters = []): array
	{
		$clauses = '';
		$values = [];
		if (!empty($filters)) {
			$where = [];
			foreach ($filters as $key => $value) {
				if (\is_array($value)) {
					[$op, $vals] = $value;
					if ($op == 'between' && \is_array($vals)) {
						$where[] = "$key BETWEEN ? AND ?";
						$values[] = $vals[0];
						$values[] = $vals[1];
					} elseif ($op == 'in' && \is_array($vals)) {
						$placeholders = join(',', array_fill(0, \count($vals), '?'));
						$where[] = "$key IN ($placeholders)";
						$values = [...$values, ...$vals];
					} elseif (\in_array($op, ['=', '!=', '<', '<=', '>', '>=']) && !\is_array($vals)) {
						$where[] = "$key $op ?";
						$values[] = $vals;
					}
				} else {
					$where[] = "$key = ?";
					$values[] = $value;
				}
			}
			$clauses = 'WHERE ' . join(' AND ', $where);
		}
		return [$clauses, $values];
	}
}
