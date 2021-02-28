<?php

// load packages
require_once('./vendor/autoload.php');

// bring in Fat Free
$f3 = require_once 'lib/base.php';

// load phpdotenv
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

// require POSTMARK_SECRET
$dotenv->required( 'POSTMARK_SECRET' );

// set default debug level
$debug_level = getenv( 'DEBUG' );
$debug_level = $debug_level ? $debug_level : 0;

$f3->set( 'DEBUG', $debug_level );

$f3->set( 'AUTOLOAD', 'classes/models/; classes/controllers/' );
$f3->set( 'DB', new \DB\Jig( 'data/' ) );

// homepage
$f3->route( 'GET /', function( $f3 ) {
    echo \Template::instance()->render( 'templates/home.html' );
} );

// dashboard
$f3->route( 'GET /dashboard', 'Dashboard->sheet_list' );
$f3->route( 'GET|POST /add-sheet', 'Dashboard->add_sheet' );
$f3->route( 'POST /make-public/@sheet_id', 'Dashboard->make_sheet_public' );

// sheet view
$f3->route( 'GET /sheet/@sheet_id', 'Dashboard->sheet_single' );
$f3->route( 'POST /sheet/@sheet_id', 'Dashboard->save_sheet' );
$f3->route( 'DELETE /sheet/@sheet_id', 'Dashboard->delete_sheet' );

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

$f3->set( 'ONERROR', function( $f3, $params ) {
    echo \Template::instance()->render( 'templates/error.html' );
} );

$f3->run();