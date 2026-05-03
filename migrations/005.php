<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

// One-time setting to enable WAL mode for better concurrency and performance
$db->exec('PRAGMA journal_mode = WAL;');

// Get existing columns to check what needs to be added
$columns = $db->exec('PRAGMA table_info(user);');
$columnNames = array_column($columns, 'name');

// Add created_at column if it doesn't exist
if (!in_array('created_at', $columnNames)) {
    $db->exec('ALTER TABLE "user" ADD COLUMN "created_at" TEXT DEFAULT NULL;');
}

// Add updated_at column if it doesn't exist
if (!in_array('updated_at', $columnNames)) {
    $db->exec('ALTER TABLE "user" ADD COLUMN "updated_at" TEXT DEFAULT NULL;');
}

// Add deleted_at column if it doesn't exist (NULL by default for soft deletes)
if (!in_array('deleted_at', $columnNames)) {
    $db->exec('ALTER TABLE "user" ADD COLUMN "deleted_at" TEXT DEFAULT NULL;');
}

die;
