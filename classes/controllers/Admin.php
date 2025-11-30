<?php

class Admin {

    private $auth;

    public function __construct( $f3, $params ) {
        $this->auth = new Authentication( $f3 );
        
        // otherwise, enforce auth
        $this->auth->bounce();

        // bounce if not admin
        $email = $f3->get( 'SESSION.email' );
        $user = new User( $f3->get( 'DB' ) );
        $user_data = $user->get_by_email( $email );
        if( ! $user_data['is_admin'] ) {
            $f3->error( 403 );
            return;
        }
    }

    public function admin_dashboard( $f3 ) {
        $f3->set( 'admin_dashboard', true );
        echo \Template::instance()->render( 'templates/admin.html' );
    }

    public function admin_users( $f3 ) {
        $db = $f3->get( 'DB' );
        $users = $db->exec( 'SELECT id, email, confirmed, is_admin FROM user ORDER BY email' );
        $f3->set( 'users', $users );
        echo \Template::instance()->render( 'templates/admin_users.html' );
    }

    public function restore_sheet( $f3 ) {
        $db = $f3->get( 'DB' );
        $users = $db->exec( 'SELECT DISTINCT email FROM user' );
        $f3->set( 'users', $users );

        $this->auth->set_csrf();

        // if POST, handle restore
        if( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $name = $f3->get( 'POST.sheet_name' );
            $email = $f3->get( 'POST.email' );
            $is_2024 = $f3->get( 'POST.is_2024' ) === '1';
            $sheet_data = $f3->get( 'POST.sheet_data' );

            $sheet = new Sheet( $f3->get( 'DB' ) );
            $sheet->create_sheet_with_data( $name, $email, $sheet_data, $is_2024 );

            echo \Template::instance()->render( 'templates/admin_restore_sheet.html' );
            return;
        }

        // else, show restore form
        echo \Template::instance()->render( 'templates/admin_restore_sheet.html' );
    }

  }