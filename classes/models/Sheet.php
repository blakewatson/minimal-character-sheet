<?php

class Sheet extends \DB\SQL\Mapper {

    public $db;

    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'sheet' );
    }

    public function create_sheet( $name, $email, $is_2024 = true ) {
        $email = EmailUtils::normalize_email( $email );

        do {
            $slug = $this->random_slug();
        } while ( $this->get_sheet_by_slug( $slug ) );

        $str_data = $this->encode_sheet_data( $this->default_sheet_data( $name, null, $slug, $is_2024 ), 'create_sheet' );

        if( !$str_data ) {
            return false;
        }

        $this->slug = $slug;
        $this->name = $name;
        $this->data = $str_data;
        $this->email = $email;
        $this->is_public = false;
        $this->is_2024 = $is_2024;
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        $save_ok = $this->save();

        // If save didn't work for some reason, then we're going to clear the
        // reserved slug and return false.
        if( !$save_ok || !(int) $this->id ) {
            $this->slug = null;
            return false;
        }

        $str_data = $this->encode_sheet_data(
            $this->default_sheet_data( $name, (int) $this->id, $slug, $is_2024 ),
            'create_sheet for sheet ID: ' . $this->id
        );

        if( !$str_data ) {
            return false;
        }

        $this->data = $str_data;
        $save_ok = $this->save();

        if( !$save_ok ) {
            return false;
        }

        return (int) $this->id;
    }

    public function create_sheet_with_data( $name, $email, $data, $is_2024 = true ) {
        $email = EmailUtils::normalize_email( $email );

        do {
            $slug = $this->random_slug();
        } while ( $this->get_sheet_by_slug( $slug ) );

        // Handle both JSON strings and already-decoded data to prevent double-encoding
        // Data should be a JSON string from the frontend, but we handle both cases gracefully
        if( is_string( $data ) ) {
            // Data is a JSON string - decode it once to get the actual object/array
            $sheet_data = json_decode( $data, true );

            // If decoding fails, log the error and skip the update
            if( $sheet_data === null ) {
                error_log( 'Failed to decode JSON in create_sheet_with_data for sheet slug: ' . $slug );
                return false;
            }
        } elseif( is_array( $data ) ) {
            // Data is already decoded (array/object) - use it directly
            $sheet_data = $data;
        } else {
            error_log( 'Non-string, non-array data received in create_sheet_with_data for sheet slug: ' . $slug );
            return false;
        }

        // Update the sheet data with the new sheet's details
        $sheet_data['id'] = null;
        $sheet_data['slug'] = $slug;
        $sheet_data['characterName'] = $name;

        // Encode once for storage in the database
        $str_data = $this->encode_sheet_data( $sheet_data, 'create_sheet_with_data for sheet slug: ' . $slug );

        if( !$str_data ) {
            return false;
        }

        $this->slug = $slug;
        $this->name = $name;
        $this->email = $email;
        $this->data = $str_data;
        $this->is_public = false;
        $this->is_2024 = $is_2024;
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');

        $save_ok = $this->save();

        if( !$save_ok || !(int) $this->id ) {
            $this->slug = null;
            return false;
        }

        $sheet_data['id'] = (int) $this->id;
        $str_data = $this->encode_sheet_data( $sheet_data, 'create_sheet_with_data for sheet ID: ' . $this->id );

        if( !$str_data ) {
            return false;
        }

        $this->data = $str_data;
        $save_ok = $this->save();

        if( !$save_ok ) {
            return false;
        }

        return (int) $this->id;
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
            'created_at' => $this->exists( 'created_at' ) ? $this->get( 'created_at' ) : null,
            'updated_at' => $this->exists( 'updated_at' ) ? $this->get( 'updated_at' ) : null,
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
            'created_at' => $this->exists( 'created_at' ) ? $this->get( 'created_at' ) : null,
            'updated_at' => $this->exists( 'updated_at' ) ? $this->get( 'updated_at' ) : null,
            'email' => $this->email
        ];
    }

    public function get_all_sheets( $email ) {
        $email = EmailUtils::normalize_email( $email );
        $this->load( [ 'lower(trim(email)) = ?', $email ] );
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

    public function get_all_sheet_summaries( $email, $sort = 'id-asc' ) {
        $email = EmailUtils::normalize_email( $email );

        $order_by = 'id ASC'; // default sorting

        switch( $sort ) {
            case 'created-desc':
                $order_by = 'created_at DESC NULLS LAST, id DESC';
                break;
            case 'created-asc':
                $order_by = 'created_at ASC NULLS FIRST, id ASC';
                break;
            case 'name-asc':
                $order_by = 'name COLLATE NOCASE ASC NULLS LAST, id ASC';
                break;
            case 'name-desc':
                $order_by = 'name COLLATE NOCASE DESC NULLS LAST, id DESC';
                break;
            case 'updated-desc':
                $order_by = 'updated_at DESC NULLS LAST, id DESC';
                break;
            default:
                // If an unknown sort option is provided, default to created-asc
                $order_by = 'id ASC';
        }

        $rows = $this->db->exec(
            'SELECT id, slug, name, is_public, is_2024, email, created_at, updated_at FROM sheet WHERE lower(trim(email)) = ? ORDER BY ' . $order_by,
            [ $email ]
        );

        if( ! $rows ) return false;

        return array_map( function( $row ) {
            return [
                'id' => $row['id'],
                'slug' => $row['slug'],
                'name' => $row['name'],
                'is_public' => (bool) $row['is_public'],
                'is_2024' => (bool) $row['is_2024'],
                'email' => $row['email'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }, $rows );
    }

    public function save_sheet( $id, $name, $data ) {
        // Reuse existing mapper state when already loaded with this record.
        if( $this->dry() || (int) $this->id !== (int) $id ) {
            $this->load( [ 'id=?', $id ] );
        }

        if( $this->dry() ) return false;

        if( !$data || !is_string( $data ) ) {
            error_log( 'save_sheet: null or non-string data rejected for sheet ID: ' . $id );
            return false;
        }

        $this->set( 'name', $name );

        // Handle both JSON strings and already-decoded data to prevent double-encoding
        // Frontend sends data as a JSON string, but legacy data may have been double-encoded
        if( is_string( $data ) ) {
            // Data is already a JSON string - decode it first to get the actual object/array
            $decoded_data = json_decode( $data, true );

            // If decoding fails, reject the save to prevent overwriting good data with garbage
            if( $decoded_data === null ) {
                error_log( 'Failed to decode JSON in save_sheet for sheet ID: ' . $id );
                return false;
            }

            // Successfully decoded - re-encode it once for storage
            // This automatically fixes any legacy double-encoded data
            $encoded = json_encode( $decoded_data );
            if( !$encoded ) {
                error_log( 'Failed to encode JSON in save_sheet for sheet ID: ' . $id );
                return false;
            }
            $this->set( 'data', $encoded );
        } else {
            // Data is already decoded (array/object) - encode it once
            $encoded = json_encode( $data );
            if( !$encoded ) {
                error_log( 'Failed to encode JSON in save_sheet for sheet ID: ' . $id );
                return false;
            }
            $this->set( 'data', $encoded );
        }

        $this->set('updated_at', date('Y-m-d H:i:s'));
        return $this->save();
    }

    public function delete_sheet( $id ) {
        if( $this->dry() || (int) $this->id !== (int) $id ) {
            $this->load( [ 'id=?', $id ] );
        }

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

    private function default_sheet_data( $name, $id, $slug, $is_2024 ) {
        return [
            'id' => $id,
            'slug' => $slug,
            'is_2024' => (bool) $is_2024,
            'characterName' => $name,
        ];
    }

    private function encode_sheet_data( $sheet_data, $context ) {
        $str_data = json_encode( $sheet_data );

        if( !$str_data ) {
            error_log( 'Failed to encode JSON in ' . $context );
            return false;
        }

        return $str_data;
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
