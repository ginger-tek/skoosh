<?php

namespace App\Data;

use App\Services\Db;

class Tickets
{
  public const array STATES = [
    'New',
    'In Progress',
    'On Hold',
    'Completed'
  ];

  public const array REASONS = [
    'Supplies',
    'Costly',
    'Other'
  ];

  public const array URGENCY_LEVELS = [
    1 => 'Today',
    2 => 'This Week',
    3 => 'This Month'
  ];

  public static function create(array $data): ?object
  {
    $id = strtoupper(uniqid());
    Db::run("insert into tickets (id, summary, assignee_id, location_id, category_id, urgency, created_by, created_at)
    values (?, ?, ?, ?, ?, ?, ?, ?)", [
      $id,
      $data['summary'],
      $data['assignee_id'] ?? null,
      $data['location_id'],
      $data['category_id'],
      $data['urgency'],
      $data['created_by'],
      time()
    ]);
    return self::read($id);
  }

  public static function read(string $id): ?object
  {
    return Db::run("select * from v_tickets where id = ?", [$id])->fetch() ?: null;
  }

  public static function list(array $filters = []): array
  {
    if (!empty($filters))
      [$clauses, $vals] = Db::buildClauses($filters);
    $rows = Db::run("select * from v_tickets " . ($clauses ?? '') . " order by urgency", $vals ?? [])->fetchAll();
    usort($rows, fn($a, $b) => array_search($a->state, self::STATES) <=> array_search($b->state, self::STATES));
    return $rows;
  }

  public static function update(string $id, array $data): ?object
  {
    Db::run("update tickets set 
      summary = ?,
      details = ?,
      state = ?,
      reason = ?,
      urgency = ?,
      assignee_id = ?,
      category_id = ?,
      location_id = ?,
      updated_at = ?,
      updated_by = ?
    where id = ?", [
      $data['summary'],
      $data['details'],
      $data['state'],
      $data['reason'],
      $data['urgency'],
      $data['assignee_id'],
      $data['category_id'],
      $data['location_id'],
      time(),
      $data['updated_by'],
      $id
    ]);
    return self::read($id);
  }

  public static function delete(string $id): bool
  {
    return Db::run("delete from tickets where id = ?", [$id])->rowCount() == 1;
  }

  public static function createNote(array $data): ?object
  {
    Db::run("insert into ticket_notes (ticket_id, content, created_by, created_at)
    values (?, ?, ?, ?)", [
      $data['ticket_id'],
      $data['note'],
      $data['created_by'],
      time()
    ]);
    $id = Db::lastInsertId();
    return Db::run("select * from v_ticket_notes where id = ?", [$id])->fetch() ?: null;
  }

  public static function listNotes(string $ticket_id): array
  {
    return Db::run("select * from v_ticket_notes
    where ticket_id = ? order by created_at", [$ticket_id])->fetchAll();
  }
}