<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class QuestionModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function createQuestion($data)
    {
        $this->db->insert('question', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when creating question'
            ];
        }
            
        return [
            'status' => true,
            'message' => 'Question created successfully'
        ];
    }

    public function getQuestions($userId)
    {
        if ($userId !== null) {
            $this->db->where('user_id', $userId);
        }
        
        $query = $this->db->get('question');

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when retrieving question'
            ];
        }

        if ($query->num_rows() === 0) {
            return [
                'status' => false,
                'message' => 'No questions found'
            ];
        }

        return [
            'status' => true,
            'data' => $query->result_array()
        ];
    }

    public function updateQuestion($questionId, $data)
    {
        $this->db->where('question_id', $questionId);
        $this->db->update('question', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when updating question'
            ];
        }

        if ($this->db->affected_rows() === 1) {
            return [
                'status' => true,
                'message' => 'Question updated successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'No changes were made to the question'
            ];
        }
    }

    public function deleteQuestion($questionId)
    {
        $this->db->where('question_id', $questionId);
        $this->db->delete('question');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when deleting question',
                'code' => 500
            ];
        }

        if ($this->db->affected_rows() === 1) {
            return [
                'status' => true,
                'message' => 'Question deleted successfully',
                'code' => 200
            ];
        }
    }

    public function isQuestionOwner($userId, $questionId)
    {
        $this->db->limit(1);
        $this->db->select('user_id');
        $this->db->where('question_id', $questionId);
        $query = $this->db->get('question');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when validating question ownership'
            ];
        }

        if ($query->num_rows() === 0) {
            return [
                'status' => false,
                'message' => 'Question not found'
            ];
        }

        $question = $query->row();

        if ($question->user_id === $userId) {
            return [
                'status' => true
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Question not owned by user'
            ];
        }
    }
}
