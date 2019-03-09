<?php

class User extends \DB\Jig\Mapper {

    public $user;
    public $email;
    public $token_cleartext;
    
    public function __construct( $db ) {
        parent::__construct( $db, 'users.json' );
    }

    public function create( $new_user_data ) {
        // check for unique email
        $this->load( [ '@email=?', $new_user_data['email'] ] );
        if( ! $this->dry() ) return false;

        // check for unique username
        $this->load( [ '@user=?', $new_user_data['user'] ] );
        if( ! $this->dry() ) return 'Username already exists.';

        $this->set( 'user', $new_user_data['user'] );
        $this->set( 'email', $new_user_data['email'] );
        $this->set( 'pw', $new_user_data['pw'] );
        $this->set( 'confirmed', false );
        $this->set( 'token', $this->make_token( '1 day' ) );
        $this->save();
        return $this;
    }

    public function get_by_email( $email ) {
        $this->load( [ '@email=?', $email ] );

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

    public function email_token() {
        $to = $this->email;
        $from = 'blake@blakewatson.com';
        $subject = 'Confirm your Character Sheet account';
        $headers = array(
            'From' => $from,
            'Reply-To' => $from,
            'X-Mailer' => 'PHP/' . phpversion()
        );

        $url = sprintf(
            '%s/register/confirm/%s/%s',
            $_SERVER['SERVER_NAME'],
            $this->user,
            $this->token_cleartext
        );

        $message = "Click here to confirm your account: \n\n$url";

        mail( $to, $subject, $message, $headers );
    }

    public function get_token() {
        $token_obj = new Token();
        $token = $this->get( 'token' );
        $token_obj->hash = $token['hash'];
        $token_obj->expiry = $token['expiry'];
        $token_obj->one_time = $token['one_time'];
        return $token_obj;
    }

    public function delete_token() {
        $this->set( 'token', NULL );
        $this->save();
    }

}