<?php

define('ROOT_DIR', dirname(__DIR__));

error_reporting(E_ALL & ~E_DEPRECATED);

require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/scripts/backblaze-b2/upload-files-b2.php';

$dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
$dotenv->load();

$args = $argv;
array_shift($args);
$dry_run_key = array_search('--dry-run', $args, true);
$dry_run = $dry_run_key !== false;

if ($dry_run) {
    unset($args[$dry_run_key]);
    $args = array_values($args);
} else {
    $dotenv->required([
        'BACKBLAZE_KEY_ID',
        'BACKBLAZE_KEY',
        'BACKBLAZE_BUCKET_ID',
    ]);
}

$allowed_mime_types = [
    'application/zip',
    'application/x-zip-compressed',
];

function backup_env(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false) {
        return $default;
    }

    return $value;
}

$database_path = $args[0] ?? ROOT_DIR . '/data/db.sqlite3';
$database_path = realpath($database_path);

if ($database_path === false || !is_file($database_path) || !is_readable($database_path)) {
    fwrite(STDERR, "Database file does not exist or is not readable.\n");
    exit(1);
}

$backup_dir = ROOT_DIR . '/tmp/backups';

if (!is_dir($backup_dir) && !mkdir($backup_dir, 0700, true)) {
    fwrite(STDERR, "Could not create backup directory: $backup_dir\n");
    exit(1);
}

$timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d_H.i.s');
$filename_prefix = backup_env('BACKBLAZE_BACKUP_FILENAME_PREFIX');

if (str_contains($filename_prefix, '/') || str_contains($filename_prefix, '\\')) {
    fwrite(STDERR, "BACKBLAZE_BACKUP_FILENAME_PREFIX cannot contain path separators.\n");
    exit(1);
}

$backup_name = "{$filename_prefix}data-$timestamp";
$snapshot_path = "$backup_dir/$backup_name.sqlite3";
$zip_path = "$backup_dir/$backup_name.zip";
$b2_prefix = trim(backup_env('BACKBLAZE_BACKUP_PREFIX', 'minimal-character-sheet'), '/');
$b2_file_name = ($b2_prefix === '' ? '' : "$b2_prefix/") . basename($zip_path);

try {
    $source = new SQLite3($database_path, SQLITE3_OPEN_READONLY);
    $source->busyTimeout(5000);

    $snapshot = new SQLite3($snapshot_path);

    if (!$source->backup($snapshot)) {
        throw new Exception('Could not create SQLite database snapshot.');
    }

    $snapshot->close();
    $source->close();

    $zip = new ZipArchive();

    if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception("Could not create zip archive: $zip_path");
    }

    if (!$zip->addFile($snapshot_path, basename($database_path))) {
        $zip->close();
        throw new Exception('Could not add database snapshot to zip archive.');
    }

    if (!$zip->close()) {
        throw new Exception('Could not write zip archive.');
    }

    if ($dry_run) {
        printf("Created backup zip %s for Backblaze B2 file %s\n", $zip_path, $b2_file_name);

        if (!unlink($zip_path)) {
            throw new Exception("Could not remove dry-run zip: $zip_path");
        }

        return;
    }

    $upload = upload_file_b2($zip_path, $b2_file_name, $allowed_mime_types);

    if (!$upload['success']) {
        throw new Exception($upload['error'] ?? 'Could not upload backup to Backblaze B2.');
    }

    if (!unlink($zip_path)) {
        throw new Exception("Uploaded backup, but could not remove local zip: $zip_path");
    }

    printf(
        "Uploaded %s to Backblaze B2 as %s\n",
        $zip_path,
        $upload['b2_file']['fileName'] ?? $b2_file_name
    );
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
} finally {
    if (isset($snapshot) && $snapshot instanceof SQLite3) {
        $snapshot->close();
    }

    if (isset($source) && $source instanceof SQLite3) {
        $source->close();
    }

    if (is_file($snapshot_path)) {
        unlink($snapshot_path);
    }
}
