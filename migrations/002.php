<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

// Get existing columns to check what needs to be added
$columns = $db->exec('PRAGMA table_info(sheet);');
$columnNames = array_column($columns, 'name');

// Add is_2024 column if it doesn't exist
if (!in_array('is_2024', $columnNames)) {
    $db->exec('ALTER TABLE "sheet" ADD COLUMN "is_2024" integer DEFAULT 0;');
}

// Add is_compact column if it doesn't exist
if (!in_array('is_compact', $columnNames)) {
    $db->exec('ALTER TABLE "sheet" ADD COLUMN "is_compact" integer DEFAULT 0;');
}

die; 