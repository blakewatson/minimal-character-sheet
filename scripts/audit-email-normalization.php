<?php

define( 'ROOT_DIR', dirname( __DIR__ ) );

require ROOT_DIR . '/vendor/autoload.php';
require ROOT_DIR . '/scripts/EmailNormalizationPlan.php';

if( PHP_SAPI !== 'cli' ) {
    fwrite( STDERR, "This script must be run from the command line.\n" );
    exit( 1 );
}

$database_path = $argv[1] ?? ROOT_DIR . '/data/db.sqlite3';

if( in_array( $database_path, [ '-h', '--help' ], true ) ) {
    print_usage();
    exit( 0 );
}

if( ! is_readable( $database_path ) ) {
    fwrite( STDERR, "Database is not readable: {$database_path}\n" );
    print_usage();
    exit( 1 );
}

$resolved_database_path = realpath( $database_path );
$db = new DB\SQL( 'sqlite:' . $resolved_database_path );
$db->exec( 'PRAGMA foreign_keys = ON;' );

$plan = EmailNormalizationPlan::build( $db );
$total_users = (int) $db->exec( 'SELECT COUNT(*) AS total FROM "user"' )[0]['total'];
$total_sheets = (int) $db->exec( 'SELECT COUNT(*) AS total FROM sheet' )[0]['total'];
$has_multiple_confirmed_users_with_sheets = has_risk_reason(
    $plan,
    'multiple_confirmed_users_with_sheets'
);

print_line( 'Email Normalization Audit' );
print_line( '=========================' );
print_line( 'Database: ' . $resolved_database_path );
print_line( 'Generated: ' . $plan['generated_at'] );
print_line();
print_line( 'Totals' );
print_line( '------' );
print_line( 'Users: ' . $total_users );
print_line( 'Sheets: ' . $total_sheets );
print_line( 'Duplicate normalized email groups: ' . $plan['totals']['duplicate_groups'] );
print_line( 'Duplicate users to delete: ' . $plan['totals']['duplicate_users'] );
print_line( 'Proposed sheet reassignments: ' . $plan['totals']['sheet_reassignments'] );
print_line(
    'Multiple confirmed users with sheets: ' .
    ( $has_multiple_confirmed_users_with_sheets ? 'YES' : 'no' )
);
print_line();

if( empty( $plan['groups'] ) ) {
    print_line( 'No duplicate normalized email groups found.' );
} else {
    print_line( 'Duplicate Groups' );
    print_line( '----------------' );

    foreach( $plan['groups'] as $index => $group ) {
        print_group( $group, $index + 1 );
    }
}

if( ! empty( $plan['remaining_normalizations']['users'] ) ) {
    print_line( 'Non-Duplicate User Email Normalizations' );
    print_line( '---------------------------------------' );

    foreach( $plan['remaining_normalizations']['users'] as $user ) {
        print_line(
            sprintf(
                '- user #%d: %s -> %s',
                $user['id'],
                format_nullable( $user['email'] ),
                format_nullable( $user['normalized_email'] )
            )
        );
    }

    print_line();
}

if( ! empty( $plan['remaining_normalizations']['sheets'] ) ) {
    print_line( 'Non-Duplicate Sheet Email Normalizations' );
    print_line( '----------------------------------------' );

    foreach( $plan['remaining_normalizations']['sheets'] as $sheet ) {
        print_line(
            sprintf(
                '- %d sheet(s): %s -> %s',
                $sheet['sheet_count'],
                format_nullable( $sheet['email'] ),
                format_nullable( $sheet['normalized_email'] )
            )
        );
    }

    print_line();
}

exit( 0 );

function print_usage() {
    $script = basename( __FILE__ );

    fwrite(
        STDERR,
        "Usage: php scripts/{$script} [path/to/db.sqlite3]\n" .
        "If no database path is provided, data/db.sqlite3 is audited.\n"
    );
}

function print_group( $group, $number ) {
    print_line( sprintf( '%d. %s', $number, $group['normalized_email'] ) );
    print_line( '   Final email: ' . $group['final_email'] );
    print_line(
        sprintf(
            '   Proposed canonical user: #%d <%s>',
            $group['canonical_user_id'],
            $group['canonical_user_email']
        )
    );
    print_line(
        '   Duplicate user IDs to delete: ' .
        ( empty( $group['duplicate_user_ids'] ) ? 'none' : implode( ', ', $group['duplicate_user_ids'] ) )
    );
    print_line( '   Email variants: ' . implode( ', ', array_map( 'format_nullable', $group['email_variants'] ) ) );
    print_line( '   Proposed sheet reassignment count: ' . $group['sheet_reassignment_count'] );
    print_line(
        '   Risk: ' .
        ( empty( $group['risk_reasons'] ) ? 'none' : implode( ', ', $group['risk_reasons'] ) )
    );
    print_line( '   Merged metadata:' );
    print_line(
        sprintf(
            '     confirmed=%d, is_admin=%d, created_at=%s, updated_at=%s',
            $group['merged_metadata']['confirmed'],
            $group['merged_metadata']['is_admin'],
            format_nullable( $group['merged_metadata']['created_at'] ),
            format_nullable( $group['merged_metadata']['updated_at'] )
        )
    );
    print_line( '   Users:' );

    foreach( $group['users'] as $user ) {
        $canonical_marker = $user['id'] === $group['canonical_user_id'] ? ' canonical' : '';

        print_line(
            sprintf(
                '     - #%d%s | email=%s | confirmed=%d | is_admin=%d | created_at=%s | updated_at=%s | sheets=%d',
                $user['id'],
                $canonical_marker,
                format_nullable( $user['email'] ),
                $user['confirmed'],
                $user['is_admin'],
                format_nullable( $user['created_at'] ),
                format_nullable( $user['updated_at'] ),
                $user['sheet_count']
            )
        );
    }

    if( ! empty( $group['sheet_variants'] ) ) {
        print_line( '   Sheet variants:' );

        foreach( $group['sheet_variants'] as $variant ) {
            print_line(
                sprintf(
                    '     - %s: %d sheet(s)',
                    format_nullable( $variant['email'] ),
                    $variant['sheet_count']
                )
            );
        }
    }

    print_line();
}

function has_risk_reason( $plan, $risk_reason ) {
    foreach( $plan['risk_groups'] as $group ) {
        if( in_array( $risk_reason, $group['risk_reasons'], true ) ) return true;
    }

    return false;
}

function format_nullable( $value ) {
    if( $value === null || $value === '' ) return '(empty)';
    return (string) $value;
}

function print_line( $line = '' ) {
    echo $line . "\n";
}
