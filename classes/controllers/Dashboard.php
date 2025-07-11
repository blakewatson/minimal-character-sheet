<?php

class Dashboard {

    private $auth;

    public function __construct( $f3, $params ) {
        $this->auth = new Authentication( $f3 );
        
        // bypass initial auth check if this is a GET on a single sheet or sheet data.
        // the sheet might be public. the sheet_single method will enforce auth if needed.
        $method = $f3->get( 'SERVER' )['REQUEST_METHOD'];
        $sheet_or_sheet_data = strpos( $params[0], '/sheet/' ) === 0 || strpos( $params[0], '/sheet-data/' ) === 0;
        
        if( $method === 'GET' && $sheet_or_sheet_data ) {
            return;
        }

        if( $f3->get( 'PATTERN' ) === '/sheet/@sheet_slug' && !$this->auth->is_logged_in() ) {
            // return an unauthorized response
            $f3->status( 401 );
            echo json_encode([ 'success' => false, 'reason' => 'unauthorized' ]);
            die();
        }
        
        // otherwise, enforce auth
        $this->auth->bounce();
    }

    public function sheet_list( $f3 ) {
        $email = $f3->get( 'SESSION.email' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheets = $sheet->get_all_sheets( $email );
        $f3->set( 'sheets', $sheets );
        $f3->set( 'dashboard', true );
        $this->auth->set_csrf();
        echo \Template::instance()->render( 'templates/dashboard.html' );
    }

    public function sheet_single( $f3, $params ) {
        $slug = $params['sheet_slug'];
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheet_data = $sheet->get_sheet_by_slug( $slug );
        $email = $f3->get( 'SESSION.email' ) ? $f3->get( 'SESSION.email' ) : '';
        
        // sheet not allowed to be accessed by current user
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) && ! $sheet_data['is_public'] ) {
            // save the requested url to the session
            $f3->set( 'SESSION.requested_url', $f3->get( 'SERVER.REQUEST_URI' ) );
            $this->auth->bounce();
            return;
        }
        
        // if this is a public sheet and the current user does not own it…
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) && $sheet_data['is_public'] ) {
            // …redact the email address
            $sheet_data['email'] = null;
        }
        
        // if it’s not public, enforce auth
        if( ! $sheet_data['is_public'] ) {
            $this->auth->bounce();
        }
        
        $character_name = $sheet_data['name'];
        $is_2024 = $sheet_data['is_2024'];
        $sheet_data = addslashes( json_encode( $sheet_data ) );
        $f3->set( 'sheet', $sheet_data );
        $f3->set( 'character_name', addslashes( $character_name ) );
        $f3->set( 'is_2024', $is_2024 ? 'true' : 'false' );
        $f3->set( 'sheet_slug', $slug );
        $f3->set( 'app', true );
        $this->auth->set_csrf();
        
        echo \Template::instance()->render( 'templates/sheet.html' );
    }
    
    public function get_sheet_data( $f3, $params ) {
        $email = $f3->get( 'SESSION.email' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheet_data = $sheet->get_sheet_by_slug( $params['sheet_slug'] );
        
        // sheet not allowed to be accessed by current user
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) && ! $sheet_data['is_public'] ) {
            $f3->error( 404 );
            return;
        }
        
        // read-only access allowed
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) && $sheet_data['is_public'] ) {
            // redact the email address
            $sheet_data['email'] = null;
        }
        
        echo json_encode([
            'success' => true,
            'sheet' => $sheet_data
        ]);
    }

    public function add_sheet( $f3 ) {
        if( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $name = $f3->get( 'POST.sheet_name' );
            $email = $f3->get( 'SESSION.email' );
            $is_2024 = $f3->get( 'POST.is_2024' ) === '1';
            $sheet = new Sheet( $f3->get( 'DB' ) );
            $sheet->create_sheet( $name, $email, $is_2024 );
            $f3->reroute( '/dashboard' );
        } else {
            $this->auth->set_csrf();
            echo \Template::instance()->render( 'templates/add-sheet.html' );
        }
    }

    public function save_sheet( $f3, $params ) {
        if( ! $this->auth->verify_ajax_csrf() ) {
            $this->auth->set_csrf();
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }
        
        $this->auth->set_csrf();

        $email = $f3->get( 'SESSION.email' );
        $name = $f3->get( 'REQUEST.name' );
        $data = $f3->get( 'REQUEST.data' );
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheet_data = $sheet->get_sheet_by_slug( $params['sheet_slug'] );
        
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) ) {
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }
        
        $result = $sheet->save_sheet( $sheet_data['id'], $name, $data );

        if( ! $result ) {
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'csrf' => $f3->get( 'CSRF' )
        ]);
        return;
    }

    public function delete_sheet( $f3, $params ) {
        if( ! $this->auth->verify_ajax_csrf() ) {
            $this->auth->set_csrf();
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }
        
        $this->auth->set_csrf();
        
        $sheet = new Sheet( $f3->get( 'DB' ) );
        $sheet_data = $sheet->get_sheet_by_slug( $params['sheet_slug'] );
        $email = $f3->get( 'SESSION.email' );
        
        if( strtolower( $sheet_data['email'] ) !== strtolower( $email ) ) {
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }

        $result = $sheet->delete_sheet( $sheet_data['id'] );
        echo json_encode([ 'success' => $result, 'csrf' => $f3->get( 'CSRF' ) ]);
    }
    
    public function make_sheet_public( $f3, $params ) {
        if( ! $this->auth->verify_ajax_csrf() ) {
            $this->auth->set_csrf();
            echo json_encode([ 'success' => false, 'reason' => 'ajax', 'csrf' => $f3->get( 'CSRF' ) ]);
            return;
        }
        
        $this->auth->set_csrf();
        
        $sheetObj = new Sheet( $f3->get( 'DB' ) );
        $sheet = $sheetObj->get_sheet_by_slug( $params['sheet_slug'] );
                
        if( $f3->get( 'SESSION.email' ) !== $sheet['email']) {
            echo json_encode([ 'success' => false, 'csrf' => $f3->get( 'CSRF' ) ]);
        }
        
        $value = $f3->get( 'REQUEST.is_public' );
        $sheetObj->set( 'is_public', $value === 'true' ? true : false );
        $sheetObj->save();
        
        echo json_encode([ 'success' => true, 'csrf' => $f3->get( 'CSRF' ) ]);
    }

}