<?php

use Postmark\PostmarkClient;

class Authentication {

    public $f3;
    public $db;
    public $session;

    public function __construct( $f3 ) {
        $this->f3 = $f3;
        $this->db = $f3->get( 'DB' );
        $this->session = new \DB\Jig\Session( $this->db );
    }

    public function registration_form( $f3 ) {
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/register.html' );
    }

    public function register( $f3 ) {
        // check honeypot
        if( $f3->get( 'POST.phone' ) !== '' ) {
            $f3->set( 'error_message', 'Something went wrong. User was not created. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }

        // check csrf
        if( $this->verify_csrf() ) {
            $f3->set( 'error_message', 'Something went wrong. User was not created. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }

        // check passwords match
        if( $f3->get( 'POST.pw1' ) !== $f3->get( 'POST.pw2' ) ) {
            $f3->set( 'error_message', 'Passwords do not match.' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }

        // create user
        $user = new User( $f3->get( 'DB' ) );
        $result = $user->create( [
            'user' => $f3->get( 'POST.user' ),
            'email' => $f3->get( 'POST.email' ),
            'pw' => password_hash( $f3->get( 'POST.pw1' ), PASSWORD_DEFAULT )
        ] );

        // username taken
        if( $result === 'Username already exists.' ) {
            $f3->set( 'error_message', $result );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }
        
        // all good! send confirmation email
        if( $result !== false ) {
            $user->set( 'token', $user->make_token( '1 day' ) );
            $user->save();
            $this->email_token( $user );
        }

        $f3->set( 'success_message', 'An email has been sent to the email address provided.' );
        echo \Template::instance()->render( 'templates/register.html' );
    }

    public function confirm( $f3, $params ) {
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $params['email'] );

        if( $user->dry() || $user->get( 'confirmed' ) ) {
            $f3->set( 'error_message', 'Something went wrong.' );
            echo \Template::instance()->render( 'templates/confirm.html' );
            return;
        }

        // get the user's token
        $token = $user->get_token();
        // delete this if it's a one time use token
        if( $token->one_time ) $user->delete_token();
        // add the clear-text token
        $token->clear = $params['clear_token'];

        // check if token is expired
        if( $token->is_expired() ) {
            $f3->set( 'error_message', 'Token is expired.' );
            echo \Template::instance()->render( 'templates/confirm.html' );
            return;
        }

        // check if token is invalid
        if( ! $token->verify() ) {
            $f3->set( 'error_message', 'Token is invalid.' );
            echo \Template::instance()->render( 'templates/confirm.html' );
            return;
        }

        // confirm user
        $user->set( 'confirmed', true );
        $user->save();

        // save the username to the session
        $f3->set( 'SESSION.email', $user->get( 'email' ) );

        $f3->set( 'success', true );
        echo \Template::instance()->render( 'templates/confirm.html' );
    }

    public function resend_confirmation_form() {
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/re-confirm.html' );
    }

    public function resend_confirmation( $f3 ) {
        // check honeypot
        if( $f3->get( 'POST.username' ) ) {
            $f3->set( 'error_message', 'Confirmation error. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/re-confirm.html' );
            return;
        }

        // check csrf
        if( $this->verify_csrf() ) {
            $f3->set( 'error_message', 'Confirmation error. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/re-confirm.html' );
            return;
        }
        
        // get user by email
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $f3->get( 'POST.email' ) );

        if( $user->dry() || $user->get( 'confirmed' ) ) {
            $f3->set( 'error_message', 'Something went wrong.' );
            echo \Template::instance()->render( 'templates/re-confirm.html' );
            return;
        }

        $user->set( 'token', $user->make_token( '1 day' ) );
        $user->save();
        $this->email_token( $user );
        
        $f3->set( 'success_message', 'An email has been sent to the email address provided.' );
        echo \Template::instance()->render( 'templates/re-confirm.html' );
    }

    public function login_form( $f3 ) {
        if( $f3->get( 'SESSION.email' ) ) return $f3->reroute( '/dashboard' );
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/login.html' );
    }

    public function login( $f3 ) {
        // check honeypot
        if( $f3->get( 'POST.username' ) ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // check csrf
        if( $this->verify_csrf() ) {
            $f3->set( 'error_message', 'Invalid login. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // get form data
        $email = $f3->get( 'POST.email' );
        $pw = $f3->get( 'POST.pw' );

        // get user
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $email );
        if( $user->dry() ) {
            $f3->set( 'error_message', 'Invalid login. 3' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // check if user has confirmed
        if( ! $user->get( 'confirmed' ) ) {
            $f3->set( 'failed_confirmation', true );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // verify password
        if( ! password_verify( $pw, $user->get( 'pw' ) ) ) {
            $f3->set( 'error_message', 'Invalid login. 4' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // save the email to the session
        $f3->set( 'SESSION.email', $user->get( 'email' ) );

        $f3->reroute( '/dashboard' );
    }
    
    public function logout( $f3 ) {
        $f3->set( 'SESSION', [] );
        $f3->reroute( '/' );
    }

    public function is_logged_in() {
        $email = $this->f3->get( 'SESSION.email' );
        return isset( $email );
    }

    public function bounce( $dest = '/login' ) {
        if( ! $this->is_logged_in() ) return $this->f3->reroute( $dest );
    }

    public function set_csrf() {
        $this->f3->set( 'CSRF', $this->session->csrf() );
        $this->f3->copy( 'CSRF', 'SESSION.csrf' );
    }

    public function verify_csrf() {
        return $this->f3->get( 'SESSION.csrf' ) !== $this->f3->get( 'POST.csrf' );
    }

    public function verify_ajax_csrf() {
        $csrf = $this->f3->get( 'HEADERS.X-Ajax-Csrf' );
        if( ! $csrf ) return false;
        if( $csrf !== $this->f3->get( 'SESSION.csrf' ) ) return false;
        return true;
    }

    public function email_token( $user ) {
        $postmark_secret = getenv( 'POSTMARK_SECRET' );
        $client = new PostmarkClient( $postmark_secret );
        $email = $user->get( 'email' );
        $to = $email;
        $from = 'minimalcharactersheet@blakewatson.com';
        $subject = 'Confirm your account';

        $url = sprintf(
            'https://%s/register/confirm/%s/%s',
            $_SERVER['SERVER_NAME'],
            $email,
            $user->token_cleartext
        );

        $message = "Click here to confirm your account: \n\n$url";

        $result = $client->sendEmail(
            $from,
            $to,
            $subject,
            $message
        );
    }

}