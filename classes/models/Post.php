<?php

class Post extends \DB\SQL\Mapper {
    
    public $db;
    
    public function __construct( $db ) {
        $this->db = $db;
        parent::__construct( $db, 'post' );
    }

    public function get_post_list() {
        $this->load(null, ['order' => 'created_at DESC']);
        
        if ($this->dry()) {
            return [];
        }
        
        $posts = [];

        while (!$this->dry()) {
            $posts[] = [
                'id' => $this->id,
                'title' => $this->title,
                'user_id' => $this->user_id,
                'published_at' => $this->published_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
            $this->next();
        }

        return $posts;
    }
    
    public function get_full_posts( $published = true ) {
        $conditions = [];
        $parameters = [];

        if ($published) {
            $conditions[] = 'published_at IS NOT NULL AND published_at <= ?';
            $parameters[] = date( 'Y-m-d H:i:s' );
        }
        $filter = $conditions
            ? array_merge( [implode( ' AND ', $conditions )], $parameters )
            : null;
        $this->load( $filter, ['order' => 'published_at DESC, created_at DESC'] );

        if ($this->dry()) {
            return [];
        }
        
        $posts = [];

        $Parsedown = new Parsedown();

        while (!$this->dry()) {
            $posts[] = [
                'id' => $this->id,
                'title' => $this->title,
                'body' => $this->body,
                'html' => $Parsedown->text($this->body),
                'user_id' => $this->user_id,
                'published_at' => $this->published_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
            $this->next();
        }

        return $posts;
    }
    
    public function create_post(
        string $title,
        string $body,
        int $user_id,
        ?string $published_at = null
    ) {
        if (!$title || !$body || !$user_id) {
            throw new Exception('Title, body, and user required');
        }
        
        $this->title = $title;
        $this->body = $body;
        $this->user_id = $user_id;
        $this->published_at = $published_at;
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        $save_ok = $this->save();
        
        return $save_ok ? $this->id : false;
    }

    public function delete_post( int $post_id ) {
        if (!$post_id) {
            throw new Exception('Post ID required');
        }
        
        $this->load(['id = ?', $post_id]);
        
        if ($this->dry()) {
            throw new Exception('Post not found');
        }

        var_dump('Deleting post with ID: ' . $post_id);
        
        return $this->erase();
    }

}
