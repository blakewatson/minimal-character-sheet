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
        $sheet_data = addslashes( json_encode( $sheet_data ) );
        $f3->set( 'sheet', $sheet_data );
        $f3->set( 'sheet_id', $id );
        $f3->set( 'app', true );
        $this->auth->set_csrf();
        echo \Template::instance()->render( 'templates/sheet.html' );
    }

    public function add_sheet( $f3 ) {
        if( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $name = $f3->get( 'POST.sheet_name' );
            $email = $f3->get( 'SESSION.email' );
            $sheet = new Sheet( $f3->get( 'DB' ) );
            $sheet->create_sheet( $name, $email );
            $f3->reroute( '/dashboard' );
        } else {
            $this->auth->set_csrf();
            echo \Template::instance()->render( 'templates/add-sheet.html' );
        }
    }

    public function save_sheet( $f3, $params ) {
        if( ! $this->auth->verify_ajax_csrf() ) {
            echo json_encode([ 'success' => false ]);
            return;
        }

        $data = file_get_contents( 'php://input' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $result = $sheet->save_sheet( $params['sheet_id'], $data );

        if( ! $result ) {
            echo json_encode([ 'success' => false ]);
            return;
        }

        $this->auth->set_csrf();

        echo json_encode([
            'success' => true,
            'csrf' => $f3->get( 'CSRF' )
        ]);
        return;
    }

}