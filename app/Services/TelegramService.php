<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $botToken;
    protected string $channelId;
    protected string $apiBase;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->channelId = config('services.telegram.channel_id');
        $this->apiBase = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a text message to the Telegram channel.
     */
    public function sendMessage(string $text, string $parseMode = 'HTML'): bool
    {
        if (empty($this->botToken) || empty($this->channelId)) {
            Log::warning('Telegram: Bot token or channel ID not configured');
            return false;
        }

        try {
            $response = Http::timeout(15)->post("{$this->apiBase}/sendMessage", [
                'chat_id' => $this->channelId,
                'text' => $text,
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => false,
            ]);

            if ($response->successful() && ($response->json()['ok'] ?? false)) {
                Log::info('Telegram: Message sent successfully');
                return true;
            }

            Log::error('Telegram: Failed to send message', [
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram: Exception sending message', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send a photo with caption to the Telegram channel.
     */
    public function sendPhoto(string $imageUrl, string $caption = '', string $parseMode = 'HTML'): bool
    {
        if (empty($this->botToken) || empty($this->channelId)) {
            Log::warning('Telegram: Bot token or channel ID not configured');
            return false;
        }

        try {
            $response = Http::timeout(15)->post("{$this->apiBase}/sendPhoto", [
                'chat_id' => $this->channelId,
                'photo' => $imageUrl,
                'caption' => $caption,
                'parse_mode' => $parseMode,
            ]);

            if ($response->successful() && ($response->json()['ok'] ?? false)) {
                Log::info('Telegram: Photo sent successfully');
                return true;
            }

            Log::error('Telegram: Failed to send photo', [
                'response' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Telegram: Exception sending photo', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
