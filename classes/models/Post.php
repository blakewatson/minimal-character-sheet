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
                'is_maintenance_message' => (bool)$this->is_maintenance_message,
                'published_at' => $this->published_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ];
            $this->next();
        }

        return $posts;
    }
    
    public function get_full_posts( $published = true, $maintenance = false ) {
        $conditions = [];

        if ($published) {
            $conditions[] = 'published_at IS NOT NULL';
        }
        if (!$maintenance) {
            $conditions[] = 'is_maintenance_message = 0';
        }
        $this->load( implode( ' AND ', $conditions ), ['order' => 'created_at DESC'] );

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
                'is_maintenance_message' => (bool)$this->is_maintenance_message,
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
        bool $is_published = false,
        bool $is_maintenance_message = false
    ) {
        if (!$title || !$body || !$user_id) {
            throw new Exception('Title, body, and user required');
        }
        
        $this->title = $title;
        $this->body = $body;
        $this->user_id = $user_id;
        $this->is_maintenance_message = $is_maintenance_message;
        $this->published_at = $is_published ? date('Y-m-d H:i:s') : null;
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