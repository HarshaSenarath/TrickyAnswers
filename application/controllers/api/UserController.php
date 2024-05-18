<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class UserController extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usermodel');
        $this->load->model('authmodel');
    }

    public function users_get()
    {
        try {
            $userId = $this->get('userId');

            if(!$userId) {
                $userId = $this->session->userId;
            }

            $result = $this->usermodel->getUser($userId);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $result['code']);
            }

            $response = [
                'status' => 'success',
                'data' => $result['data']
            ];
            $this->response($response, $result['code']);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function users_put()
    {
        try {
            $isAuthorized = $this->authmodel->isAuthenticated();

            if (!$isAuthorized['status']) {
                $response = [
                    'status' => 'error',
                    'message' => 'Authorization required'
                ];
                $this->response($response, 401);
            }

            $this->form_validation->set_data($this->put());
            $this->form_validation->set_rules('firstName', 'First Name', 'trim|required|alpha');
            $this->form_validation->set_rules('lastName', 'Last Name', 'trim|required|alpha');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            if ($this->form_validation->run() === false) {
                $errors = $this->form_validation->error_array();
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
                $this->response($response, 400);
            }

            $userId = $this->session->userId;

            $firstName = $this->put('firstName');
            $lastName = $this->put('lastName');
            $username = $firstName . ' ' . $lastName;

            $data = [
                'username' => $username,
                'email' => $this->put('email')
            ];

            $result = $this->usermodel->updateUser($userId, $data);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $result['code']);
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, $result['code']);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function users_post()
    {
        try {
            $isAuthorized = $this->authmodel->isAuthenticated();

            if (!$isAuthorized['status']) {
                $response = [
                    'status' => 'error',
                    'message' => 'Authorization required'
                ];
                $this->response($response, 401);
            }

            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('oldPassword', 'Old Password', 'trim|required');
            $this->form_validation->set_rules('newPassword', 'New Password', 'trim|required');
            $this->form_validation->set_rules('confirmPassword', 'Confirm Password', 'trim|required|matches[newPassword]');

            if ($this->form_validation->run() === false) {
                $errors = $this->form_validation->error_array();
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
                $this->response($response, 400);
            }

            $userId = $this->session->userId;
            
            $isVerified = $this->authmodel->verifyPassword($userId, $this->post('oldPassword'));

            if (!$isVerified['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $isVerified['message']
                ];
                $this->response($response, $isVerified['code']);
            }

            $data = [
                'password' => $this->post('newPassword')
            ];

            $result = $this->usermodel->updateUser($userId, $data);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $result['code']);
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, $result['code']);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function users_delete()
    {
        try {
            $isAuthorized = $this->authmodel->isAuthenticated();

            if (!$isAuthorized['status']) {
                $response = [
                    'status' => 'error',
                    'message' => 'Authorization required'
                ];
                $this->response($response, 401);
            }

            $userId = $this->session->userId;

            $result = $this->usermodel->deleteUser($userId);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $result['code']);
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, $result['code']);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }
}
