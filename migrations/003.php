<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

// Get existing columns to check what needs to be added
$columns = $db->exec('PRAGMA table_info(user);');
$columnNames = array_column($columns, 'name');

// Add is_admin column if it doesn't exist
if (!in_array('is_admin', $columnNames)) {
    $db->exec('ALTER TABLE "user" ADD COLUMN "is_admin" integer DEFAULT 0;');
}

die;
