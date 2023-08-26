<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$sheets_file = file_get_contents(ROOT_DIR. '/data/sheet.json');
$sheets = json_decode($sheets_file, true);

$sheets_mapper = new \DB\SQL\Mapper( $db, 'sheet' );

foreach ($sheets as $slug => $sheet) {
  error_log($slug);
  error_log($sheet['name']);

  if (!$sheet['email']) {
    continue;
  }


  $sheets_mapper->slug = $slug;
  $sheets_mapper->email = $sheet['email'];
  $sheets_mapper->name = $sheet['name'];
  $sheets_mapper->is_public = isset($sheet['is_public']) && $sheet['is_public'] ? 1 : 0;
  $sheets_mapper->data = isset($sheet['data']) ? json_encode($sheet['data']) : null;
  $sheets_mapper->save();
  $sheets_mapper->reset();
}