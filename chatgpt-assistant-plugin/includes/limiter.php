<?php

if (!defined('ABSPATH')) {
    exit;
}

class ChatGPT_Assistant_Limiter {
    private $user_id;
    private $daily_limit = 50; // Maximum messages per day
    private $cooldown_seconds = 5; // Time between messages
    
    public function __construct() {
        $this->user_id = get_current_user_id() ?: $this->get_visitor_id();
    }

    // Get unique ID for non-logged-in users
    private function get_visitor_id() {
        // Get IP address
        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) 
            ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] 
            : $_SERVER['REMOTE_ADDR'];
            
        // Hash the IP with a salt for privacy
        return wp_hash('visitor_' . $ip . wp_salt());
    }

    // Check if user can send message
    public function can_send_message() {
        if ($this->is_rate_limited()) {
            return [
                'allowed' => false,
                'message' => 'Please wait a few seconds between messages.'
            ];
        }

        if ($this->is_daily_limit_reached()) {
            return [
                'allowed' => false,
                'message' => 'Daily message limit reached. Please try again tomorrow.'
            ];
        }

        return ['allowed' => true];
    }

    // Check rate limiting (cooldown between messages)
    private function is_rate_limited() {
        $last_message_time = get_transient('chatgpt_last_message_' . $this->user_id);
        if ($last_message_time && (time() - $last_message_time) < $this->cooldown_seconds) {
            return true;
        }
        set_transient('chatgpt_last_message_' . $this->user_id, time(), 3600);
        return false;
    }

    // Check daily message limit
    private function is_daily_limit_reached() {
        $today = date('Y-m-d');
        $count_key = 'chatgpt_message_count_' . $this->user_id . '_' . $today;
        $message_count = (int)get_transient($count_key);

        if ($message_count >= $this->daily_limit) {
            return true;
        }

        set_transient($count_key, $message_count + 1, DAY_IN_SECONDS);
        return false;
    }

    // Reset limits for testing
    public function reset_limits() {
        delete_transient('chatgpt_last_message_' . $this->user_id);
        delete_transient('chatgpt_message_count_' . $this->user_id . '_' . date('Y-m-d'));
    }
}
