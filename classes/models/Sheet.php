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

    public function get_sheet( $id ) {
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'data' => json_decode( $this->data, true ),
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
            'data' => json_decode( $this->data, true ),
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
                'data' => json_encode( $this->data, true ),
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
        $this->set( 'data', json_encode( $data ) );
        return $this->save();
    }

    public function delete_sheet( $id ) {
        error_log('Deleting sheet ' . $id);
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return $this->erase();
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