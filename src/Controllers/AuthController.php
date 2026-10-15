<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Users;

class AuthController
{
  public static function viewLogin(Routy $app)
  {
    $app->render('login', ['next' => $app->getQuery('next')]);
  }

  public static function postLogin(Routy $app)
  {
    $body = $app->getBody();
    if (!isset($body->username, $body->password))
      return $app->render('login', ['error' => 'Missing values']);
    $user = Users::readByUsername($body->username);
    if (!$user || !password_verify($body->password, $user->passhash))
      return $app->render('login', ['error' => 'Invalid username or password']);
    session_start();
    session_regenerate_id(true);
    $_SESSION['user'] = (object) [
      'id' => $user->id,
      'username' => $user->username,
      'display_name' => $user->display_name,
      'role' => $user->role,
      'status' => $user->status
    ];
    session_write_close();
    if ($user->status == 'New')
      return $app->redirect('/pass-reset');
    $app->redirect($app->getQuery('next') ?? '/');
  }

  public static function viewPassReset(Routy $app)
  {
    $app->render('passReset');
  }

  public static function postPassReset(Routy $app)
  {
    $body = $app->getBody();
    if (!isset($body->current_password, $body->new_password, $body->confirm_new_password))
      return $app->render('passReset', ['error' => 'Missing values']);
    $user = Users::read($app->getCtx('current_user')->id);
    if (!password_verify($body->current_password, $user->passhash))
      return $app->render('passReset', ['error' => 'Current password is incorrect']);
    if ($body->new_password !== $body->confirm_new_password)
      return $app->render('passReset', ['error' => 'New passwords do not match']);
    Users::updatePassword($user->id, $body->new_password, $user->id);
    self::postLogout($app);
  }

  public static function postLogout(Routy $app)
  {
    session_start();
    unset($_SESSION['user']);
    session_destroy();
    $app->redirect('/login');
  }
}
