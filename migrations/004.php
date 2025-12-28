<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

// Get existing columns to check what needs to be added
$columns = $db->exec('PRAGMA table_info(sheet);');
$columnNames = array_column($columns, 'name');

// Add created_at column if it doesn't exist
if (!in_array('created_at', $columnNames)) {
    $db->exec('ALTER TABLE "sheet" ADD COLUMN "created_at" TEXT DEFAULT NULL;');
}

// Add updated_at column if it doesn't exist
if (!in_array('updated_at', $columnNames)) {
    $db->exec('ALTER TABLE "sheet" ADD COLUMN "updated_at" TEXT DEFAULT NULL;');
}

// Add deleted_at column if it doesn't exist (NULL by default for soft deletes)
if (!in_array('deleted_at', $columnNames)) {
    $db->exec('ALTER TABLE "sheet" ADD COLUMN "deleted_at" TEXT DEFAULT NULL;');
}

die;
