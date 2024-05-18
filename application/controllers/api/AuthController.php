<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class AuthController extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usermodel');
        $this->load->model('authmodel');
    }

    public function register_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('firstName', 'First Name', 'trim|required|alpha');
            $this->form_validation->set_rules('lastName', 'Last Name', 'trim|required|alpha');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[user.email]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');
            $this->form_validation->set_rules('confirmPassword', 'Confirm Password', 'trim|required|matches[password]');

            if ($this->form_validation->run() === false) {
                $errors = $this->form_validation->error_array();
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
                $this->response($response, 400);
            }

            $firstName = $this->post('firstName');
            $lastName = $this->post('lastName');
            $username = $firstName . ' ' . $lastName;

            $data = [
                'username' => $username,
                'email' => $this->post('email'),
                'password' => $this->post('password')
            ];

            $result = $this->usermodel->createUser($data);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, 500);
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, 201);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }


    public function login_post()
    {
        try {
            $this->form_validation->set_data($this->post());
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'trim|required');

            if ($this->form_validation->run() === false) {
                $errors = $this->form_validation->error_array();
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors
                ];
                $this->response($response, 400);
                
            }

            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $result = $this->usermodel->authenticateUser($email, $password);

            if (!$result['status']) {
                $statusCode = 500;
                if ($result['message'] === 'User not found') {
                    $statusCode = 404;
                } elseif ($result['message'] === 'Invalid email or password') {
                    $statusCode = 401;
                }

                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $statusCode);
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, 200);
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function logout_get()
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

            $result = $this->authmodel->endSession();

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, 200);   
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function verify_get()
    {
        try {
            $result = $this->authmodel->isAuthenticated();

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, 401);             
            }

            $response = [
                'status' => 'success',
                'message' => $result['message']
            ];
            $this->response($response, 200);
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
