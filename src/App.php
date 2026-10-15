<?php

declare(strict_types=1);
date_default_timezone_set('America/New_York');
define('ROOT', realpath(__DIR__ . '/..'));
require ROOT . '/vendor/autoload.php';

\App\Services\Env::load();

use App\Controllers\ApiController;
use App\Controllers\DashController;
use App\Controllers\TicketsController;
use App\Controllers\UsersController;
use App\Controllers\SettingsController;
use App\Controllers\AuthController;
use App\Middleware\Auth;

$app = new \GingerTek\Routy([
	'render' => function ($view, $ctx, $app) {
		$ctx['view'] = ROOT . "/views/$view.php";
		$ctx['current_user'] = $app->getCtx('current_user');
		$ctx['is_admin'] = $app->getCtx('is_admin');
		$ctx['is_new'] = ($app->getCtx('current_user')->status ?? '') === 'New';
		$ctx['csrf'] = $_SESSION['csrf'] ?? '';
		ob_start();
		extract($ctx);
		include ROOT . '/views/_layout.php';
		return ob_get_clean();
	}
]);

const DATE_FORMAT = 'D, M d, Y @ g:i A';

try {
	$app->group('/api', function () use ($app) {
		$app->get('/notifications/config', ApiController::getNotificationsConfig(...));
		$app->post('/notifications/subscribe', Auth::authn(...), ApiController::postSubscribeToNotifications(...));
		$app->post('/notifications/resubscribe', Auth::authn(...), ApiController::postResubscribeToNotifications(...));
		$app->fallback(fn() => $app->sendJson(['error' => 'Not Found']));
	});
	$app->use(Auth::csrf(...));
	$app->get('/login', AuthController::viewLogin(...));
	$app->post('/login', AuthController::postLogin(...));
	$app->post('/logout', Auth::authn(...), AuthController::postLogout(...));
	$app->use(Auth::authn(...));
	$app->get('/', DashController::viewDash(...));
	$app->get('/tickets/create', TicketsController::viewCreate(...));
	$app->post('/tickets', TicketsController::postCreate(...));
	$app->get('/tickets/:id', TicketsController::viewTicket(...));
	$app->post('/tickets/:id', TicketsController::postSaveTicket(...));
	$app->post('/tickets/:id/delete', TicketsController::postDeleteTicket(...));
	$app->get('/tickets', Auth::authz('Admin')(...), TicketsController::viewTickets(...));
	$app->get('/users/create', UsersController::viewCreate(...));
	$app->get('/users', Auth::authz('Admin')(...), UsersController::viewUsers(...));
	$app->post('/users', Auth::authz('Admin')(...), UsersController::postCreate(...));
	$app->get('/users/:id', Auth::authz('Admin')(...), UsersController::viewUser(...));
	$app->post('/users/:id', Auth::authz('Admin')(...), UsersController::postSaveUser(...));
	$app->post('/users/:id/delete', Auth::authz('Admin')(...), UsersController::postDeleteUser(...));
	$app->get('/settings', Auth::authz('Admin')(...), SettingsController::viewSettings(...));
	$app->post('/settings', Auth::authz('Admin')(...), SettingsController::postSaveSettings(...));
	$app->get('/pass-reset', AuthController::viewPassReset(...));
	$app->post('/pass-reset', AuthController::postPassReset(...));
	$app->get('/unauthorized', fn() => $app->render('unauthorized'));
	$app->fallback(fn() => $app->render('notFound'));
} catch (Exception $ex) {
	error_log($ex->getMessage());
	preg_match('#/api#', $app->uri)
		? $app->sendJson(['error' => 'Internal Server Error'])
		: $app->render('error');
}
