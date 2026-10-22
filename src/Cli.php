<?php

declare(strict_types=1);
date_default_timezone_set('America/New_York');
define('ROOT', realpath(__DIR__ . '/..'));
require ROOT . '/vendor/autoload.php';

\App\Services\Env::load();

if (php_sapi_name() !== 'cli') {
  echo "This script must be run from the command line" . PHP_EOL;
  exit(1);
}

(match ($argv[1]) {
  'init-db' => function () {
      echo "Initializing database..." . PHP_EOL;
      \App\Services\Db::init();
      array_map(
      fn($category) => \App\Data\Locations::create(['name' => $category]),
      [
        'Kitchen',
        'Living Room',
        'Bedroom',
        'Garage',
        'Front Lawn',
        'Bathroom',
        'Dining Room',
        'Office'
      ]
      );
      array_map(
      fn($category) => \App\Data\Categories::create(['name' => $category]),
      [
        'Plumbing',
        'Electrical',
        'HVAC',
        'Landscaping',
        'Roofing',
        'Painting',
        'Carpentry',
        'Electronics'
      ]
      );
      echo "Database initialized." . PHP_EOL;
      exit();
    },
  'gen-vapid' => function () {
      echo "Generating VAPID keys..." . PHP_EOL;
      $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
      echo "VAPID keys generated:" . PHP_EOL;
      echo "PUSH_PUBLIC_KEY='{$keys['publicKey']}'" . PHP_EOL;
      echo "PUSH_PRIVATE_KEY='{$keys['privateKey']}'" . PHP_EOL;
      echo "Set these in your environment variables" . PHP_EOL;
      exit();
    },
  'gen-jwt' => function () {
      echo "Generating JWT secret..." . PHP_EOL;
      $secret = bin2hex(random_bytes(32));
      echo "JWT_SECRET='{$secret}'" . PHP_EOL;
      echo "Set this in your environment variables" . PHP_EOL;
      exit();
    },
  'create-user' => function () use ($argv) {
      echo "Creating user..." . PHP_EOL;
      $user = \App\Data\Users::create([
      'username' => $argv[2] ?? throw new \InvalidArgumentException("No username provided"),
      'display_name' => $argv[3] ?? throw new \InvalidArgumentException("No display name provided"),
      'role' => $argv[4] ?? 'User'
      ]);
      echo "User created:" . PHP_EOL;
      print_r($user);
      exit();
    },
  'update-user' => function () use ($argv) {
      echo "Updating user..." . PHP_EOL;
      $uid = $argv[2] ?? throw new \InvalidArgumentException("No username or ID provided");
      $user = (is_numeric($uid) ? \App\Data\Users::readDTO((int) $uid) : \App\Data\Users::readByUsername($uid, true)) ?? throw new \InvalidArgumentException("User not found");
      parse_str($argv[3] ?? '', $data);
      $data = $data ?: [];
      $user->username = $data['username'] ?? $user->username;
      $user->display_name = $data['display_name'] ?? $user->display_name;
      $user->role = $data['role'] ?? $user->role;
      $user->status = $data['status'] ?? $user->status;
      $user = \App\Data\Users::update($user->id, (array) $user);
      echo "User updated:" . PHP_EOL;
      print_r($user);
      exit();
    },
  'list-users' => function () {
      echo "Listing users..." . PHP_EOL;
      $users = \App\Data\Users::list();
      foreach ($users as $user)
        echo "{$user->id}: {$user->username} ({$user->display_name}) - Role: {$user->role}" . PHP_EOL;
      exit();
    },
  default => fn() => exit(1),
})();
