<?php

class EmailNormalizationPlan {

    private $db;
    private $user_columns;

    public function __construct( $db ) {
        $this->db = $db;
        $this->user_columns = null;
    }

    public static function build( $db ) {
        $planner = new self( $db );
        return $planner->build_plan();
    }

    public function build_plan() {
        $groups = [];
        $risk_groups = [];

        foreach( $this->load_duplicate_groups() as $duplicate_group ) {
            $group = $this->build_group_plan( $duplicate_group['normalized_email'] );
            $groups[] = $group;

            if( ! empty( $group['risk_reasons'] ) ) {
                $risk_groups[] = [
                    'normalized_email' => $group['normalized_email'],
                    'canonical_user_id' => $group['canonical_user_id'],
                    'risk_reasons' => $group['risk_reasons']
                ];
            }
        }

        $duplicate_normalized_emails = array_column( $groups, 'normalized_email' );
        $remaining_normalizations = $this->build_remaining_normalization_plan( $duplicate_normalized_emails );

        return [
            'generated_at' => date( 'Y-m-d H:i:s' ),
            'groups' => $groups,
            'risk_groups' => $risk_groups,
            'remaining_normalizations' => $remaining_normalizations,
            'totals' => [
                'duplicate_groups' => count( $groups ),
                'duplicate_users' => array_sum( array_map( function( $group ) {
                    return count( $group['duplicate_user_ids'] );
                }, $groups ) ),
                'risky_groups' => count( $risk_groups ),
                'sheet_reassignments' => array_sum( array_column( $groups, 'sheet_reassignment_count' ) ),
                'remaining_user_email_normalizations' => count( $remaining_normalizations['users'] ),
                'remaining_sheet_email_normalizations' => array_sum( array_column( $remaining_normalizations['sheets'], 'sheet_count' ) )
            ]
        ];
    }

    private function build_group_plan( $normalized_email ) {
        $users = $this->load_users_for_group( $normalized_email );
        usort( $users, [ $this, 'compare_canonical_users' ] );

        $canonical_user = $users[0];
        $duplicate_user_ids = [];

        foreach( array_slice( $users, 1 ) as $user ) {
            $duplicate_user_ids[] = (int) $user['id'];
        }

        $sheet_variants = $this->load_sheet_variants_for_group( $normalized_email );
        $email_variants = $this->merge_email_variants( $users, $sheet_variants );

        $risk_reasons = $this->detect_risk_reasons( $users );

        return [
            'normalized_email' => $normalized_email,
            'final_email' => $normalized_email,
            'canonical_user_id' => (int) $canonical_user['id'],
            'canonical_user_email' => $canonical_user['email'],
            'duplicate_user_ids' => $duplicate_user_ids,
            'email_variants' => $email_variants,
            'users' => $users,
            'merged_metadata' => $this->build_merged_metadata( $users ),
            'sheet_reassignment_count' => array_sum( array_column( $sheet_variants, 'sheet_count' ) ),
            'sheet_variants' => $sheet_variants,
            'risk_reasons' => $risk_reasons
        ];
    }

    private function load_duplicate_groups() {
        return $this->db->exec(
            'SELECT lower(trim(email)) AS normalized_email, COUNT(*) AS user_count
             FROM "user"
             GROUP BY lower(trim(email))
             HAVING COUNT(*) > 1
             ORDER BY user_count DESC, normalized_email ASC'
        );
    }

    private function load_users_for_group( $normalized_email ) {
        $has_is_admin = $this->user_has_column( 'is_admin' );
        $has_created_at = $this->user_has_column( 'created_at' );
        $has_updated_at = $this->user_has_column( 'updated_at' );

        $is_admin_select = $has_is_admin ? 'u.is_admin' : '0 AS is_admin';
        $created_at_select = $has_created_at ? 'u.created_at' : 'NULL AS created_at';
        $updated_at_select = $has_updated_at ? 'u.updated_at' : 'NULL AS updated_at';

        $users = $this->db->exec(
            "SELECT
                u.id,
                u.email,
                u.confirmed,
                {$is_admin_select},
                {$created_at_select},
                {$updated_at_select},
                COUNT(s.id) AS sheet_count
             FROM \"user\" u
             LEFT JOIN sheet s ON s.email = u.email
             WHERE lower(trim(u.email)) = ?
             GROUP BY u.id, u.email
             ORDER BY u.id ASC",
            [ $normalized_email ]
        );

        return array_map( [ $this, 'normalize_user_row' ], $users );
    }

    private function load_sheet_variants_for_group( $normalized_email ) {
        $variants = $this->db->exec(
            'SELECT email, COUNT(*) AS sheet_count
             FROM sheet
             WHERE lower(trim(email)) = ?
             GROUP BY email
             ORDER BY email ASC',
            [ $normalized_email ]
        );

        return array_map( function( $variant ) {
            return [
                'email' => $variant['email'],
                'sheet_count' => (int) $variant['sheet_count']
            ];
        }, $variants );
    }

    private function build_remaining_normalization_plan( $duplicate_normalized_emails ) {
        return [
            'users' => $this->load_remaining_user_normalizations( $duplicate_normalized_emails ),
            'sheets' => $this->load_remaining_sheet_normalizations( $duplicate_normalized_emails )
        ];
    }

    private function load_remaining_user_normalizations( $duplicate_normalized_emails ) {
        $rows = $this->db->exec(
            'SELECT id, email, lower(trim(email)) AS normalized_email
             FROM "user"
             WHERE email != lower(trim(email))
             ORDER BY normalized_email ASC, id ASC'
        );

        return array_values( array_filter( array_map( function( $row ) use ( $duplicate_normalized_emails ) {
            if( in_array( $row['normalized_email'], $duplicate_normalized_emails ) ) return null;

            return [
                'id' => (int) $row['id'],
                'email' => $row['email'],
                'normalized_email' => $row['normalized_email']
            ];
        }, $rows ) ) );
    }

