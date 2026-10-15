<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Users;

class UsersController
{
	public static function viewUsers(Routy $app)
	{
		$app->render('users', [
			'users' => Users::list()
		]);
	}

	public static function viewCreate(Routy $app)
	{
		$app->render('createUser', [
			'roles' => Users::ROLES
		]);
	}

	public static function postCreate(Routy $app)
	{
		$body = $app->getBody();
		$user = Users::create([
			'username' => $body->username,
			'display_name' => $body->display_name,
			'role' => \in_array($body->role, Users::ROLES) ? $body->role : 'User',
			'created_by' => $app->getCtx('current_user')->id
		]);
		$app->redirect("/users/{$user->id}");
	}

	public static function viewUser(Routy $app)
	{
		$user = Users::read($app->getParam('id'));
		if (!$user)
			return $app->render('notFound', ['message' => 'User not found']);
		$app->render('user', [
			'user' => $user,
			'roles' => Users::ROLES,
			'statuses' => Users::STATUSES
		]);
	}

	public static function postSaveUser(Routy $app)
	{
		$user = Users::read($app->getParam('id'));
		if (!$user)
			return $app->render('notFound', ['message' => 'User not found']);
		$body = $app->getBody();
		if ($user->id == $app->getCtx('current_user')->id)
			if (
				isset($body->status, $body->role) &&
				($body->status != $user->status || $body->role != $user->role)
			)
				return $app->render('error', ['message' => 'Cannot update current user role/status']);
		$role = $body->role ?? $user->role;
		$status = $body->status ?? $user->status;
		Users::update($user->id, [
			'username' => $body->username,
			'display_name' => $body->display_name,
			'role' => \in_array($role, Users::ROLES) ? $role : 'User',
			'status' => \in_array($status, Users::STATUSES) ? $status : 'New',
			'updated_by' => $app->getCtx('current_user')->id
		]);
		$app->redirect("/users/{$user->id}");
	}

	public static function postDeleteUser(Routy $app)
	{
		$id = $app->getParam('id');
		$user = Users::read($id);
		if (!$user)
			return $app->render('notFound', ['message' => 'User not found']);
		if ($user->id == $app->getCtx('current_user')->id)
			return $app->render('error', ['message' => 'Cannot delete current user']);
		Users::delete($user->id);
		$app->redirect('/users');
	}
}
