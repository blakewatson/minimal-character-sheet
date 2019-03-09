<?php

class Dashboard {

    private $auth;

    public function __construct( $f3 ) {
        $this->auth = new Authentication( $f3 );
        $this->auth->bounce();
    }

    public function sheet_list( $f3 ) {
        $email = $f3->get( 'SESSION.email' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheets = $sheet->get_all_sheets( $email );
        $f3->set( 'sheets', $sheets );
        echo \Template::instance()->render( 'templates/dashboard.html' );
    }

    public function sheet_single( $f3, $params ) {
        $id = $params['sheet_id'];
        $email = $f3->get( 'SESSION.email' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheet_data = $sheet->get_sheet( $id );
        $sheet_data = json_encode( $sheet_data );
        $f3->set( 'sheet', $sheet_data );
        echo \Template::instance()->render( 'templates/sheet.html' );
    }

    public function add_sheet( $f3 ) {
        if( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $name = $f3->get( 'POST.sheet_name' );
            $sheet = new Sheet( $f3->get( 'DB' ) );
            $sheet->create_sheet( $name );
            $f3->reroute( '/dashboard' );
        } else {
            $this->auth->set_csrf();
            echo \Template::instance()->render( 'templates/add-sheet.html' );
        }
    }

}