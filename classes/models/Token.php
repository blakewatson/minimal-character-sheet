<?php

class Token {

	public $id;
	public $interval;
	public $life_in_hours;
	public $clear;
	public $hash;
	public $expiry;
	public $one_time;
	public $mapper;

	public function __construct( $relative_duration = NULL ) {
		if( $relative_duration ) {
			$token_clear = bin2hex( random_bytes(60) );
			$token_hash = password_hash( $token_clear, PASSWORD_DEFAULT );

			$expiry = new DateTime( "now" );
			$now_timestamp = $expiry->getTimestamp();
			$interval = DateInterval::createFromDateString( $relative_duration );
			$expiry->add( $interval );
			$life_in_seconds = $expiry->getTimestamp() - $now_timestamp;
			$life_in_hours = $life_in_seconds / 60 / 60;
			
			$this->life_in_hours = $life_in_hours;
			$this->clear = $token_clear;
			$this->hash = $token_hash;
			$this->expiry = $expiry->format( "Y-m-d H:i:s" );
			$this->one_time = false;
		}
	}

	public function allow_once() {
		$this->one_time = true;
	}

	public function is_expired() {
		// get expiry date
		$expiry = date_timestamp_get( date_create( $this->expiry ) );

		// check expiry
		$now = date_timestamp_get( date_create( 'now' ) );
		$expired = $now > $expiry;

		if( $expired ) {
			return true;
		}

		return false;
	}

	public function verify() {
    if( $this->clear == NULL ) return false;

		if( ! password_verify( $this->clear, $this->hash ) ) {
			return false;
		}

		return true;
	}

	public function get_browser_token() {
		return array(
			"clear" => $this->clear,
			"id" => $this->id,
			"life" => $this->life_in_hours
		);
	}

}