<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Tickets;

class DashController
{
	public static function getDash(Routy $app)
	{
		$user = $app->getCtx('current_user');
		$filters = ['state' => ['!=', 'Completed']];
		if ($user->role !== 'Admin')
			$filters['created_by'] = $user->id;
		$app->sendJson([
			'tickets' => Tickets::list($filters)
		]);
	}
}
