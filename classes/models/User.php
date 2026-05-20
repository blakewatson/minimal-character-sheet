<?php

class User extends \DB\SQL\Mapper {

    public $email;
    public $token_cleartext;
    
    public function __construct( $db ) {
        parent::__construct( $db, 'user' );
    }

    public function create( $new_user_data ) {
        $email = EmailUtils::normalize_email( $new_user_data['email'] ?? '' );

        if( $email === '' ) return false;

        // check for unique email
        $this->load( [ 'lower(trim(email)) = ?', $email ] );
        if( ! $this->dry() ) return false;

        $this->set( 'email', $email );
        $this->set( 'pw', $new_user_data['pw'] );
        $this->set( 'confirmed', false );
        $this->set( 'token', json_encode( $this->make_token( '1 day' ) ) );
        $this->set( 'reset_token', null );
        $this->set( 'created_at', date('Y-m-d H:i:s') );
        $this->set( 'updated_at', date('Y-m-d H:i:s') );
        try {
            $save_ok = $this->save();
        } catch( \Exception $e ) {
            error_log( 'Failed to create user for email: ' . $email );
            return false;
        }

        if( ! $save_ok ) return false;

        // for some reason we have to reload this user
        $this->load( [ 'lower(trim(email)) = ?', $email ] );
        return $this;
    }

    public function get_by_email( $email ) {
        $email = EmailUtils::normalize_email( $email );

        if( $email === '' ) return false;

        $this->load( [ 'lower(trim(email)) = ?', $email ] );

        if( $this->dry() ) return false;
        return $this;
    }

    public function make_token( $duration ) {
        $token = new Token( $duration );
        $token->allow_once();
        $this->token_cleartext = $token->clear;
        $token->clear = NULL;
        return $token;
    }

    public function get_token( $key = 'token' ) {
        $token_obj = new Token();
        $token = $this->get( $key );
        $token = json_decode( $token, true );
        $token_obj->hash = $token['hash'];
        $token_obj->expiry = $token['expiry'];
        $token_obj->one_time = $token['one_time'];
        return $token_obj;
    }

    public function delete_token( $key = 'token' ) {
        $this->set( $key, NULL );
        $this->save();
    }
    
    public function delete_reset_token() {
        $this->set( 'reset_token', NULL );
        $this->save();
    }

}
