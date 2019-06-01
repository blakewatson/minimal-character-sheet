<?php

class Sheet extends \DB\Jig\Mapper {

    public $db;

    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'sheet.json' );
    }

    public function create_sheet( $name, $email ) {
        $this->set( 'name', $name );
        $this->set( 'data', '' );
        $this->set( 'email', $email );
        $this->save();
    }

    public function get_sheet( $id ) {
        $this->load( [ '@_id=?', $id ] );
        if( $this->dry() ) return false;
        return [
            'id' => $this->_id,
            'name' => $this->name,
            'data' => $this->data
        ];
    }

    public function get_all_sheets( $email ) {
        $this->load( [ '@email=?', $email ] );
        if( $this->dry() ) return false;

        $sheets = [];
        for( $i = 0; $i < $this->loaded(); $i++ ) {
            $sheets[] = [
                'id' => $this->_id,
                'name' => $this->name,
                'data' => $this->data,
            ];

            $this->next();
        }
        
        return $sheets;
    }

    public function save_sheet( $id, $name, $data ) {
        $this->load( [ '@_id=?', $id ] );
        if( $this->dry() ) return false;
        $this->set( 'name', $name );
        $this->set( 'data', $data );
        return $this->save();
    }

    public function delete_sheet( $id ) {
        error_log('Deleting sheet ' . $id);
        $this->load( [ '@_id=?', $id ] );
        if( $this->dry() ) return false;
        return $this->erase();
    }

}