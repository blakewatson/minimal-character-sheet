<?php

class Admin {

    private Authentication $auth;

    public function __construct( \Base $f3, array $params ) {
        $this->auth = new Authentication( $f3 );
        
        // otherwise, enforce auth
        $this->auth->bounce();

        // bounce if not admin
        $email = $this->auth->get_logged_in_email();

        // shouldn't be possible but
        if ( empty( $email ) ) {
            $f3->error( 403 );
            return;
        }

        $user = new User( $f3->get( 'DB' ) );
        $user_data = $user->get_by_email( $email );
        
        if( ! $user_data || ! $user_data['is_admin'] ) {
            $f3->error( 403 );
            return;
        }
    }

    public function dashboard( \Base $f3 ) {
        $f3->set( 'admin_dashboard', true );
        echo \Template::instance()->render( 'templates/admin.html' );
    }
    
    public function posts( \Base $f3 ) {
        $post_mapper = new Post( $f3->get( 'DB' ) );
        $posts = $post_mapper->get_post_list();
        $f3->set( 'posts', $posts );
        echo \Template::instance()->render( 'templates/admin_posts.html' );
    }

    public function post_create( \Base $f3 ) {
        if ( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $post_data = $this->get_post_form_data( $f3 );

            if ( isset( $post_data['error'] ) ) {
                $f3->set( 'error_msg', $post_data['error'] );
                $f3->set( 'post', $post_data );
                echo \Template::instance()->render( 'templates/admin_post_create.html' );
                return;
            }

            $user = new User( $f3->get( 'DB' ) );
            $user_data = $user->get_by_email( $this->auth->get_logged_in_email() );
            
            if ( ! $user_data || ! ($user_data['is_admin'] ?? false) ) {
                $f3->error( 403 );
                return;
            }

            $post_mapper = new Post( $f3->get( 'DB' ) );
            $post_mapper->create_post(
                $post_data['title'],
                $post_data['body'],
                (int)$user_data['id'],
                $post_data['published_at']
            );

            // redirect to posts list
            $f3->reroute( '/admin/posts' );
            exit;
        }

        echo \Template::instance()->render( 'templates/admin_post_create.html' );
    }

    public function post_edit( \Base $f3, array $params ) {
        if ( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' && isset( $params['id'] ) ) {
            // handle post edit submission
            $id = (int)$params['id'];
            $post_data = $this->get_post_form_data( $f3 );

            $post_mapper = new Post( $f3->get( 'DB' ) );

            $post_mapper->load( ['id = ?', $id] );
            if ( $post_mapper->dry() ) {
                $f3->error( 404 );
                return;
            }

            if ( isset( $post_data['error'] ) ) {
                $post_data['id'] = $id;
                $f3->set( 'error_msg', $post_data['error'] );
                $f3->set( 'post', $post_data );
                echo \Template::instance()->render( 'templates/admin_post_edit.html' );
                return;
            }

            $post_mapper->title = $post_data['title'];
            $post_mapper->body = $post_data['body'];
            $post_mapper->published_at = $post_data['published_at'];
            $post_mapper->updated_at = date( 'Y-m-d H:i:s' );
            $post_mapper->save();

            // redirect to posts list
            $f3->reroute( '/admin/posts' );
            exit;
        }

        if ( !isset( $params['id'] ) ) {
            $f3->reroute( '/admin/posts' );
            exit;
        }

        $post_mapper = new Post( $f3->get( 'DB' ) );
        $post_mapper->load( ['id = ?', (int)$params['id']] );

        if ( $post_mapper->dry() ) {
            $f3->error( 404 );
            return;
        }

        $f3->set( 'post', [
            'id' => $post_mapper->id,
            'title' => $post_mapper->title,
            'body' => $post_mapper->body,
            'user_id' => $post_mapper->user_id,
            'is_published' => (bool)$post_mapper->published_at,
            'published_at' => $post_mapper->published_at,
            'created_at' => $post_mapper->created_at,
            'updated_at' => $post_mapper->updated_at
        ] );

        echo \Template::instance()->render( 'templates/admin_post_edit.html' );
    }

