<?php

require_once('./vendor/autoload.php');
$f3 = require_once 'lib/base.php';

$f3->set( 'DEBUG', 3 );

$f3->set( 'AUTOLOAD', 'classes/models/; classes/controllers/' );
$f3->set( 'DB', new \DB\Jig( 'data/' ) );

// homepage
$f3->route( 'GET /', function( $f3 ) {
    echo \Template::instance()->render( 'templates/home.html' );
} );

// dashboard
$f3->route( 'GET /dashboard', 'Dashboard->sheet_list' );
$f3->route( 'GET /add-sheet', 'Dashboard->add_sheet' );
$f3->route( 'POST /add-sheet', 'Dashboard->add_sheet' );
// sheet view
$f3->route( 'GET /sheet/@sheet_id', 'Dashboard->sheet_single' );
$f3->route( 'POST /sheet/@sheet_id', 'Dashboard->save_sheet' );

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

$f3->run();