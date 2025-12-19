<?php

class Sheet extends \DB\SQL\Mapper {

    public $db;

    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'sheet' );
    }

    public function create_sheet( $name, $email, $is_2024 = true ) {
        do {
            $slug = $this->random_slug();
        } while ( $this->get_sheet_by_slug( $slug ) );

        $this->slug = $slug;
        $this->name = $name;
        $this->data = '';
        $this->email = $email;
        $this->is_public = false;
        $this->is_2024 = $is_2024;
        $this->save();
    }

    public function create_sheet_with_data( $name, $email, $data, $is_2024 = true ) {
        do {
            $slug = $this->random_slug();
        } while ( $this->get_sheet_by_slug( $slug ) );

        $this->slug = $slug;
        $this->name = $name;
        $this->email = $email;
        $this->data = '';
        $this->is_public = false;
        $this->is_2024 = $is_2024;
        $this->save();

        // Handle both JSON strings and already-decoded data to prevent double-encoding
        // Data should be a JSON string from the frontend, but we handle both cases gracefully
        if( is_string( $data ) ) {
            // Data is a JSON string - decode it once to get the actual object/array
            $sheet_data = json_decode( $data, true );

            // If decoding fails, log the error and skip the update
            if( $sheet_data === null ) {
                error_log( 'Failed to decode JSON in create_sheet_with_data for sheet slug: ' . $slug );
                return;
            }
        } else {
            // Data is already decoded (array/object) - use it directly
            $sheet_data = $data;
        }

        // Update the sheet data with the new sheet's details
        $sheet_data['id'] = $this->id;
        $sheet_data['slug'] = $this->slug;
        $sheet_data['characterName'] = $name;

        // Encode once for storage in the database
        $this->data = json_encode( $sheet_data );

        $this->save();
    }

    public function get_sheet( $id ) {
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'data' => $this->decode_sheet_data( $this->data ),
            'is_public' => $this->exists( 'is_public' ) ? (bool) $this->get( 'is_public' ) : false,
            'is_2024' => $this->exists( 'is_2024' ) ? (bool) $this->get( 'is_2024' ) : true,
            'email' => $this->email
        ];
    }

    public function get_sheet_by_slug( $slug ) {
        $this->load( [ 'slug=?', $slug ] );
        if( $this->dry() ) return false;
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'data' => $this->decode_sheet_data( $this->data ),
            'is_public' => $this->exists( 'is_public' ) ? (bool) $this->get( 'is_public' ) : false,
            'is_2024' => $this->exists( 'is_2024' ) ? (bool) $this->get( 'is_2024' ) : true,
            'email' => $this->email
        ];
    }

    public function get_all_sheets( $email ) {
        $this->load( [ 'email=?', $email ] );
        if( $this->dry() ) return false;

        $sheets = [];
        for( $i = 0; $i < $this->loaded(); $i++ ) {
            $sheets[] = [
                'id' => $this->id,
                'slug' => $this->slug,
                'name' => $this->name,
                'data' => $this->decode_sheet_data( $this->data ),
                'is_public' => $this->exists( 'is_public' ) ? (bool) $this->get( 'is_public' ) : false,
                'is_2024' => $this->exists( 'is_2024' ) ? (bool) $this->get( 'is_2024' ) : true,
                'email' => $this->email
            ];

            $this->next();
        }
        
        return $sheets;
    }

    public function save_sheet( $id, $name, $data ) {
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        $this->set( 'name', $name );

        // Handle both JSON strings and already-decoded data to prevent double-encoding
        // Frontend sends data as a JSON string, but legacy data may have been double-encoded
        if( is_string( $data ) ) {
            // Data is already a JSON string - decode it first to get the actual object/array
            $decoded_data = json_decode( $data, true );

            // If decoding fails, try using it as-is (shouldn't happen due to validation)
            if( $decoded_data === null ) {
                error_log( 'Failed to decode JSON in save_sheet for sheet ID: ' . $id );
                $this->set( 'data', $data );
            } else {
                // Successfully decoded - re-encode it once for storage
                // This automatically fixes any legacy double-encoded data
                $this->set( 'data', json_encode( $decoded_data ) );
            }
        } else {
            // Data is already decoded (array/object) - encode it once
            $this->set( 'data', json_encode( $data ) );
        }

        return $this->save();
    }

    public function delete_sheet( $id ) {
        error_log('Deleting sheet ' . $id);
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return $this->erase();
    }

    private function decode_sheet_data( $raw_data ) {
        if( empty( $raw_data ) ) {
            return null;
        }

        $decoded = json_decode( $raw_data, true );

        if( $decoded === null ) {
            error_log( 'Failed to decode JSON data: ' . substr( $raw_data, 0, 100 ) . '...' );
            return null;
        }

        if( is_string( $decoded ) ) {
            $double_decoded = json_decode( $decoded, true );
            if( $double_decoded !== null ) {
                error_log( 'Double-encoded JSON detected and corrected for data: ' . substr( $raw_data, 0, 50 ) . '...' );
                return $double_decoded;
            }
        }

        return $decoded;
    }

    public function random_slug() {
        $bank = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $slug = '';
        
        for ($i = 0; $i < 10; $i++) {
          $slug .= $bank[rand(0, 51)];
        }
        
        return $slug;
      }

}