<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Notifications;

class NotificationsController
{
  public static function postSubscribe(Routy $app)
  {
    $body = $app->getBody();
    $result = Notifications::createSub([
      'endpoint' => $body->endpoint,
      'p256dh_key' => $body->keys->p256dh,
      'auth_key' => $body->keys->auth,
      'created_by' => $app->getCtx('current_user')->id
    ]);
    $app->sendJson(['success' => !!$result]);
  }

  public static function postResubscribe(Routy $app)
  {
    $body = $app->getBody();
    $subs = Notifications::listSubs(['endpoint' => $body->old_endpoint]);
    if (empty($subs))
      return $app->sendJson(['success' => false]);
    $old = $subs[0];
    $new = $body->new_subscription;
    $result = Notifications::updateSub($old->id, [
      'endpoint' => $new->endpoint,
      'p256dh_key' => $new->keys->p256dh,
      'auth_key' => $new->keys->auth,
      'updated_by' => $app->getCtx('current_user')->id
    ]);
    $app->sendJson(['success' => !!$result]);
  }
}