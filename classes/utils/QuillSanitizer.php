<?php

class QuillSanitizer {

    private const DISALLOWED_EMBEDS = [ 'image' ];

    public static function sanitize_sheet_data( $value ) {
        if( !is_array( $value ) ) {
            return $value;
        }

        foreach( $value as $key => $child ) {
            $value[$key] = self::sanitize_sheet_data( $child );
        }

        if( !self::is_quill_delta( $value ) ) {
            return $value;
        }

        $value['ops'] = array_values( array_filter( $value['ops'], function( $op ) {
            $insert = $op['insert'];

            if( !is_array( $insert ) ) {
                return true;
            }

            return empty( array_intersect( self::DISALLOWED_EMBEDS, array_keys( $insert ) ) );
        } ) );

        return $value;
    }

    private static function is_quill_delta( $value ) {
        if(
            !array_key_exists( 'ops', $value ) ||
            !is_array( $value['ops'] ) ||
            empty( $value['ops'] )
        ) {
            return false;
        }

        foreach( $value['ops'] as $op ) {
            if( !is_array( $op ) || !array_key_exists( 'insert', $op ) ) {
                return false;
            }

            $unexpected_keys = array_diff( array_keys( $op ), [ 'insert', 'attributes' ] );

            if( !empty( $unexpected_keys ) ) {
                return false;
            }

            if( array_key_exists( 'attributes', $op ) && !is_array( $op['attributes'] ) ) {
                return false;
            }

            $insert = $op['insert'];

            if( is_string( $insert ) ) {
                continue;
            }

            if( !is_array( $insert ) || count( $insert ) !== 1 || array_is_list( $insert ) ) {
                return false;
            }
        }

        return true;
    }
}
