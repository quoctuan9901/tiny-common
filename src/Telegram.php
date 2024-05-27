<?php

namespace Scaleflex\Commons;

use GuzzleHttp\Client;
use Illuminate\Http\Response;

class Telegram
{
    protected $client;
    protected $apiUrl;

    // Private constructor to prevent direct instantiation
    private function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = 'https://api.telegram.org/bot' . Helper::config('config', 'telegram.bot') . '/sendMessage';
    }

    // Public static method to send notifications
    public static function send($message, $parseMode = 'HTML')
    {
        $telegram = new self();
        $response = $telegram->sendMessage(Helper::config('config', 'telegram.group_id'), $message, $parseMode);
        if ($response['ok']) {
            return new Response(json_encode(['status' => 'success', 'message' => 'Notification sent']), Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }
        return new Response(json_encode(['status' => 'error', 'message' => 'Failed to send notification']), Response::HTTP_BAD_REQUEST, ['Content-Type' => 'application/json']);
    }

    // Private method to send message to Telegram
    private function sendMessage($chatId, $message, $parseMode = 'HTML')
    {
        $response = $this->client->post($this->apiUrl, [
            'form_params' => [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $parseMode
            ]
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }
}