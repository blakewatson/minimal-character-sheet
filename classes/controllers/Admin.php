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

    public function stats( $f3 ) {
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
        $daily_active_sheets = $db->exec(
            "SELECT COUNT(*) AS count FROM sheet WHERE updated_at >= datetime('now', '-1 day')"
        )[0]['count'];
        $add_stat( 'Daily active sheets', $daily_active_sheets );

        $weekly_active_sheets = $db->exec(
            "SELECT COUNT(*) AS count FROM sheet WHERE updated_at >= datetime('now', '-7 days')"
        )[0]['count'];
        $add_stat( 'Weekly active sheets', $weekly_active_sheets );

        $monthly_active_sheets = $db->exec(
            "SELECT COUNT(*) AS count FROM sheet WHERE updated_at >= datetime('now', '-30 days')"
        )[0]['count'];
        $add_stat( 'Monthly active sheets', $monthly_active_sheets );

        $recently_active_sheet_creators = $db->exec(
            "SELECT COUNT(DISTINCT email) AS count FROM sheet WHERE updated_at >= datetime('now', '-30 days')"
        )[0]['count'];
        $add_stat( 'Recently active sheet creators (30 days)', $recently_active_sheet_creators );

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