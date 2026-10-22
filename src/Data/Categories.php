<?php

namespace App\Data;

use App\Services\Db;

class Categories
{
  public static function create(array $data): ?object
  {
    Db::run("insert into categories (name,created_at,created_by)
    values (?,?,?)", [
      $data['name'],
      time(),
      $data['created_by'] ?? 0
    ])->fetch();
    return self::read(Db::lastInsertId());
  }

  public static function read(int $id)
  {
    return Db::run("select * from categories where id = ?", [$id])->fetch();
  }

  public static function list(array $filters = []): array
  {
    if (!empty($filters))
      [$clauses, $vals] = Db::buildClauses($filters);
    return Db::run("select id, name from categories " . ($clauses ?? '') . " order by name", $vals ?? [])->fetchAll();
  }

  public static function update(int $id, array $data): void
  {
    Db::run("update categories set
      name = ?,
      updated_at = ?,
      updated_by = ?
    where id = ?", [
      $data['name'],
      time(),
      $data['updated_by'],
      $id
    ]);
  }

  public static function delete(int $id): bool
  {
    return Db::run("delete from categories where id = ?", [$id])->rowCount() == 1;
  }

}