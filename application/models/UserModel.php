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

        $this->db->insert('user', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when creating user'
            ];
        }
            
        return [
            'status' => true,
            'message' => 'User created successfully'
        ];
    }

    public function authenticateUser($email, $password)
    {
        $this->db->limit(1);
        $this->db->select('user_id, password');
        $this->db->where('email', $email);
        $query = $this->db->get('user');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when authenticating user'
            ];
        }

        if ($query->num_rows() === 0) {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }

        $user = $query->row();
        if (password_verify($password, $user->password)) {
            $this->session->authenticated = true;
            $this->session->userId = $user->user_id;
            
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

    public function getUser($userId)
    {
        $this->db->limit(1);
        $this->db->select('username, email, created_at, points');
        $this->db->where('user_id', $userId);
        $query = $this->db->get('user');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when retrieving user'
            ];
        }

        if ($query->num_rows() === 0) {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }

        $user = $query->row();

        $username = explode(' ', $user->username);
        $userData['firstName'] = isset($username[0]) ? $username[0] : '';
        $userData['lastName'] = isset($username[1]) ? $username[1] : '';
        $userData['email'] = $user->email;
        $userData['joinDate'] = date("F j, Y", strtotime($user->created_at));
        $userData['points'] = $user->points;

        return [
            'status' => true,
            'data' => $userData
        ];
    }

    public function updateUser($userId, $data)
    {
        if ($data['password']) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $this->db->where('user_id', $userId);
        $this->db->update('user', $data);
        $error = $this->db->error();

        if (!empty($error['message'])) {
            if ($error['code'] == 1062) {
                return [
                    'status' => false,
                    'message' => 'The email address already exists'
                ];
            } else {
                log_message('error', 'Database error: ' . $error['message']);
            
                return [
                    'status' => false,
                    'message' => 'An error occurred when updating user'
                ];
            }
        }

        if ($this->db->affected_rows() === 1) {
            return [
                'status' => true,
                'message' => 'User updated successfully'
            ];
        } else {
            return [
                'status' => false,
                'message' => 'No changes were made to the user'
            ];
        }
    }

    public function deleteUser($userId)
    {
        $this->db->where('user_id', $userId);
        $this->db->delete('user');
        $error = $this->db->error();

        if (!empty($error['message'])) {
            log_message('error', 'Database error: ' . $error['message']);
            
            return [
                'status' => false,
                'message' => 'An error occurred when deleting user'
            ];
        }

        if ($this->db->affected_rows() === 1) {
            $this->session->sess_destroy();
            
            return [
                'status' => true,
                'message' => 'User deleted successfully'
            ];
        }
    }
}
