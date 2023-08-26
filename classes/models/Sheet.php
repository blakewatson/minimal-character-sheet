<?php

class Sheet extends \DB\SQL\Mapper {

    public $db;

    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'sheet' );
    }

    public function create_sheet( $name, $email ) {
        $this->name = $name;
        $this->data = '';
        $this->email = $email;
        $this->save();
    }

    public function get_sheet( $id ) {
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'data' => $this->data,
            'is_public' => $this->exists( 'is_public' ) ? (bool) $this->get( 'is_public' ) : false,
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
                'name' => $this->name,
                'data' => $this->data,
                'is_public' => $this->exists( 'is_public' ) ? (bool) $this->get( 'is_public' ) : false,
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
        $this->set( 'data', $data );
        return $this->save();
    }

    public function delete_sheet( $id ) {
        error_log('Deleting sheet ' . $id);
        $this->load( [ 'id=?', $id ] );
        if( $this->dry() ) return false;
        return $this->erase();
    }

}