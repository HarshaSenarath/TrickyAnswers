<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class AnswerController extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('answermodel');
        $this->load->model('authmodel');
    }

    public function answers_post()
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
            $this->form_validation->set_rules('content', 'Content', 'trim|required');
            $this->form_validation->set_rules('questionId', 'Question ID', 'required');

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

            $data = [
                'content' => $this->post('content'),
                'question_id' => $this->post('questionId'),
                'user_id' => $userId
            ];
            
            $result = $this->answermodel->createAnswer($data);

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

    public function answers_get($questionId)
    {
        try {
            $result = $this->answermodel->getAnswers($questionId);

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

    public function answers_put()
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
            $this->form_validation->set_rules('content', 'Content', 'trim|required');
            $this->form_validation->set_rules('answerId', 'Answer ID', 'required');

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
            $answerId = $this->put('answerId');

            $isOwner = $this->answermodel->isAnswerOwner($userId, $answerId);

            if (!$isOwner['status']) {
                $statusCode = 500;
                if ($isOwner['message'] === 'Answer not found') {
                    $statusCode = 404;
                } elseif ($isOwner['message'] === 'Answer not owned by user') {
                    $statusCode = 401;
                }

                $response = [
                    'status' => 'error',
                    'message' => $isOwner['message']
                ];
                $this->response($response, $statusCode);
            }

            $data = [
                'content' => $this->put('content')
            ];

            $result = $this->answermodel->updateAnswer($answerId, $data);

            if (!$result['status']) {
                $statusCode = ($result['message'] === 'No changes were made to the answer') ? 304 : 500;

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

    public function answers_delete($answerId)
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

            $isOwner = $this->answermodel->isAnswerOwner($userId, $answerId);

            if (!$isOwner['status']) {
                $statusCode = 500;
                if ($isOwner['message'] === 'Answer not found') {
                    $statusCode = 404;
                } elseif ($isOwner['message'] === 'Answer not owned by user') {
                    $statusCode = 401;
                }

                $response = [
                    'status' => 'error',
                    'message' => $isOwner['message']
                ];
                $this->response($response, $statusCode);
            }

            $result = $this->answermodel->deleteAnswer($answerId);

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
