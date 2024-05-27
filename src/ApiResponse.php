<?php

namespace Scaleflex\Commons;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;

class ApiResponse
{
    private static function apiResponse(string $status, string $message, int $statusCode = Response::HTTP_OK, array $additionalData = []) {
        $response = [
            'status' => $status,
            'msg' => $message,
        ];

        if (!empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }

        $response['api_info'] = [
            'version' => 'v1',
            'time_execute' => round(microtime(true) - TINY_START, 4) . "s",
            'mode' => Request::has('debug') && Request::get('debug') == 8022 ? "debug" : "production",
            'timestamp' => Carbon::now()->toIso8601String(),
        ];

        if (defined('JSON_INVALID_UTF8_IGNORE') && defined('JSON_UNESCAPED_UNICODE') ) {
            $options = JSON_INVALID_UTF8_IGNORE+JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE;
        } else {
            $options = JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES;
        }

        return new Response(json_encode($response, $options), $statusCode, ['Content-Type' => 'application/json']);
    }

    public static function error(string $message, array $additionalData = [], int $statusCode = Response::HTTP_BAD_REQUEST) {
        self::sendErrorNotification($message, $statusCode);
        return self::apiResponse('error', $message, $statusCode, $additionalData);
    }

    public static function success(array $additionalData = [], string $message = '', int $statusCode = Response::HTTP_OK) {
        return self::apiResponse('success', $message, $statusCode, $additionalData);
    }

    private static function sendErrorNotification($message, $statusCode)
    {   
        $ipAddress = Request::ip();
        $requestUri = Request::fullUrl();
        $method = Request::method();
        $company = Request::header('X-Company-Token', 'Unknown');
        $project = Request::header('X-Project-Token', 'Unknown');
        $session = Request::header('X-Session-Token', 'Unknown');
        $userAgent = Request::userAgent();
        $deviceType = self::getDeviceType($userAgent);

        $formattedHeaders = '';
        $except_headers = ['content-length', 'user-agent', 'postman-token', 'cache-control', 'accept-encoding', 'connection', 'accept'];
        $headersArray = [];

        foreach (getallheaders() as $key => $value) {
            if (!in_array(strtolower($key), $except_headers)) {
                $formattedHeaders .= "$key: $value\n";
                $headersArray[] = "-H \"$key: $value\"";
            }
        }

        $headersString = implode(' ', $headersArray);
        $requestData = json_encode(file_get_contents("php://input"), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $curlCommand = "curl -X {$method} \"{$requestUri}\" {$headersString} -d '{$requestData}'";

        $telegramMessage = "🚨 *Error Notification* 🚨\n\n" .
            "🏢 *Request from:* {$session} (*Company:* {$company} - *Project:* {$project})\n" .
            "💻 *Device:* {$deviceType} (*IP:* {$ipAddress}) \n\n" .
            "*-=-=- Additional Details: -=-=-*\n\n" .
            "🚀 *Request:* {$requestUri}\n" .
            "📝 *Status code:* {$statusCode}\n" .
            "📦 *Method:* {$method}\n" .
            "📡 *Request Headers:*\n```\n" .
            $formattedHeaders .
            "```\n" .
            "📦 *Request Data:*\n```\n" .
            $requestData .
            "```\n\n" .
            "📝 *Issue:*\n```\n" .
            str_replace(['*'], ['\\*'], $message) .
            "```\n\n" .
            "🔗 *Curl Command:*\n```\n" .
            $curlCommand .
            "```";

        try {
            Telegram::send($telegramMessage, 'Markdown');
        } catch (Exception $e) {
            return new Response(json_encode([
                'status' => 'error',
                'msg' => $e->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
        }
    }

    private static function getDeviceType($userAgent): String
    {
        $deviceTypes = [
            'Mozilla/5.0' => 'Browser',
            'curl/' => 'Terminal',
            'PostmanRuntime/' => 'Postman',
            // ... more specific regex patterns
        ];

        foreach ($deviceTypes as $pattern => $type) {
            if (strpos($userAgent, $pattern) !== false) {
                return $type;
            }
        }

        return 'Other';
    }

}
