<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Users;

class UsersController
{
	public static function listUsers(Routy $app)
	{
		$app->sendJson(Users::list());
	}

	public static function listAssignees(Routy $app)
	{
		$app->sendJson(Users::list(['role' => 'Admin']));
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
		$app->status(201)->sendJson($user);
	}

	public static function getUser(Routy $app)
	{
		$user = Users::readDTO($app->getParam('id'));
		if (!$user)
			return $app->status(404)->sendJson(['error' => 'User not found']);
		$app->sendJson($user);
	}

	public static function putSaveUser(Routy $app)
	{
		$user = Users::readDTO($app->getParam('id'));
		if (!$user)
			return $app->status(404)->sendJson(['error' => 'User not found']);
		$body = $app->getBody();
		if ($user->id == $app->getCtx('current_user')->id)
			if (
				isset($body->status, $body->role) &&
				($body->status != $user->status || $body->role != $user->role)
			)
				return $app->status(400)->sendJson(['error' => 'Cannot update current user role/status']);
		$role = $body->role ?? $user->role;
		$status = $body->status ?? $user->status;
		Users::update($user->id, [
			'username' => $body->username,
			'display_name' => $body->display_name,
			'role' => \in_array($role, Users::ROLES) ? $role : 'User',
			'status' => \in_array($status, Users::STATUSES) ? $status : 'New',
			'updated_by' => $app->getCtx('current_user')->id
		]);
		$app->sendJson($user);
	}

	public static function deleteUser(Routy $app)
	{
		$id = $app->getParam('id');
		$user = Users::readDTO($id);
		if (!$user)
			return $app->status(404)->sendJson(['error' => 'User not found']);
		if ($user->id == $app->getCtx('current_user')->id)
			return $app->status(400)->sendJson(['error' => 'Cannot delete current user']);
		Users::delete($user->id);
		$app->sendJson(['success' => true]);
	}
}
