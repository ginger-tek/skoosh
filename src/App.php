<?php

declare(strict_types=1);
date_default_timezone_set('America/New_York');
define('ROOT', realpath(__DIR__ . '/..'));
require ROOT . '/vendor/autoload.php';

use App\Controllers\NotificationsController;
use App\Controllers\DashController;
use App\Controllers\TicketsController;
use App\Controllers\UsersController;
use App\Controllers\SettingsController;
use App\Controllers\AuthController;
use App\Controllers\ConfigController;
use App\Middleware\Auth;

$app = new \GingerTek\Routy;

\App\Services\Env::load();

try {
	$app->group('/api', function () use ($app) {
		$app->get('/config', ConfigController::getConfig(...));
		$app->get('/session', Auth::parseToken(...), AuthController::getSession(...));
		$app->post('/login', AuthController::postLogin(...));
		$app->group('/', Auth::authn(...), function () use ($app) {
			$app->post('/logout', AuthController::postLogout(...));
			$app->post('/notifications/subscribe', NotificationsController::postSubscribe(...));
			$app->post('/notifications/resubscribe', NotificationsController::postResubscribe(...));
			$app->post('/pass-reset', AuthController::postPassReset(...));
			
			$app->get('/dash', DashController::getDash(...));

			$app->post('/tickets', TicketsController::postCreate(...));
			$app->get('/tickets', TicketsController::listTickets(...));
			$app->get('/tickets/:id', TicketsController::getTicket(...));
			$app->put('/tickets/:id', TicketsController::putSaveTicket(...));
			$app->delete('/tickets/:id', TicketsController::deleteTicket(...));

			$app->get('/assignees', UsersController::listAssignees(...));

			$app->get('/users', Auth::authz('Admin')(...), UsersController::listUsers(...));
			$app->post('/users', Auth::authz('Admin')(...), UsersController::postCreate(...));
			$app->get('/users/:id', Auth::authz('Admin')(...), UsersController::getUser(...));
			$app->put('/users/:id', Auth::authz('Admin')(...), UsersController::putSaveUser(...));
			$app->delete('/users/:id', Auth::authz('Admin')(...), UsersController::deleteUser(...));
			
			$app->get('/settings', SettingsController::getSettings(...));
			$app->put('/settings', Auth::authz('Admin')(...), SettingsController::putSaveSettings(...));
		});
		$app->fallback(fn() => $app->sendJson(['error' => 'Not Found']));
	});
} catch (\Exception $ex) {
	error_log($ex->getMessage());
	$app->status(500)->sendJson(['error' => 'Internal Server Error']);
}
