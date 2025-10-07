<?php
// responses/ResponseHandler.php

require_once __DIR__ . '/../models/Log.php';

class ResponseHandler {
    private $logModel;

    public function __construct() {
        $this->logModel = new Log();
    }

    public function sendSuccess($data, $message = "Operación exitosa", $code = 200) {
        http_response_code($code);
        $response = [
            "status" => "success",
            "message" => $message,
        ];
        if ($data) {
            $response = array_merge($response, $data);
        }
        echo json_encode($response);
    }
    
    public function sendFailure($message, $code, Exception $exception = null) {
        http_response_code($code);
        $response = [
            "status" => "failure",
            "message" => $message
        ];

        if ($exception) {
            $logMessage = (string) $exception;
            $this->logModel->createLog($logMessage);
        }

        echo json_encode($response);
    }
}