    private function load_remaining_sheet_normalizations( $duplicate_normalized_emails ) {
        $rows = $this->db->exec(
            'SELECT email, lower(trim(email)) AS normalized_email, COUNT(*) AS sheet_count
             FROM sheet
             WHERE email != lower(trim(email))
             GROUP BY email
             ORDER BY normalized_email ASC, email ASC'
        );

        return array_values( array_filter( array_map( function( $row ) use ( $duplicate_normalized_emails ) {
            if( in_array( $row['normalized_email'], $duplicate_normalized_emails ) ) return null;

            return [
                'email' => $row['email'],
                'normalized_email' => $row['normalized_email'],
                'sheet_count' => (int) $row['sheet_count']
            ];
        }, $rows ) ) );
    }

    private function normalize_user_row( $user ) {
        return [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'normalized_email' => self::normalize_email( $user['email'] ),
            'confirmed' => (int) ( $user['confirmed'] ?? 0 ),
            'is_admin' => (int) ( $user['is_admin'] ?? 0 ),
            'created_at' => $user['created_at'] ?? null,
            'updated_at' => $user['updated_at'] ?? null,
            'sheet_count' => (int) ( $user['sheet_count'] ?? 0 )
        ];
    }

    private function compare_canonical_users( $a, $b ) {
        $checks = [
            [ 'is_admin', 'desc' ],
            [ 'confirmed', 'desc' ],
            [ 'sheet_count', 'desc' ]
        ];

        foreach( $checks as $check ) {
            [ $field, $direction ] = $check;
            if( $a[$field] === $b[$field] ) continue;
            return $direction === 'desc' ? $b[$field] <=> $a[$field] : $a[$field] <=> $b[$field];
        }

        $updated_at_compare = $this->compare_timestamps_desc( $a['updated_at'], $b['updated_at'] );
        if( $updated_at_compare !== 0 ) return $updated_at_compare;

        return $a['id'] <=> $b['id'];
    }

    private function compare_timestamps_desc( $a, $b ) {
        if( $a === $b ) return 0;
        if( empty( $a ) ) return 1;
        if( empty( $b ) ) return -1;

        $a_time = strtotime( $a );
        $b_time = strtotime( $b );

        if( $a_time === $b_time ) return 0;
        if( $a_time === false ) return 1;
        if( $b_time === false ) return -1;

        return $b_time <=> $a_time;
    }

    private function build_merged_metadata( $users ) {
        return [
            'email' => $users[0]['normalized_email'],
            'confirmed' => $this->max_value( $users, 'confirmed' ),
            'is_admin' => $this->max_value( $users, 'is_admin' ),
            'created_at' => $this->min_timestamp( $users, 'created_at' ),
            'updated_at' => $this->max_timestamp( $users, 'updated_at' )
        ];
    }

    private function detect_risk_reasons( $users ) {
        $risk_reasons = [];
        $confirmed_users_with_sheets = 0;
        $admin_users = 0;

        foreach( $users as $user ) {
            if( (int) $user['confirmed'] === 1 && (int) $user['sheet_count'] > 0 ) {
                $confirmed_users_with_sheets++;
            }

            if( (int) $user['is_admin'] === 1 ) {
                $admin_users++;
            }
        }

        if( $confirmed_users_with_sheets > 1 ) {
            $risk_reasons[] = 'multiple_confirmed_users_with_sheets';
        }

        if( $admin_users > 1 ) {
            $risk_reasons[] = 'multiple_admin_users';
        }

        return $risk_reasons;
    }

    private function merge_email_variants( $users, $sheet_variants ) {
        $email_variants = [];

        foreach( $users as $user ) {
            $email_variants[$user['email']] = true;
        }

        foreach( $sheet_variants as $variant ) {
            $email_variants[$variant['email']] = true;
        }

        $email_variants = array_keys( $email_variants );
        sort( $email_variants );

        return $email_variants;
    }

    private function max_value( $rows, $field ) {
        return max( array_map( function( $row ) use ( $field ) {
            return (int) ( $row[$field] ?? 0 );
        }, $rows ) );
    }

    private function min_timestamp( $rows, $field ) {
        $timestamps = $this->non_empty_values( $rows, $field );
        if( empty( $timestamps ) ) return null;

        usort( $timestamps, function( $a, $b ) {
            return strtotime( $a ) <=> strtotime( $b );
        } );

        return $timestamps[0];
    }

    private function max_timestamp( $rows, $field ) {
        $timestamps = $this->non_empty_values( $rows, $field );
        if( empty( $timestamps ) ) return null;

        usort( $timestamps, function( $a, $b ) {
            return strtotime( $b ) <=> strtotime( $a );
        } );

        return $timestamps[0];
    }

    private function non_empty_values( $rows, $field ) {
        return array_values( array_filter( array_map( function( $row ) use ( $field ) {
            return $row[$field] ?? null;
        }, $rows ), function( $value ) {
            return ! empty( $value );
        } ) );
    }

    private function user_has_column( $column ) {
        if( $this->user_columns === null ) {
            $this->user_columns = array_column( $this->db->exec( 'PRAGMA table_info("user")' ), 'name' );
        }

        return in_array( $column, $this->user_columns );
    }

    public static function normalize_email( $email ) {
        return strtolower( trim( $email ?? '' ) );
    }

}
