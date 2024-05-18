<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AnswerModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function createAnswer($data)
    {
        $this->db->insert('answer', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when creating answer'
            ];
        }
            
        return [
            'status' => true,
            'message' => 'Answer created successfully'
        ];
    }

    public function getAnswers($questionId)
    {
        $this->db->where('question_id', $questionId);
        
        $query = $this->db->get('answer');

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when retrieving answers'
            ];
        }

        return [
            'status' => true,
            'data' => $query->result_array()
        ];
    }

    public function updateAnswer($answerId, $data)
    {
        $this->db->where('answer_id', $answerId);
        $this->db->update('answer', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when updating answer'
            ];
        }

        if ($this->db->affected_rows() === 1) {
            return [
                'status' => true,
                'message' => 'Answer updated successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'No changes were made to the answer'
            ];
        }
    }

    public function deleteAnswer($answerId)
    {
        $this->db->where('answer_id', $answerId);
        $this->db->delete('answer');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when deleting answer',
                'code' => 500
            ];
        }

        if ($this->db->affected_rows() === 1) {
            return [
                'status' => true,
                'message' => 'Answer deleted successfully',
                'code' => 200
            ];
        }
    }

    public function isAnswerOwner($userId, $answerId)
    {
        $this->db->limit(1);
        $this->db->select('user_id');
        $this->db->where('answer_id', $answerId);
        $query = $this->db->get('answer');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when validating answer ownership'
            ];
        }

        if ($query->num_rows() === 0) {
            return [
                'status' => false,
                'message' => 'Answer not found'
            ];
        }

        $answer = $query->row();

        if ($answer->user_id === $userId) {
            return [
                'status' => true
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Answer not owned by user'
            ];
        }
    }
}
