<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class TagController extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tagmodel');
        $this->load->model('authmodel');
    }

    public function tags_post()
    {
        try {
            
        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function tags_get($questionId)
    {
        try {
            $result = $this->tagmodel->getTags($questionId);

            if (!$result['status']) {
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, 500);
            }

            $response = [
                'status' => 'success',
                'data' => $result['data']
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

    public function tags_put()
    {
        try {

        } catch (Exception $e) {
            log_message('error', 'Server error: ' . $e->getMessage());

            $response = [
                'status' => 'error',
                'message' => 'Internal server error'
            ];
            $this->response($response, 500);
        }
    }

    public function tags_delete($questionId)
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
