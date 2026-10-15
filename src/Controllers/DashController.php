<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Tickets;
use App\Data\Categories;
use App\Data\Locations;
use App\Data\Users;
use App\Data\Notifications;

class DashController
{
	public static function viewDash(Routy $app)
	{
		$user = $app->getCtx('current_user');
		$filters = ['state' => ['!=', 'Completed']];
		if ($user->role != 'Admin')
			$filters['created_by'] = $user->id;
		$app->render('dash', [
			'tickets' => Tickets::list($filters)
		]);
	}
}
