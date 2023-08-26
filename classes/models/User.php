<?php

class User extends \DB\SQL\Mapper {

    public $email;
    public $token_cleartext;
    
    public function __construct( $db ) {
        parent::__construct( $db, 'user' );
    }

    public function create( $new_user_data ) {
        // check for unique email
        $this->load( [ 'email = ?', $new_user_data['email'] ] );
        if( ! $this->dry() ) return false;

        $this->set( 'email', $new_user_data['email'] );
        $this->set( 'pw', $new_user_data['pw'] );
        $this->set( 'confirmed', false );
        $this->set( 'token', json_encode( $this->make_token( '1 day' ) ) );
        $this->set( 'reset_token', null );
        $this->set( 'is_public', false );
        $this->save();
        // for some reason we have to reload this user
        $this->load( [ 'email = ?', $new_user_data['email'] ] );
        return $this;
    }

    public function get_by_email( $email ) {
        $this->load( [ 'email=?', $email ] );

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

    public function delete_token() {
        $this->set( 'token', NULL );
        $this->save();
    }
    
    public function delete_reset_token() {
        $this->set( 'reset_token', NULL );
        $this->save();
    }

}