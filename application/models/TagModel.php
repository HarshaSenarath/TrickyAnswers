<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TagModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function createTag()
    {
        
    }

    public function getTags($questionId)
    {
        $this->db->where('question_id', $questionId);
        
        $query = $this->db->get('question_tag');

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when retrieving tags'
            ];
        }

        return [
            'status' => true,
            'data' => $query->result_array()
        ];
    }

    public function updateTag($answerId, $data)
    {
        
    }

    public function deleteTag($answerId)
    {
        
    }
}
