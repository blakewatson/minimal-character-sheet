<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

try {
    $db->exec('PRAGMA foreign_keys = ON;');
    $db->exec('BEGIN;');

    $db->exec(
        'CREATE TABLE "post" (
            "id" integer not null primary key autoincrement,
            "title" text not null,
            "body" text not null,
            "user_id" integer,
            "is_maintenance_message" integer not null default 0
                check ("is_maintenance_message" in (0, 1)),
            "published_at" text,
            "created_at" text not null default current_timestamp,
            "updated_at" text not null default current_timestamp,
            foreign key ("user_id") references "user" ("id") on delete set null
        );'
    );

    $db->exec(
        'CREATE TABLE "banner" (
            "id" integer not null primary key autoincrement,
            "post_id" integer,
            "body" text not null,
            "dismissible" integer not null default 1
                check ("dismissible" in (0, 1)),
            "published_at" text,
            "expires_at" text,
            "created_at" text not null default current_timestamp,
            "updated_at" text not null default current_timestamp,
            foreign key ("post_id") references "post" ("id") on delete set null
        );'
    );

    $db->exec('COMMIT;');
} catch (Throwable $e) {
    try {
        $db->exec('ROLLBACK;');
    } catch (Throwable $rollback_exception) {
        // Ignore rollback failures so the original migration error is reported.
    }

    fwrite(STDERR, 'Migration 008 failed: ' . $e->getMessage() . "\n");
    exit(1);
}

die;