    public function post_delete( \Base $f3, array $params ) {
        if ( ! isset( $params['id'] ) ) {
            var_dump( $params );
            $f3->reroute( '/admin/posts' );
            exit;
        }

        try {
            $post_mapper = new Post( $f3->get( 'DB' ) );
            $post_mapper->delete_post( (int)$params['id'] );
        } catch (Exception $e) {
            $f3->set( 'error_msg', 'Error deleting post: ' . $e->getMessage() );
            echo \Template::instance()->render( 'templates/admin_posts.html' );
            return;
        }

        // redirect to posts list
        $f3->reroute( '/admin/posts' );
        exit;
    }

    public function banners( \Base $f3 ) {
        $banner_mapper = new Banner( $f3->get( 'DB' ) );
        $f3->set( 'banners', $banner_mapper->get_banner_list() );
        echo \Template::instance()->render( 'templates/admin_banners.html' );
    }

    public function banner_create( \Base $f3 ) {
        if ( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $banner_data = $this->get_banner_form_data( $f3 );

            if ( isset( $banner_data['error'] ) ) {
                $f3->set( 'error_msg', $banner_data['error'] );
                $f3->set( 'banner', $banner_data );
                echo \Template::instance()->render( 'templates/admin_banner_create.html' );
                return;
            }

            $banner_mapper = new Banner( $f3->get( 'DB' ) );
            $banner_mapper->create_banner(
                $banner_data['body'],
                $banner_data['dismissible'],
                $banner_data['published_at'],
                $banner_data['expires_at']
            );

            $f3->reroute( '/admin/banners' );
            exit;
        }

        echo \Template::instance()->render( 'templates/admin_banner_create.html' );
    }

    public function banner_edit( \Base $f3, array $params ) {
        if ( !isset( $params['id'] ) ) {
            $f3->reroute( '/admin/banners' );
            exit;
        }

        $banner_mapper = new Banner( $f3->get( 'DB' ) );
        $banner_mapper->load( ['id = ?', (int)$params['id']] );

        if ( $banner_mapper->dry() ) {
            $f3->error( 404 );
            return;
        }

        if ( $f3->get( 'SERVER.REQUEST_METHOD' ) === 'POST' ) {
            $banner_data = $this->get_banner_form_data( $f3 );

            if ( isset( $banner_data['error'] ) ) {
                $banner_data['id'] = (int)$params['id'];
                $f3->set( 'error_msg', $banner_data['error'] );
                $f3->set( 'banner', $banner_data );
                echo \Template::instance()->render( 'templates/admin_banner_edit.html' );
                return;
            }

            $banner_mapper->body = $banner_data['body'];
            $banner_mapper->dismissible = $banner_data['dismissible'];
            $banner_mapper->published_at = $banner_data['published_at'];
            $banner_mapper->expires_at = $banner_data['expires_at'];
            $banner_mapper->updated_at = date( 'Y-m-d H:i:s' );
            $banner_mapper->save();

            $f3->reroute( '/admin/banners' );
            exit;
        }

        $f3->set( 'banner', [
            'id' => (int)$banner_mapper->id,
            'body' => $banner_mapper->body,
            'dismissible' => (bool)$banner_mapper->dismissible,
            'is_published' => (bool)$banner_mapper->published_at,
            'published_at' => $banner_mapper->published_at,
            'expires_at' => $banner_mapper->expires_at
        ] );
        echo \Template::instance()->render( 'templates/admin_banner_edit.html' );
    }

    public function banner_delete( \Base $f3, array $params ) {
        if ( !isset( $params['id'] ) ) {
            $f3->reroute( '/admin/banners' );
            exit;
        }

        try {
            $banner_mapper = new Banner( $f3->get( 'DB' ) );
            $banner_mapper->delete_banner( (int)$params['id'] );
        } catch ( Exception $e ) {
            $f3->set( 'error_msg', 'Error deleting banner: ' . $e->getMessage() );
            $this->banners( $f3 );
            return;
        }

        $f3->reroute( '/admin/banners' );
        exit;
    }

