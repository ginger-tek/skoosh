<?php

namespace App\Middleware;

use GingerTek\Routy;
use App\Data\Users;

class Auth
{
  public static function csrf(Routy $app)
  {
    session_start(['read_and_close' => true]);
    if ($app->method === 'POST') {
      $token = $app->getBody()->csrf ?? '';
      if (!isset($_SESSION['csrf']) || $_SESSION['csrf'] !== $token)
        return $app->end(403);
    } elseif ($app->method === 'GET') {
      session_start();
      if (!isset($_SESSION['csrf']))
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
      session_write_close();
    }
  }
  public static function authn(Routy $app)
  {
    session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'secure' => $_SERVER['HTTPS'] ?? false,
      'httponly' => true,
      'samesite' => 'Strict'
    ]);
    session_status() === PHP_SESSION_NONE && session_start(['read_and_close' => true]);
    if (!isset($_SESSION['user']) && $app->uri != '/login')
      return preg_match('#/api#', $app->uri)
        ? $app->status(403)->sendJson(['error' => 'Forbidden'])
        : $app->redirect('/login' . ($app->uri != '/' ? "?next=" . urlencode($app->uri) : ''));
    $app->setCtx('current_user', $_SESSION['user'] ?? null);
    $app->setCtx('is_admin', ($_SESSION['user']->role ?? '') === 'Admin');
    if (($_SESSION['user']->status ?? '') === 'New' && $app->uri != '/pass-reset')
      $app->redirect('/pass-reset');
  }

  public static function authz(string $role)
  {
    return function ($app) use ($role) {
      $user = $app->getCtx('current_user');
      if (!$user)
        return $app->redirect('/login');
      if ($user->role !== $role)
        return $app->redirect('/unauthorized');
    };
  }
}