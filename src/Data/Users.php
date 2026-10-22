<?php

namespace App\Data;

use App\Services\Db;

class Users
{
  public const array ROLES = [
    'Admin',
    'User'
  ];

  public const array STATUSES = [
    'New',
    'Active',
    'Inactive'
  ];

  public static function create(array $data): ?object
  {
    Db::run("insert into users (username, display_name, passhash, role, status, created_by, created_at) values (?, ?, ?, ?, ?, ?, ?)", [
      $data['username'],
      $data['display_name'],
      password_hash($data['password'] ?? getenv('DEFAULT_PASSWORD'), PASSWORD_BCRYPT),
      $data['role'] ?? 'User',
      $data['status'] ?? 'New',
      $data['created_by'] ?? 0,
      time()
    ])->fetch();
    $id = Db::lastInsertId();
    return self::readDTO($id);
  }

  public static function list(array $filters = []): array
  {
    if (!empty($filters))
      [$clauses, $vals] = Db::buildClauses($filters);
    return Db::run("select id, display_name, username, role from v_users " . ($clauses ?? ''), $vals ?? [])->fetchAll();
  }

  public static function read(int $id): ?object
  {
    return Db::run("select * from users where id = ?", [$id])->fetch() ?: null;
  }

  public static function readDTO(int $id): ?object
  {
    return Db::run("select * from v_users where id = ?", [$id])->fetch() ?: null;
  }

  public static function readByUsername(string $username, bool $includeInactive = false): ?object
  {
    return Db::run(
      "select * from users where username = ?" . ($includeInactive ? "" : " and status != 'Inactive'"),
      [$username]
    )->fetch() ?: null;
  }

  public static function readFirstOrDefaultAdmin(): ?object
  {
    return Db::run(
      "select * from v_users
      where role = 'Admin' order by id asc limit 1"
    )->fetch() ?: null;
  }

  public static function update(int $id, array $data): ?object
  {
    Db::run("update users set 
      username = ?,
      display_name = ?,
      role = ?,
      status = ?,
      updated_by = ?,
      updated_at = ?
    where id = ?", [
      $data['username'],
      $data['display_name'],
      $data['role'] ?? 'User',
      $data['status'] ?? 'New',
      $data['updated_by'] ?? 0,
      time(),
      $id
    ]);
    return self::readDTO($id);
  }

  public static function updatePassword(int $id, string $newPassword, int $updatedBy): bool
  {
    return Db::run("update users set
      status = 'Active',
      passhash = ?,
      updated_by = ?,
      updated_at = ?
    where id = ?", [
      password_hash($newPassword, PASSWORD_BCRYPT),
      $updatedBy,
      time(),
      $id
    ])->rowCount() == 1;
  }

  public static function delete(int $id): bool
  {
    return Db::run("delete from users where id = ?", [$id])->rowCount() == 1;
  }
}