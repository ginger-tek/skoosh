<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Users;
use App\Services\Tokens;

class AuthController
{
  public static function getSession(Routy $app)
  {
    $session = $app->getCtx('current_user');
    $user = Users::readDTO($session->id);
    if (!$user || ($user->status ?? null) === 'Inactive')
      return $app->status(401)
        ->setCookie('access_token', '', ['path' => '/', 'expires' => time() - 3600])
        ->setCookie('expires', '', ['path' => '/', 'expires' => time() - 3600])
        ->sendJson(['error' => 'Invalid session']);
    $app->sendJson($session);
  }

  public static function postLogin(Routy $app)
  {
    $body = $app->getBody();
    if (!isset($body->username, $body->password))
      return $app->status(400)->sendJson(['error' => 'Missing values']);
    $user = Users::readByUsername($body->username);
    if (!$user || !password_verify($body->password, $user->passhash))
      return $app->status(401)->sendJson(['error' => 'Invalid username or password']);
    $payload = [
      'id' => $user->id,
      'username' => $user->username,
      'display_name' => $user->display_name,
      'role' => $user->role,
      'status' => $user->status
    ];
    $tokenData = Tokens::sign($payload, $body->remember ?? false ? 3600 * 24 * 30 : 3600);
    $cookieParams = [
      'path' => '/',
      'expires' => $tokenData['exp'],
      'secure' => $app->getHeader('https') === 'on',
      'samesite' => 'Lax'
    ];
    $app->setCookie('access_token', $tokenData['token'], ['httponly' => true] + $cookieParams);
    $app->setCookie('expires', $tokenData['exp'], $cookieParams);
    if ($user->status == 'New')
      return $app->sendJson([...$tokenData, 'redirect' => '/pass-reset']);
    $app->sendJson($tokenData);
  }

  public static function postPassReset(Routy $app)
  {
    $body = $app->getBody();
    if (!isset($body->current_password, $body->new_password, $body->confirm_new_password))
      return $app->status(400)->sendJson(['error' => 'Missing values']);
    $user = Users::read($app->getCtx('current_user')->id);
    if (!password_verify($body->current_password, $user->passhash))
      return $app->status(400)->sendJson(['error' => 'Current password is incorrect']);
    if ($body->new_password !== $body->confirm_new_password)
      return $app->status(400)->sendJson(['error' => 'New passwords do not match']);
    Users::updatePassword($user->id, $body->new_password, $user->id);
    self::postLogout($app);
  }

  public static function postLogout(Routy $app)
  {
    $app->setCookie('access_token', '', ['path' => '/', 'expires' => time() - 3600]);
    $app->setCookie('expires', '', ['path' => '/', 'expires' => time() - 3600]);
    $app->sendJson(['success' => true]);
  }
}
