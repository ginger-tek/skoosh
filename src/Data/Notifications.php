<?php

namespace App\Data;

use App\Services\Db;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use GuzzleHttp\Client;

class Notifications
{
  public static function getWebPushConfig(): array
  {
    [$subjectOrigin] = array_values(
      array_filter(
        explode(',', getenv('ALLOWED_ORIGINS') ?: ''),
        fn($o) => getenv('ENV') == 'DEV'
          ? str_contains($o, 'localhost')
          : !str_contains($o, 'localhost')
      )
    );
    if (!$subjectOrigin)
      throw new \Exception("No valid subject origin found in ALLOWED_ORIGINS\n");
    $subjectOrigin = (str_contains($subjectOrigin, 'localhost') ? 'http://' : 'https://') . $subjectOrigin;
    return [
      'VAPID' => [
        'subject' => $subjectOrigin,
        'publicKey' => getenv('PUSH_PUBLIC_KEY'),
        'privateKey' => getenv('PUSH_PRIVATE_KEY')
      ],
      [],
      new Client(['verify' => false])
    ];
  }

  public static function sendPush(int $user_id, array $data): bool
  {
    $subs = self::listSubs(['created_by' => $user_id]);
    if (empty($subs))
      return false;
    $sub = $subs[0];
    $webPush = new WebPush(self::getWebPushConfig());
    $report = $webPush->sendOneNotification(
      Subscription::create([
        'endpoint' => $sub->endpoint,
        'keys' => [
          'p256dh' => $sub->p256dh_key,
          'auth' => $sub->auth_key,
        ]
      ]),
      json_encode([
        'message' => $data['message'],
        'resource_url' => $data['resource_url'],
      ]),
    );
    if (!$report->isSuccess()) {
      $reason = trim($report->getReason());
      error_log("Failed to send notification for '{$data['message']}': $reason");
      if (preg_match('#Gone#', $reason)) {
        self::deleteSub($sub->id);
        return self::sendPush($user_id, $data);
      }
    }
    return $report->isSuccess();
  }

  public static function createSub(array $data): ?object
  {
    Db::run("insert or ignore into notification_subs(endpoint,p256dh_key,auth_key,created_by,created_at)
      values(?, ?, ?, ?, ?)", [
      $data['endpoint'],
      $data['p256dh_key'],
      $data['auth_key'],
      $data['created_by'],
      time()
    ]);
    $id = Db::lastInsertId();
    if (!$id) {
      [$sub] = self::listSubs(['endpoint' => $data['endpoint']]);
      return $sub ?? null;
    }
    return self::getSub($id);
  }

  public static function getSub(int $id): ?object
  {
    return Db::run("select * from notification_subs where id = ?", [$id])->fetch() ?: null;
  }

  public static function listSubs(?array $filters = null): array
  {
    [$clauses, $values] = Db::buildClauses($filters ?? []);
    return Db::run("select * from notification_subs " . ($clauses ?? '') . " order by created_at, updated_at desc", $values ?? [])->fetchAll();
  }

  public static function updateSub(int $id, array $data): ?object
  {
    Db::run("update notification_subs set
      endpoint = ?,
      p256dh_key = ?,
      auth_key = ?,
      updated_by = ?,
      updated_at = ?
    where id = ?", [
      $data['endpoint'],
      $data['p256dh_key'],
      $data['auth_key'],
      $data['updated_by'],
      time(),
      $id
    ]);
    return self::getSub($id);
  }

  public static function deleteSub(int $id): bool
  {
    return Db::run("delete from notification_subs where id = ?", [$id])->rowCount() == 1;
  }
}