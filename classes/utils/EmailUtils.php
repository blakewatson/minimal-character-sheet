<?php

class EmailUtils {

    public static function normalize_email( $email ) {
        return strtolower( trim( $email ?? '' ) );
    }

    public static function emails_match( $email_a, $email_b ) {
        return self::normalize_email( $email_a ) === self::normalize_email( $email_b );
    }

}
