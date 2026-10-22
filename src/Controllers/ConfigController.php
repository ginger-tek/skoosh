<?php

namespace App\Controllers;

use GingerTek\Routy;
use App\Data\Tickets;
use App\Data\Users;

class ConfigController
{
  public static function getConfig(Routy $app)
  {
    $app->sendJson([
      'push_key' => getenv('PUSH_PUBLIC_KEY'),
      'tickets' => [
        'states' => Tickets::STATES,
        'reasons' => Tickets::REASONS,
        'urgency_levels' => Tickets::URGENCY_LEVELS,
      ],
      'users' => [
        'roles' => Users::ROLES,
        'statuses' => Users::STATUSES,
      ]
    ]);
  }
}