<?php

namespace App\Middleware;

use App\Services\Tokens;
use GingerTek\Routy;
use App\Data\Users;

class Auth
{
  public static function parseToken(Routy $app)
  {
    $token = $app->getHeader('Authorization') ?? $app->getCookie('access_token') ?? '';
    if ($token && ($data = Tokens::verify($token)))
      $app->setCtx('current_user', $data->data);
  }

  public static function authn(Routy $app)
  {
    self::parseToken($app);
    if (!$app->getCtx('current_user'))
      return $app->status(401)->sendJson(['error' => 'Unauthorized']);
    $currentUser = $app->getCtx('current_user');
    $app->setCtx('is_admin', $currentUser->role === 'Admin');
    if ($currentUser->status === 'New' && $app->uri != '/api/pass-reset')
      $app->status(302)->sendJson(['redirect' => '/api/pass-reset']);
  }

  public static function authz(string $role)
  {
    return function (Routy $app) use ($role) {
      $user = $app->getCtx('current_user');
      if (!$user)
        return $app->status(401)->sendJson(['error' => 'Unauthorized']);
      if ($user->role !== $role)
        return $app->status(403)->sendJson(['error' => 'Forbidden']);
    };
  }
}