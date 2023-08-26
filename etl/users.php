<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$users_file = file_get_contents(ROOT_DIR. '/data/users.json');
$users = json_decode($users_file, true);

$user_mapper = new \DB\SQL\Mapper( $db, 'user' );

foreach ($users as $slug => $user) {
  error_log($slug);
  error_log( print_r( $user, true ) );

  if (!$user['email']) {
    continue;
  }

  $user_mapper->email = $user['email'];
  $user_mapper->pw = $user['pw'];
  $user_mapper->confirmed = $user['confirmed'] ?? 0;
  $user_mapper->token = isset($user['token']) && $user['token'] ? json_encode($user['token']) : null;
  $user_mapper->reset_token = isset($user['reset_token']) && $user['reset_token'] ? json_encode($user['reset_token']) : null;
  $user_mapper->save();
  $user_mapper->reset();
}