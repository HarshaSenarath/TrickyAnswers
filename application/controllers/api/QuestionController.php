<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';
use chriskacerguis\RestServer\RestController;

class QuestionController extends RestController {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('questionmodel');
        $this->load->model('authmodel');
    }

    public function questions_post()
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
            $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('content', 'Content', 'trim|required');

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
                'title' => $this->post('title'),
                'content' => $this->post('content'),
                'user_id' => $userId
            ];
            
            $result = $this->questionmodel->createQuestion($data);

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

    public function questions_get()
    {
        try {
            $userId = $this->get('userId');

            $result = $this->questionmodel->getQuestions($userId);

            if (!$result['status']) {
                $statusCode = ($result['message'] === 'No questions found') ? 404 : 500;
                $response = [
                    'status' => 'error',
                    'message' => $result['message']
                ];
                $this->response($response, $statusCode);
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

    public function questions_put()
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
            $this->form_validation->set_rules('title', 'Title', 'trim|required|max_length[255]');
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
            $questionId = $this->put('questionId');

            $isOwner = $this->questionmodel->isQuestionOwner($userId, $questionId);

            if (!$isOwner['status']) {
                $statusCode = 500;
                if ($isOwner['message'] === 'Question not found') {
                    $statusCode = 404;
                } elseif ($isOwner['message'] === 'Question not owned by user') {
                    $statusCode = 401;
                }

                $response = [
                    'status' => 'error',
                    'message' => $isOwner['message']
                ];
                $this->response($response, $statusCode);
            }

            $data = [
                'title' => $this->put('title'),
                'content' => $this->put('content')
            ];

            $result = $this->questionmodel->updateQuestion($questionId, $data);

            if (!$result['status']) {
                $statusCode = ($result['message'] === 'No changes were made to the question') ? 304 : 500;

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

    public function questions_delete($questionId)
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

            $isOwner = $this->questionmodel->isQuestionOwner($userId, $questionId);

            if (!$isOwner['status']) {
                $statusCode = 500;
                if ($isOwner['message'] === 'Question not found') {
                    $statusCode = 404;
                } elseif ($isOwner['message'] === 'Question not owned by user') {
                    $statusCode = 401;
                }

                $response = [
                    'status' => 'error',
                    'message' => $isOwner['message']
                ];
                $this->response($response, $statusCode);
            }

            $result = $this->questionmodel->deleteQuestion($questionId);

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
