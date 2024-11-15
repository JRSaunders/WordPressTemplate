<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function ai_blog_writer_schedule_posts() {
    $frequency = get_option('ai_blog_writer_frequency', 'disabled');
    
    // Clear any existing schedules
    wp_clear_scheduled_hook('ai_blog_writer_cron_hook');
    
    // Schedule new if not disabled
    if ($frequency !== 'disabled') {
        switch ($frequency) {
            case 'daily':
                wp_schedule_event(time(), 'daily', 'ai_blog_writer_cron_hook');
                break;
            case 'twice_daily':
                wp_schedule_event(time(), 'twicedaily', 'ai_blog_writer_cron_hook');
                break;
            case 'weekly':
                wp_schedule_event(time(), 'weekly', 'ai_blog_writer_cron_hook');
                break;
        }
    }
}

function ai_blog_writer_cron_callback() {
    ai_blog_writer_generate_post();
}
add_action('ai_blog_writer_cron_hook', 'ai_blog_writer_cron_callback');

function ai_blog_writer_handle_frequency_change($old_value, $new_value, $option) {
    if ($option === 'ai_blog_writer_frequency') {
        ai_blog_writer_schedule_posts();
    }
}
add_action('update_option_ai_blog_writer_frequency', 'ai_blog_writer_handle_frequency_change', 10, 3);

// These need to be in the main plugin file:
// register_activation_hook(__FILE__, 'ai_blog_writer_schedule_posts');
// register_deactivation_hook(__FILE__, function() {
//     wp_clear_scheduled_hook('ai_blog_writer_cron_hook');
// }); 