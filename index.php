<?php

// load packages
require_once('./vendor/autoload.php');

// bring in Fat Free
$f3 = \Base::instance();

// load phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


if ( ( $_ENV['ENV'] ?? '' ) === 'MAINTENANCE') {
    error_log($_ENV['ENV']);
    echo \Template::instance()->render( 'templates/maintenance.html' );
    return;
}

// require POSTMARK_SECRET
$dotenv->required( 'POSTMARK_SECRET' );

// set default debug level
$debug_level = $_ENV['DEBUG'] ?? '';
$debug_level = $debug_level ? $debug_level : 0;

$f3->set( 'DEBUG', $debug_level );

$f3->set( 'AUTOLOAD', 'classes/models/; classes/controllers/' );
$f3->set( 'DB', new \DB\SQL( 'sqlite:data/db.sqlite3' ) );

// homepage
$f3->route( 'GET /', function( $f3 ) {
    $f3->set( 'lightbox', true );
    echo \Template::instance()->render( 'templates/home.html' );
} );

// dashboard
$f3->route( 'GET /dashboard', 'Dashboard->sheet_list' );
$f3->route( 'GET|POST /add-sheet', 'Dashboard->add_sheet' );
$f3->route( 'POST /make-public/@sheet_slug', 'Dashboard->make_sheet_public' );

// sheet view
$f3->route( 'GET /sheet/@sheet_slug', 'Dashboard->sheet_single' );
$f3->route( 'POST /sheet/@sheet_slug', 'Dashboard->save_sheet' );
$f3->route( 'DELETE /sheet/@sheet_slug', 'Dashboard->delete_sheet' );
// read-only public sheet refresh. This endpoint receives a request for a sheet
// and provides an updated date if it exists. The endpoint returns sheet data
// only if the sheet has been updated.
$f3->route( 'GET /sheet-data/@sheet_slug', 'Dashboard->get_sheet_data' );
$f3->route( 'GET /print/@sheet_slug', 'Dashboard->print_sheet' );

// login/logout
$f3->route( 'GET /login', 'Authentication->login_form' );
$f3->route( 'POST /login', 'Authentication->login' );
$f3->route( 'GET /logout', 'Authentication->logout' );

// registration
$f3->route( 'GET /register/confirm/@email/@clear_token', 'Authentication->confirm' );
$f3->route( 'GET /register/confirm/send', 'Authentication->resend_confirmation_form' );
$f3->route( 'POST /register/confirm/send', 'Authentication->resend_confirmation' );
$f3->route( 'GET /register', 'Authentication->registration_form' );
$f3->route( 'POST /register', 'Authentication->register' );

// password reset
$f3->route( 'GET /request-password-reset', 'Authentication->request_password_reset_form' );
$f3->route( 'POST /request-password-reset', 'Authentication->request_password_reset' );
$f3->route( 'GET /password-reset/@email/@clear_token', 'Authentication->password_reset_form' );
$f3->route( 'POST /password-reset', 'Authentication->password_reset' );

// admin dashboard
$f3->route( 'GET /admin', 'Admin->admin_dashboard' );
$f3->route( 'GET /admin/users', 'Admin->admin_users' );
$f3->route( 'GET|POST /admin/restore-sheet', 'Admin->restore_sheet' );

// set up custom error page
$f3->set( 'ONERROR', function( $f3, $params ) {
    echo \Template::instance()->render( 'templates/error.html' );
} );

/**
 * Helper functions
 * Move these to a separate helpers.php file if the list grows.
 */

/**
 * Returns a versioned asset path using Laravel Mix's manifest file.
 * Used in templates for cache busting: {{ mix('/app.js') }}
 */
function mix($path) {
    static $manifest;
    if (!$manifest) {
        $manifestPath = __DIR__ . '/dist/mix-manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        } else {
            $manifest = [];
        }
    }
    $path = '/' . ltrim($path, '/');
    return '/dist' . ($manifest[$path] ?? $path);
}

$f3->run();