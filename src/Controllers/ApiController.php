<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Notifications;

class ApiController
{  
  public static function getNotificationsConfig(Routy $app)
  {
    $app->sendJson([
      'publicKey' => getenv('PUSH_PUBLIC_KEY')
    ]);
  }

  public static function postSubscribeToNotifications(Routy $app)
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

  public static function postResubscribeToNotifications(Routy $app)
  {
    $body = $app->getBody();
    $subs = Notifications::listSubs(['endpoint' => $body->old->endpoint]);
    if (empty($subs))
      return $app->sendJson(['success' => false]);
    $old = $subs[0];
    $new = $body->new;
    $result = Notifications::updateSub($old->id, [
      'endpoint' => $new->endpoint,
      'p256dh_key' => $new->p256dh_key,
      'auth_key' => $new->auth_key,
      'updated_by' => $app->getCtx('current_user')->id
    ]);
    $app->sendJson(['success' => !!$result]);
  }
}