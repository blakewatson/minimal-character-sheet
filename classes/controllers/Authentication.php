<?php

use Postmark\PostmarkClient;

class Authentication {

    public $f3;
    public $db;
    public $session;

    public function __construct( $f3 ) {
        $this->f3 = $f3;
        $this->db = $f3->get( 'DB' );
        $this->session = new \DB\SQL\Session( $this->db, 'sessions', true, function( $sess ) { return true; } );
    }

    public function registration_form( $f3 ) {
        $this->set_csrf();

        $turnstile_site_key = $_ENV['CLOUDFLARE_TURNSTILE_SITE_KEY'] ?? null;
        $turnstile_secret_key = $_ENV['CLOUDFLARE_TURNSTILE_SECRET_KEY'] ?? null;

        if ( $turnstile_site_key && $turnstile_secret_key ) {
            $f3->set( 'use_turnstile', true );
            $f3->set( 'cloudflare_turnstile_site_key', $turnstile_site_key );
        }

        echo \Template::instance()->render( 'templates/register.html' );
    }

    public function register( $f3 ) {
        // check honeypot
        if( trim( (string) $f3->get( 'POST.phone' ) ) !== '' || trim( (string) $f3->get( 'POST.username' ) ) !== '' ) {
            $f3->set( 'error_message', 'Something went wrong. User was not created. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
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

        $email = $f3->get( 'POST.email' );

        if ( ! $email || ! str_contains( $email ?? '', '@' )) {
            $f3->set( 'error_message', 'Please enter a valid email address.' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/register.html' );
            return;
        }

        $normalized_email = strtolower( trim( (string) $email ) );

        // validate turnstile if enabled
        $turnstile_site_key = $_ENV['CLOUDFLARE_TURNSTILE_SITE_KEY'] ?? null;
        $turnstile_secret_key = $_ENV['CLOUDFLARE_TURNSTILE_SECRET_KEY'] ?? null;

        if ($turnstile_site_key && $turnstile_secret_key) {
            $token = $_POST['cf-turnstile-response'] ?? '';

            $validation = $this->validateTurnstile($token, $turnstile_secret_key);

            if ( ! $validation['success'] ) {
                $this->log_auth_event( 'turnstile_validation_failed', [
                    'email' => $normalized_email,
                ] );
                error_log('Turnstile validation failed: ' . implode(', ', $validation['error-codes']));
                $this->set_csrf();
                $f3->set( 'error_message', 'Something went wrong. User was not created.' );
                echo \Template::instance()->render( 'templates/register.html' );
                return;
            }
        }

        // create user
        $user = new User( $f3->get( 'DB' ) );
        $user = $user->create( [
            'email' => $email,
            'pw' => password_hash( $f3->get( 'POST.pw1' ), PASSWORD_DEFAULT )
        ] );

        $this->log_auth_event( 'register_attempt', [
            'email' => $normalized_email,
            'result' => $user === false ? 'rejected' : 'created'
        ] );
        
        // all good! send confirmation email
        if( $user !== false ) {
            $user->set( 'token', json_encode( $user->make_token( '1 day' ) ) );
            $user->save();
            $this->email_confirmation_token( $user );

            $this->record_rate_limit_hit( 'confirmation_email', $normalized_email, 60 );
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

        if( $f3->get( 'SERVER.REQUEST_METHOD' ) !== 'POST' ) {
            $f3->set( 'email', $user->get( 'email' ) );
            $f3->set( 'clear_token', $params['clear_token'] );
            $f3->set( 'show_form', true );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/confirm.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
            $f3->set( 'email', $user->get( 'email' ) );
            $f3->set( 'clear_token', $params['clear_token'] );
            $f3->set( 'show_form', true );
            $f3->set( 'error_message', 'Something went wrong.' );
            $this->set_csrf();
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

        $this->log_auth_event( 'account_confirmed', [
            'email' => strtolower( trim( (string) $user->get( 'email' ) ) )
        ] );

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
        if( trim( (string) $f3->get( 'POST.phone' ) ) !== '' || trim( (string) $f3->get( 'POST.username' ) ) !== '' ) {
            $f3->set( 'error_message', 'Confirmation error. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/re-confirm.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
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

        if( $this->check_confirmation_resend_rate_limit( $user->get( 'email' ) ) ) {
            $f3->set( 'error_message', 'Too many email requests. Please wait 60 seconds and try again.' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/re-confirm.html' );
            return;
        }

        $user->set( 'token', json_encode( $user->make_token( '1 day' ) ) );
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
        if( trim( (string) $f3->get( 'POST.username' ) ) !== '' ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/login.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
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

        // if ADMIN_ONLY, disallow regular users
        if ($f3->get( 'admin_only' ) && !$user->is_admin) {
            $f3->set( 'error_message', 'Log in is currently restricted to admins only.' );
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

        // check if there's a requested URL to redirect to
        $requested_url = $f3->get( 'SESSION.requested_url' );
        
        if( $requested_url ) {
            // clear the requested URL from session
            $f3->clear( 'SESSION.requested_url' );
            $f3->reroute( $requested_url );
        } else {
            $f3->reroute( '/dashboard' );
        }
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
        if( trim( (string) $f3->get( 'POST.phone' ) ) !== '' || trim( (string) $f3->get( 'POST.username' ) ) !== '' ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/request-password-reset.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
            $f3->set( 'error_message', 'Invalid login. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/request-password-reset.html' );
            return;
        }

        // get form data
        $email = trim( (string) $f3->get( 'POST.email' ) );

        // get user
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $email );
        
        if( $user->dry() || ! $user->get( 'confirmed' ) ) {
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/request-password-reset.html' );
            return;
        }
        
        // send password reset token
        $user->set( 'reset_token', json_encode( $user->make_token( '1 hour' ) ) );
        $user->save();
        $this->email_password_reset_token( $user );

        $this->log_auth_event( 'password_reset_requested', [
            'email' => strtolower( trim( (string) $user->get( 'email' ) ) )
        ] );
        
        $this->set_csrf();
        echo \Template::instance()->render( 'templates/request-password-reset.html' );
    }
    
    public function password_reset_form( $f3, $params ) {
        // get the user received from the URL
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $params['email'] );
        
        // check user exists
        if( $user->dry() || ! $user->get( 'confirmed' ) ) {
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
        if( trim( (string) $f3->get( 'POST.phone' ) ) !== '' ) {
            $f3->set( 'error_message', 'Invalid login. 1' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }

        // check csrf
        if( $this->has_invalid_csrf() ) {
            $f3->set( 'error_message', 'Invalid login. 2' );
            $this->set_csrf();
            echo \Template::instance()->render( 'templates/password-reset.html' );
            return;
        }
        
        $user = new User( $f3->get( 'DB' ) );
        $user->get_by_email( $f3->get( 'POST.email' ) );
        
        if( $user->dry() || ! $user->get( 'confirmed' ) ) {
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

        $this->log_auth_event( 'password_reset_completed', [
            'email' => strtolower( trim( (string) $user->get( 'email' ) ) )
        ] );
        
        // show success message
        $f3->set( 'success', true );
        
        echo \Template::instance()->render( 'templates/password-reset.html' );
    }

    public function is_logged_in() {
        $email = $this->f3->get( 'SESSION.email' );
        return isset( $email );
    }

    public function bounce( $dest = '/login' ) {
        if( ! $this->is_logged_in() ) {
            $this->f3->reroute( $dest );
        }
    }

    public function set_csrf() {
        // Check if session already has a CSRF token
        $existing_csrf = $this->f3->get( 'SESSION.csrf' );
        
        if( empty( $existing_csrf ) ) {
            // Generate new token only if none exists
            $csrf_token = $this->session->csrf();
            $this->f3->set( 'CSRF', $csrf_token );
            $this->f3->copy( 'CSRF', 'SESSION.csrf' );
        } else {
            // Use existing session token
            $this->f3->set( 'CSRF', $existing_csrf );
        }
    }

    public function has_invalid_csrf() {
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

        // delete this if it's a one time use token
        if( $token->one_time ) {
            $user->delete_token( $token_key );
        }
    }

    public function log_auth_event( $event, $context = [] ) {
        $request_context = $this->get_request_context();
        $pairs = array_merge(
            [ 'event' => $event ],
            $context,
            $request_context
        );

        $parts = [
            'auth_event',
            'event=' . $this->format_auth_log_value( $pairs['event'] ?? null ),
            'email=' . $this->format_auth_log_value( $pairs['email'] ?? null ),
            'result=' . $this->format_auth_log_value( $pairs['result'] ?? null ),
            'ip=' . $this->format_auth_log_value( $pairs['ip'] ?? null ),
            'remote_addr=' . $this->format_auth_log_value( $pairs['remote_addr'] ?? null ),
            'forwarded_for=' . $this->format_auth_log_value( $pairs['forwarded_for'] ?? null ),
            'real_ip=' . $this->format_auth_log_value( $pairs['real_ip'] ?? null ),
            'cf_connecting_ip=' . $this->format_auth_log_value( $pairs['cf_connecting_ip'] ?? null )
        ];

        error_log( implode( ' | ', $parts ) );
    }

    public function format_auth_log_value( $value ) {
        $value = trim( (string) $value );

        if( $value === '' ) {
            return '-';
        }

        return sprintf( '"%s"', str_replace( '"', '\"', $value ) );
    }

    public function get_request_context() {
        $remote_addr = trim( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
        $forwarded_for = trim( (string) ( $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '' ) );
        $real_ip = trim( (string) ( $_SERVER['HTTP_X_REAL_IP'] ?? '' ) );
        $cf_connecting_ip = trim( (string) ( $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '' ) );

        return [
            'ip' => $this->get_client_ip( $cf_connecting_ip, $forwarded_for, $real_ip, $remote_addr ),
            'remote_addr' => $remote_addr,
            'forwarded_for' => $forwarded_for,
            'real_ip' => $real_ip,
            'cf_connecting_ip' => $cf_connecting_ip
        ];
    }

    public function get_client_ip( ...$candidates ) {
        foreach( $candidates as $candidate ) {
            if( ! $candidate ) {
                continue;
            }

            foreach( explode( ',', $candidate ) as $possible_ip ) {
                $possible_ip = trim( $possible_ip );

                if( filter_var( $possible_ip, FILTER_VALIDATE_IP ) ) {
                    return $possible_ip;
                }
            }
        }

        return 'unknown';
    }

    public function rate_limit_key( $scope, $value ) {
        return 'ratelimit_auth_' . $scope . '_' . hash( 'sha256', (string) $value );
    }

    public function is_rate_limited( $scope, $value, $limit, $window_seconds ) {
        $cache = \Cache::instance();
        $cache_key = $this->rate_limit_key( $scope, $value );

        if( $cache->exists( $cache_key, $count ) ) {
            return $count >= $limit;
        }

        return false;
    }

    public function record_rate_limit_hit( $scope, $value, $window_seconds ) {
        $cache = \Cache::instance();
        $cache_key = $this->rate_limit_key( $scope, $value );

        if( $cache->exists( $cache_key, $count ) ) {
            $cache->set( $cache_key, $count + 1, $window_seconds );
            return;
        }

        $cache->set( $cache_key, 1, $window_seconds );
    }

    public function check_confirmation_resend_rate_limit( $email ) {
        $scope = 'confirmation_email';
        $normalized_email = strtolower( trim( (string) $email ) );

        if( $normalized_email === '' ) {
            return false;
        }

        if( $this->is_rate_limited( $scope, $normalized_email, 1, 60 ) ) {
            return true;
        }

        $this->record_rate_limit_hit( $scope, $normalized_email, 60 );

        return false;
    }
    
    public function email_confirmation_token( $user ) {
        $this->email_token( $user, 'register/confirm', [
            'subject' => 'Confirm your Minimal Character Sheet account',
            'intro_text' => 'Someone used this email address to create a Minimal Character Sheet account.',
            'link_text' => 'Confirm your account',
            'detail_text' => 'If you did not create this account, you can ignore this email. No account will be usable until the email address is confirmed.',
            'tag' => 'account-confirmation'
        ] );
    }
    
    public function email_password_reset_token( $user ) {
        $this->email_token( $user, 'password-reset', [
            'subject' => 'Reset your Minimal Character Sheet password',
            'intro_text' => 'A password reset was requested for your Minimal Character Sheet account.',
            'link_text' => 'Reset your password',
            'detail_text' => 'This link expires in 1 hour. If you did not request a password reset, you can ignore this email and your password will not change.',
            'tag' => 'password-reset'
        ] );
    }

    public function email_token( $user, $url_path, $email_config ) {
        $env = $_ENV['ENV'] ?? null;
        $postmark_secret = $_ENV['POSTMARK_SECRET'] ?? null;
        $postmark_from = $_ENV['POSTMARK_FROM'] ?? null;

        if (!$postmark_secret || !$postmark_from) {
            return;
        }

        $client = new PostmarkClient( $postmark_secret );
        
        // construct email
        $email = $user->get( 'email' );
        $to = $email;
        $from = $postmark_from;
        $subject = $email_config['subject'];
        
        // if in development, use test email
        if( $env === 'DEVELOPMENT' ) {
            $to = 'test@blackhole.postmarkapp.com';
        }

        $url = sprintf(
            'https://%s/%s/%s/%s',
            $_SERVER['SERVER_NAME'],
            $url_path,
            urlencode($email),
            urlencode($user->token_cleartext)
        );

        $escaped_url = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
        $html_body = sprintf(
            '<p>%s</p><p><a href="%s">%s</a></p><p>%s</p><p>Need help? Contact Blake at <a href="mailto:blake@minimalcharactersheet.com">blake@minimalcharactersheet.com</a>.</p>',
            htmlspecialchars( $email_config['intro_text'], ENT_QUOTES, 'UTF-8' ),
            $escaped_url,
            htmlspecialchars( $email_config['link_text'], ENT_QUOTES, 'UTF-8' ),
            htmlspecialchars( $email_config['detail_text'], ENT_QUOTES, 'UTF-8' )
        );
        $text_body = sprintf(
            "%s\n\n%s:\n%s\n\n%s\n\nNeed help? Contact Blake at blake@minimalcharactersheet.com.",
            $email_config['intro_text'],
            $email_config['link_text'],
            $url,
            $email_config['detail_text']
        );

        try {
            $client->sendEmail(
                $from,
                $to,
                $subject,
                $html_body,
                $text_body,
                $email_config['tag'],
                null,
                'blake@minimalcharactersheet.com'
            );
        } catch ( \Exception $e ) {
            error_log( 'Postmark email error: ' . $e->getMessage() );
            echo \Template::instance()->render( 'templates/email-error.html' );
            exit;
        }
    }

    public function validateTurnstile($token, $secret, $remoteip = null) {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret' => $secret,
            'response' => $token
        ];

        if ($remoteip) {
            $data['remoteip'] = $remoteip;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === FALSE) {
            return ['success' => false, 'error-codes' => ['internal-error']];
        }

        return json_decode($response, true);

    }

}
