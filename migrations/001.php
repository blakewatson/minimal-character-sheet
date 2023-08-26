<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec([
    'PRAGMA foreign_keys = ON;',

    'CREATE TABLE "user" (
        "id" integer not null primary key autoincrement,
        "email" text not null unique,
        "pw" text not null,
        "confirmed" integer,
        "token" text,
        "reset_token" text
    );',

    'CREATE TABLE "sheet" (
        "id" integer not null primary key autoincrement,
        "slug" text not null unique,
        "email" text not null,
        "name" text not null,
        "is_public" integer,
        "data" text,
        FOREIGN KEY (email) REFERENCES user(email)
    );'
]);

die;