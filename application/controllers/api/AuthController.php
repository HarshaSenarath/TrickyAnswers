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
    }

    public function register_post()
    {
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
            return;
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
            return;
        }

        $response = [
            'status' => 'success',
            'message' => 'User created successfully'
        ];
        $this->response($response, 201); 
    }


    public function login_post()
    {
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
            return;
        }

        $email = $this->input->post('email');
        $password = $this->input->post('password');

        $result = $this->usermodel->authenticateUser($email, $password);
        if (!$result['status']) {
            $response = [
                'status' => 'error',
                'message' => $result['message']
            ];

            $statusCode = ($result['message'] === 'Internal server error') ? 500 : 401;
            $this->response($response, $statusCode);
            return;
        }

        $response = [
            'status' => 'success',
            'message' => 'Login successful'
        ];
        $this->response($response, 200);
    }

    public function logout_get()
    {
        $this->session->sess_destroy();

        $response = [
            'status' => 'success',
            'message' => 'Logged out successfully'
        ];
        $this->response($response, 200);
    }

    public function verify_get()
    {
        if (isset($this->session->is_logged_in) && $this->session->is_logged_in == true) {
            $response = [
                'status' => 'success',
                'message' => 'User is logged in'
            ];
            $this->response($response, 200);
        } else {
            $response = [
                'status' => 'error',
                'message' => 'User is not logged in'
            ];
            $this->response($response, 401);
        }
    }
}
