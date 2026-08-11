<?php

class Banner extends \DB\SQL\Mapper {

    public $db;

    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'banner' );
    }

    public function get_banner_list() {
        $this->load( null, ['order' => 'created_at DESC'] );

        if ( $this->dry() ) {
            return [];
        }

        $banners = [];
        $now = time();

        while ( !$this->dry() ) {
            $body = trim( preg_replace( '/\s+/', ' ', $this->body ) );
            $excerpt = strlen( $body ) > 120 ? substr( $body, 0, 117 ) . '...' : $body;
            $published_at = $this->published_at;
            $expires_at = $this->expires_at;

            if ( !$published_at ) {
                $status = 'Draft';
            } elseif ( strtotime( $published_at ) > $now ) {
                $status = 'Scheduled';
            } elseif ( $expires_at && strtotime( $expires_at ) <= $now ) {
                $status = 'Expired';
            } else {
                $status = 'Active';
            }

            $banners[] = [
                'id' => $this->id,
                'body' => $this->body,
                'excerpt' => $excerpt,
                'dismissible' => (bool)$this->dismissible,
                'status' => $status,
                'published_at' => $published_at,
                'expires_at' => $expires_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
            $this->next();
        }

        return $banners;
    }

    public function get_active_banners( array $dismissed_ids = [] ) {
        $now = date( 'Y-m-d H:i:s' );
        $this->load(
            [
                'published_at IS NOT NULL AND published_at <= ? AND (expires_at IS NULL OR expires_at > ?)',
                $now,
                $now
            ],
            ['order' => 'dismissible ASC, published_at DESC']
        );

        if ( $this->dry() ) {
            return [];
        }

        $banners = [];
        $parsedown = new Parsedown();

        while ( !$this->dry() ) {
            $id = (int)$this->id;
            $dismissible = (bool)$this->dismissible;

            if ( !$dismissible || !in_array( $id, $dismissed_ids, true ) ) {
                $banners[] = [
                    'id' => $id,
                    'html' => $parsedown->text( $this->body ),
                    'dismissible' => $dismissible,
                    'cookie_expires' => $this->expires_at
                        ? gmdate( 'D, d M Y H:i:s', strtotime( $this->expires_at ) ) . ' GMT'
                        : gmdate( 'D, d M Y H:i:s', time() + 60 * 60 * 24 * 365 ) . ' GMT'
                ];
            }

            $this->next();
        }

        return $banners;
    }

    public function create_banner(
        string $body,
        bool $dismissible,
        ?string $published_at,
        ?string $expires_at
    ) {
        if ( !$body ) {
            throw new Exception( 'Banner content required' );
        }

        $now = date( 'Y-m-d H:i:s' );
        $this->body = $body;
        $this->dismissible = $dismissible;
        $this->published_at = $published_at;
        $this->expires_at = $expires_at;
        $this->created_at = $now;
        $this->updated_at = $now;

        $save_ok = $this->save();

        return $save_ok ? $this->id : false;
    }

    public function delete_banner( int $banner_id ) {
        if ( !$banner_id ) {
            throw new Exception( 'Banner ID required' );
        }

        $this->load( ['id = ?', $banner_id] );

        if ( $this->dry() ) {
            throw new Exception( 'Banner not found' );
        }

        return $this->erase();
    }
}
