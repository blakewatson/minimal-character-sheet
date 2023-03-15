<?php

use Postmark\PostmarkClient;

class Authentication {

    public $f3;
    public $db;
    public $session;

    public function __construct( $f3 ) {
        $this->f3 = $f3;
        $this->db = $f3->get( 'DB' );
        $this->session = new \DB\Jig\Session( $this->db, 'sessions', function( $sess ) { return true; } );
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
            $this->email_confirmation_token( $user );
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
        
        // verify the token
        $error_message = $this->verify_user_token( $user, $params['clear_token'] );
        
        if( $error_message ) {
            $f3->set( 'error_message', $error_message );
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
        $this->email_confirmation_token( $user );
        
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
    
    public function request_password_reset_form( $f3 ) {
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/request-password-reset.html' );
    }
    
    public function request_password_reset( $f3 ) {
        $f3->set( 'submitted', true );
        
        // check honeypot
        if( $f3->get( 'POST.username' ) ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/request-password-reset.html' );
            return;
        }

        // check csrf
        if( $this->verify_csrf() ) {
            $f3->set( 'error_message', 'Invalid login. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/request-password-reset.html' );
            return;
        }

        // get form data
        $email = $f3->get( 'POST.email' );

        // get user
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $email );
        
        if( $user->dry() ) {
            return;
        }
        
        // send password reset token
        $user->set( 'reset_token', $user->make_token( '1 hour' ) );
        $user->save();
        $this->email_password_reset_token( $user );
        
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/request-password-reset.html' );
    }
    
    public function password_reset_form( $f3, $params ) {
        // get the user received from the URL
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $params['email'] );
        
        // check user exists
        if( $user->dry() ) {
            $f3->set( 'error_message', 'Something went wrong.' );
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        // this email is saved to a hidden input and validated against the token on
        // the post request.
        $f3->set( 'email', $user->get( 'email' ) );
        
        // this token is saved to a hidden input and validated on the post request
        $f3->set( 'clear_token', $params['clear_token'] );
        
        $f3->set( 'show_form', true );
        
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/password-reset.html' );
    }
    
    public function password_reset( $f3, $params ) {
        // check honeypot
        if( $f3->get( 'POST.phone' ) ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }

        // check csrf
        if( $this->verify_csrf() ) {
            $f3->set( 'error_message', 'Invalid login. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $f3->get( 'POST.email' ) );
        
        if( $user->dry() ) {
            $f3->set( 'error_message', 'Something went wrong.' );
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        // set these hidden inputs
        $f3->set( 'email', $user->get( 'email' ) );
        $f3->set( 'clear_token', $f3->get( 'POST.clear_token' ) );
        
        $pw1 = $f3->get( 'POST.pw1' );
        $pw2 = $f3->get( 'POST.pw2' );
        
        if( $pw1 !== $pw2 ) {
            $f3->set( 'error_message', 'Passwords do not match. Please try again.' );
            $f3->set( 'show_form', true );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        $error_message = $this->verify_user_token( $user, $f3->get( 'POST.clear_token' ), 'reset_token' );
        
        if( $error_message ) {
            $f3->set( 'error_message', $error_message );
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        // success! change the password
        $user->set( 'pw', password_hash( $pw1, PASSWORD_DEFAULT ) );
        $user->save();
        
        // show success message
        $f3->set( 'success', true );
        
        echo \Template::instance()->render( 'templates/password-reset.html' );
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
    
    public function verify_user_token( $user, $clear_token, $token_key = 'token' ) {
        // get the user's token
        $token = $user->get_token( $token_key );
        
        // delete this if it's a one time use token
        if( $token->one_time ) {
            $user->delete_reset_token();
        }
        
        // add the clear-text token
        $token->clear = $clear_token;

        // check if token is expired
        if( $token->is_expired() ) {
            return 'Token is expired.';
        }

        // check if token is invalid
        if( ! $token->verify() ) {
            return 'Token is invalid.';
        }
    }
    
    public function email_confirmation_token( $user ) {
        $this->email_token( $user, 'register/confirm', 'Confirm your account', 'Click here to confirm your account:' );
    }
    
    public function email_password_reset_token( $user ) {
        $message = "You recently requested a password reset. If this wasn't you, ignore this message. If you do wish to reset your password, click the following link:";
        $this->email_token( $user, 'password-reset', 'Reset your password', $message );
    }

    public function email_token( $user, $url_path, $subject, $message ) {
        $env = $_ENV['ENV'];
        $postmark_secret = $_ENV['POSTMARK_SECRET'];
        error_log($postmark_secret);
        $client = new PostmarkClient( $postmark_secret );
        
        // construct email
        $email = $user->get( 'email' );
        $to = $email;
        $from = 'minimalcharactersheet@blakewatson.com';
        $subject = $subject;
        
        // if in development, use test email
        if( $env === 'DEVELOPMENT' ) {
            $to = 'test@blackhole.postmarkapp.com';
        }

        $url = sprintf(
            'https://%s/%s/%s/%s',
            $_SERVER['SERVER_NAME'],
            $url_path,
            $email,
            $user->token_cleartext
        );

        $message = "$message\n\n$url";

        $result = $client->sendEmail(
            $from,
            $to,
            $subject,
            $message
        );
    }

}