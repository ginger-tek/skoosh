<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Tickets;
use App\Data\Categories;
use App\Data\Locations;
use App\Data\Users;
use App\Data\Notifications;

class TicketsController
{
	public static function viewTickets(Routy $app)
	{
		$app->render('tickets', [
			'tickets' => Tickets::list()
		]);
	}

	public static function viewCreate(Routy $app)
	{
		$app->render('createTicket', [
			'categories' => Categories::list(),
			'locations' => Locations::list()
		]);
	}

	public static function postCreate(Routy $app)
	{
		$body = $app->getBody();
		$ticket = Tickets::create([
			'summary' => $body->summary,
			'assignee_id' => Users::readFirstOrDefaultAdmin()->id,
			'location_id' => $body->location_id,
			'category_id' => $body->category_id,
			'urgency' => $body->urgency,
			'created_by' => $app->getCtx('current_user')->id
		]);
		Notifications::sendPush($ticket->assignee_id, [
			'message' => 'New Ticket Created',
			'resource_url' => "/tickets/{$ticket->id}"
		]);
		$app->redirect("/tickets/{$ticket->id}");
	}

	public static function viewTicket(Routy $app)
	{
		$ticket = Tickets::read($app->getParam('id'));
		if (!$ticket)
			return $app->render('notFound', ['message' => 'Ticket not found']);
		$ticket->notes = Tickets::listNotes($ticket->id);
		$app->render('ticket', [
			'ticket' => $ticket,
			'assignees' => Users::list(),
			'categories' => Categories::list(),
			'locations' => Locations::list()
		]);
	}

	public static function postSaveTicket(Routy $app)
	{
		$id = $app->getParam('id');
		$cur_user = $app->getCtx('current_user');
		$ticket = Tickets::read($id);
		if (!$ticket)
			return $app->render('notFound', ['message' => 'Ticket not found']);
		$body = $app->getBody();
		Tickets::update($ticket->id, [
			'summary' => $body->summary,
			'state' => $app->getCtx('is_admin') ? $body->state : $ticket->state,
			'reason' => $app->getCtx('is_admin') && $body->state == 'On Hold' ? $body->reason ?? '' : '',
			'urgency' => $body->urgency,
			'assignee_id' => $app->getCtx('is_admin') ? $body->assignee_id : $ticket->assignee_id,
			'category_id' => $body->category_id,
			'location_id' => $body->location_id,
			'updated_by' => $app->getCtx('current_user')->id
		]);
		if ($ticket->state != $body->state && $ticket->created_by != $cur_user->id) {
			Notifications::sendPush($ticket->created_by, [
				'message' => "Ticket State Changed from {$ticket->state} to {$body->state}",
				'resource_url' => "/tickets/{$ticket->id}"
			]);
		}
		if ($ticket->urgency != $body->urgency && $ticket->created_by != $cur_user->id) {
			$old_urgency = Tickets::URGENCY_LEVELS[$ticket->urgency] ?? $ticket->urgency;
			$new_urgency = Tickets::URGENCY_LEVELS[$body->urgency] ?? $body->urgency;
			Notifications::sendPush($ticket->created_by, [
				'message' => "Ticket Urgency Changed from {$old_urgency} to {$new_urgency}",
				'resource_url' => "/tickets/{$ticket->id}"
			]);
		}
		if (!empty($body->note)) {
			$note = Tickets::createNote([
				'ticket_id' => $ticket->id,
				'note' => $body->note,
				'created_by' => $cur_user->id
			]);
			if ($note->created_by != $ticket->assignee_id && $note->created_by != $ticket->created_by)
				Notifications::sendPush(
					$note->created_by == $ticket->assignee_id ? $ticket->created_by : $ticket->assignee_id,
					[
						'message' => "{$cur_user->display_name} Added a New Note",
						'resource_url' => "/tickets/{$ticket->id}"
					]
				);
		}
		$app->redirect("/tickets/{$ticket->id}");
	}

	public static function postDeleteTicket(Routy $app)
	{
		$id = $app->getParam('id');
		$ticket = Tickets::read($id);
		if (!$ticket)
			return $app->render('notFound', ['message' => 'Ticket not found']);
		Tickets::delete($ticket->id);
		$app->redirect('/');
	}
}
