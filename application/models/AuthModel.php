<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function endSession()
    {
        $this->session->sess_destroy();

        return [
            'message' => 'Logged out successfully',
            'code' => 200
        ];
    }

    public function isAuthenticated()
    {
        if (isset($this->session->authenticated) && $this->session->authenticated == true) {
            return [
                'status' => true,
                'message' => 'User is logged in',
                'code' => 200
            ];
        }
            
        return [
            'status' => false,
            'message' => 'User is not logged in',
            'code' => 401
        ]; 
    }

    public function verifyPassword($userId, $oldPassword)
    {
        $this->db->limit(1);
        $this->db->select('password');
        $this->db->from('user');
        $this->db->where('user_id', $userId);
        $query = $this->db->get();
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when verifying old password',
                'code' => 500
            ];
        }

        $user = $query->row();

        if (password_verify($oldPassword, $user->password)) {
            return [
                'status' => true
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Incorrect old password',
                'code' => 401
            ];
        }
    }
}