    private function get_banner_form_data( \Base $f3 ) {
        $body = trim( (string)$f3->get( 'POST.body' ) );
        $dismissible = (bool)$f3->get( 'POST.dismissible' );
        $is_published = (bool)$f3->get( 'POST.is_published' );
        $published_input = trim( (string)$f3->get( 'POST.published_at' ) );
        $expires_input = trim( (string)$f3->get( 'POST.expires_at' ) );
        $published_at = $is_published ? $this->normalize_admin_date( $published_input ) : null;
        $expires_at = $expires_input ? $this->normalize_admin_date( $expires_input ) : null;

        $data = [
            'body' => $body,
            'dismissible' => $dismissible,
            'is_published' => $is_published,
            'published_at' => $published_at,
            'published_input' => $published_input,
            'expires_at' => $expires_at,
            'expires_input' => $expires_input
        ];

        if ( !$body ) {
            $data['error'] = 'Content is required.';
        } elseif ( $is_published && !$published_at ) {
            $data['error'] = 'A valid publish date is required.';
        } elseif ( $expires_input && !$expires_at ) {
            $data['error'] = 'Expiration date must be valid.';
        } elseif ( $published_at && $expires_at && $expires_at <= $published_at ) {
            $data['error'] = 'Expiration date must be later than the publish date.';
        }

        return $data;
    }

    private function get_post_form_data( \Base $f3 ) {
        $title = (string)$f3->get( 'POST.title' );
        $body = (string)$f3->get( 'POST.body' );
        $is_published = (bool)$f3->get( 'POST.is_published' );
        $published_input = trim( (string)$f3->get( 'POST.published_at' ) );
        $published_at = $is_published ? $this->normalize_admin_date( $published_input ) : null;

        $data = [
            'title' => $title,
            'body' => $body,
            'is_published' => $is_published,
            'published_at' => $published_at,
            'published_input' => $published_input
        ];

        if ( !trim( $title ) || !trim( $body ) ) {
            $data['error'] = 'Title and body are required.';
        } elseif ( $is_published && !$published_at ) {
            $data['error'] = 'A valid publish date is required.';
        }

        return $data;
    }

    private function normalize_admin_date( string $value ) {
        if ( !$value ) {
            return null;
        }

        $date = \DateTime::createFromFormat( '!Y-m-d\TH:i', $value );

        if ( !$date || $date->format( 'Y-m-d\TH:i' ) !== $value ) {
            return null;
        }

        return $date->format( 'Y-m-d H:i:s' );
    }

    public function users( \Base $f3 ) {
        $db = $f3->get( 'DB' );
        $users = $db->exec( 'SELECT id, email, confirmed, is_admin FROM user ORDER BY email' );
        $f3->set( 'users', $users );
        echo \Template::instance()->render( 'templates/admin_users.html' );
    }

    public function restore_sheet( \Base $f3 ) {
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
            $id_or_false = $sheet->create_sheet_with_data( $name, $email, $sheet_data, $is_2024 );

            if( !$id_or_false ) {
                $f3->set( 'error', 'Unable to restore sheet.' );
            }

            echo \Template::instance()->render( 'templates/admin_restore_sheet.html' );
            return;
        }

