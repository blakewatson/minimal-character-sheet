<?php

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';
require ROOT_DIR . '/scripts/EmailNormalizationPlan.php';

$db = new DB\SQL('sqlite:'.ROOT_DIR.'/data/db.sqlite3');

$db->exec('PRAGMA foreign_keys = ON;');

try {
    // Keep all email rewrites and duplicate cleanup atomic.
    $db->exec('BEGIN;');
    $db->exec('PRAGMA defer_foreign_keys = ON;');

    // Reuse the audit planner so canonical-user selection stays in one place.
    $plan = EmailNormalizationPlan::build($db);
    $user_columns = array_column($db->exec('PRAGMA table_info("user");'), 'name');

    foreach ($plan['groups'] as $group) {
        // Move every sheet variant to the final canonical lowercase email.
        foreach ($group['email_variants'] as $email_variant) {
            $db->exec(
                'UPDATE sheet SET email = ? WHERE email = ?;',
                [$group['final_email'], $email_variant]
            );
        }

        // Remove duplicate user rows before changing the kept row's email.
        if (!empty($group['duplicate_user_ids'])) {
            $placeholders = implode(',', array_fill(0, count($group['duplicate_user_ids']), '?'));
            $db->exec(
                'DELETE FROM "user" WHERE id IN (' . $placeholders . ');',
                $group['duplicate_user_ids']
            );
        }

        // Preserve merged metadata on the canonical user row.
        update_canonical_user($db, $user_columns, $group);
    }

    // Normalize non-duplicate sheet/user emails that only differ by case or whitespace.
    foreach ($plan['remaining_normalizations']['sheets'] as $sheet_normalization) {
        $db->exec(
            'UPDATE sheet SET email = ? WHERE email = ?;',
            [$sheet_normalization['normalized_email'], $sheet_normalization['email']]
        );
    }

    foreach ($plan['remaining_normalizations']['users'] as $user_normalization) {
        $db->exec(
            'UPDATE "user" SET email = ? WHERE id = ?;',
            [$user_normalization['normalized_email'], $user_normalization['id']]
        );
    }

    // Enforce case-insensitive uniqueness while keeping the original FK-compatible unique email constraint.
    $db->exec(
        'CREATE UNIQUE INDEX IF NOT EXISTS idx_user_email_normalized
         ON "user"(lower(trim(email)));'
    );

    // Abort before commit if cleanup left duplicates, broken FKs, or unnormalized emails.
    validate_email_normalization($db);

    $db->exec('COMMIT;');
} catch (Throwable $e) {
    try {
        $db->exec('ROLLBACK;');
    } catch (Throwable $rollback_exception) {
        // Ignore rollback failures so the original migration error is reported.
    }

    fwrite(STDERR, 'Migration 007 failed: ' . $e->getMessage() . "\n");
    exit(1);
}

die;

function update_canonical_user($db, $user_columns, $group) {
    $sets = [
        'email = ?',
        'confirmed = ?'
    ];

    $params = [
        $group['merged_metadata']['email'],
        $group['merged_metadata']['confirmed']
    ];

    if (in_array('is_admin', $user_columns, true)) {
        $sets[] = 'is_admin = ?';
        $params[] = $group['merged_metadata']['is_admin'];
    }

    if (in_array('created_at', $user_columns, true)) {
        $sets[] = 'created_at = ?';
        $params[] = $group['merged_metadata']['created_at'];
    }

    if (in_array('updated_at', $user_columns, true)) {
        $sets[] = 'updated_at = ?';
        $params[] = $group['merged_metadata']['updated_at'];
    }

    $params[] = $group['canonical_user_id'];

    $db->exec(
        'UPDATE "user" SET ' . implode(', ', $sets) . ' WHERE id = ?;',
        $params
    );
}

function validate_email_normalization($db) {
    $duplicate_rows = $db->exec(
        'SELECT lower(trim(email)) AS normalized_email, COUNT(*) AS user_count
         FROM "user"
         GROUP BY lower(trim(email))
         HAVING COUNT(*) > 1;'
    );

    if (!empty($duplicate_rows)) {
        throw new RuntimeException('Duplicate normalized user emails remain.');
    }

    $foreign_key_errors = $db->exec('PRAGMA foreign_key_check;');

    if (!empty($foreign_key_errors)) {
        throw new RuntimeException('Foreign key validation failed.');
    }

    $unnormalized_users = (int) $db->exec(
        'SELECT COUNT(*) AS count
         FROM "user"
         WHERE email != lower(trim(email));'
    )[0]['count'];

    if ($unnormalized_users !== 0) {
        throw new RuntimeException('Unnormalized user emails remain.');
    }

    $unnormalized_sheets = (int) $db->exec(
        'SELECT COUNT(*) AS count
         FROM sheet
         WHERE email != lower(trim(email));'
    )[0]['count'];

    if ($unnormalized_sheets !== 0) {
        throw new RuntimeException('Unnormalized sheet emails remain.');
    }
}
