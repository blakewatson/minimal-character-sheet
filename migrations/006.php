<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

// Add indexes on frequently queried columns
$db->exec([
    'CREATE INDEX IF NOT EXISTS idx_sheet_email ON sheet(email);',
    'CREATE INDEX IF NOT EXISTS idx_sheet_updated_at ON sheet(updated_at);',
]);

die;