        // else, show restore form
        echo \Template::instance()->render( 'templates/admin_restore_sheet.html' );
    }

    public function stats( \Base $f3 ) {
        $db = $f3->get( 'DB' );
        $stats = [];
        $add_stat = function( $label, $value ) use ( &$stats ) {
            $stats[] = [
                'label' => $label,
                'value' => $value
            ];
        };

        // total users
        $total_users = $db->exec( 'SELECT COUNT(*) AS count FROM user' )[0]['count'];
        $add_stat( 'Total users', $total_users );

        // total sheets
        $total_sheets = $db->exec( 'SELECT COUNT(*) AS count FROM sheet' )[0]['count'];
        $add_stat( 'Total sheets', $total_sheets );

        // first-pass growth and engagement stats
        $new_users_30_days = $db->exec(
            "SELECT COUNT(*) AS count FROM user WHERE created_at >= datetime('now', '-30 days')"
        )[0]['count'];
        $add_stat( 'New users (30 days)', $new_users_30_days );

        $new_sheets_30_days = $db->exec(
            "SELECT COUNT(*) AS count FROM sheet WHERE created_at >= datetime('now', '-30 days')"
        )[0]['count'];
        $add_stat( 'New sheets (30 days)', $new_sheets_30_days );

        $confirmed_users = $db->exec( 'SELECT COUNT(*) AS count FROM user WHERE confirmed = 1' )[0]['count'];
        $confirmed_user_rate = $total_users > 0
            ? round( ( $confirmed_users / $total_users ) * 100, 1 ) . '%'
            : '0%';
        $add_stat( 'Confirmed user rate', $confirmed_user_rate );

        $sheets_2024 = $db->exec( 'SELECT COUNT(*) AS count FROM sheet WHERE is_2024 = 1' )[0]['count'];
        $rules_2024_adoption_rate = $total_sheets > 0
            ? round( ( $sheets_2024 / $total_sheets ) * 100, 1 ) . '%'
            : '0%';
        $add_stat( '2024 rules adoption rate', $rules_2024_adoption_rate );

        $activated_users = $db->exec(
            'SELECT COUNT(*) AS count FROM user u WHERE EXISTS (SELECT 1 FROM sheet s WHERE s.email = u.email)'
        )[0]['count'];
        $activation_rate = $total_users > 0
            ? round( ( $activated_users / $total_users ) * 100, 1 ) . '%'
            : '0%';
        $add_stat( 'Activation rate (users with at least one sheet)', $activation_rate );

        // sheet activity stats (immediately useful when only sheet.updated_at is reliable)
        $daily_activity = $db->exec(
            "SELECT COUNT(*) AS sheets, COUNT(DISTINCT email) AS users FROM sheet WHERE updated_at >= datetime('now', '-1 day')"
        )[0];
        $add_stat( 'Daily active users', $daily_activity['users'] );
        $add_stat( 'Daily active sheets', $daily_activity['sheets'] );

        $weekly_activity = $db->exec(
            "SELECT COUNT(*) AS sheets, COUNT(DISTINCT email) AS users FROM sheet WHERE updated_at >= datetime('now', '-7 days')"
        )[0];
        $add_stat( 'Weekly active users', $weekly_activity['users'] );
        $add_stat( 'Weekly active sheets', $weekly_activity['sheets'] );

        $monthly_activity = $db->exec(
            "SELECT COUNT(*) AS sheets, COUNT(DISTINCT email) AS users FROM sheet WHERE updated_at >= datetime('now', '-30 days')"
        )[0];
        $add_stat( 'Monthly active users', $monthly_activity['users'] );
        $add_stat( 'Monthly active sheets', $monthly_activity['sheets'] );

        $add_stat( 'Recently active sheet creators (30 days)', $monthly_activity['users'] );

        $dormant_sheets = $db->exec(
            "SELECT COUNT(*) AS count FROM sheet WHERE updated_at IS NULL OR updated_at < datetime('now', '-90 days')"
        )[0]['count'];
        $add_stat( 'Dormant sheets (no updates in 90+ days)', $dormant_sheets );

        $dormant_sheet_rate = $total_sheets > 0
            ? round( ( $dormant_sheets / $total_sheets ) * 100, 1 ) . '%'
            : '0%';
        $add_stat( 'Dormant sheet rate', $dormant_sheet_rate );

        $top_sheet_creator = $db->exec(
            'SELECT email, COUNT(*) AS count FROM sheet GROUP BY email ORDER BY count DESC, email ASC LIMIT 1'
        );
        $top_sheet_creator_stat = count( $top_sheet_creator ) > 0
            ? $top_sheet_creator[0]['email'] . ' (' . $top_sheet_creator[0]['count'] . ')'
            : 'N/A';
        $add_stat( 'User with most sheets', $top_sheet_creator_stat );

        $users_with_more_than_five_sheets = $db->exec(
            'SELECT COUNT(*) AS count FROM (SELECT email FROM sheet GROUP BY email HAVING COUNT(*) > 5)'
        )[0]['count'];
        $add_stat( 'Users with more than 5 sheets', $users_with_more_than_five_sheets );

        $f3->set( 'stats', $stats );
        echo \Template::instance()->render( 'templates/admin_stats.html' );
    }

  }
