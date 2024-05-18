<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model {
    public function __construct()
    {
        parent::__construct();
    }

    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        try {
            $query = $this->db->insert('user', $data);
        } catch (Exception $e) {
            log_message('error', 'Database error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Internal server error'
            ];
        }

        if ($query) {
            return [
                'status' => true,
                'message' => 'User created successfully'
            ];
        } else {
            log_message('error', 'Failed to create user: ' . $this->db->error()['message']);
            
            return [
                'status' => false,
                'message' => 'Failed to create user'
            ];
        }
    }

    public function authenticateUser($email, $password)
    {
        try {
            $this->db->limit(1);
            $query = $this->db->get_where('user', array('email' => $email));
        } catch (Exception $e) {
            log_message('error', 'Database error: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Internal server error'
            ];
        }

        if ($query->num_rows() !== 1) {
            return [
                'status' => false,
                'message' => 'Failed to autheticate user'
            ];
        }

        $user = $query->row();
        if (password_verify($password, $user->password)) {
            $this->session->is_logged_in = true;
            $this->session->user_id = $user->user_id;
            
            return [
                'status' => true,
                'message' => 'User authenticated successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'Invalid email or password'
            ];
        }
    }
}
