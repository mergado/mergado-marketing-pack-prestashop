<?php

class JsonResponse
{
    public static function send_json_success($content)
    {
        http_response_code(200);
        echo json_encode($content);
        exit;
    }

    public static function send_json_error($content)
    {
        http_response_code(500);
        echo json_encode($content);
        exit;
    }

    public static function send_json_code($content, $code)
    {
        http_response_code($code);
        echo json_encode($content);
        exit;
    }
}