<?php
/*
Plugin Name: ChatGPT Assistant
Description: A WordPress plugin integrating OpenAI's ChatGPT using the Assistants feature with real-time streaming.
Version: 1.0
Author: Your Name
Text Domain: chatgpt-assistant
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
require_once plugin_dir_path(__FILE__) . 'includes/limiter.php';
// Autoload Composer dependencies
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use OpenAI\Client;

/**
 * Enqueue Scripts and Styles
 */
function chatgpt_assistant_enqueue_scripts() {
    // Enqueue jQuery (WordPress includes it by default)
    wp_enqueue_script('jquery');

    // Enqueue Marked.js for Markdown parsing (using CDN)
    wp_enqueue_script(
        'marked-js',
        'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
        array(),
        '4.0.12',
        true
    );

    // Enqueue the main ChatGPT Assistant JavaScript file
    wp_enqueue_script(
        'chatgpt-assistant-js',
        plugin_dir_url(__FILE__) . 'js/chatgpt-assistant.js',
        array('jquery', 'marked-js'),
        '1.0',
        true
    );

    // Enqueue the main ChatGPT Assistant CSS file
    wp_enqueue_style(
        'chatgpt-assistant-css',
        plugin_dir_url(__FILE__) . 'css/chatgpt-assistant.css',
        array(),
        '1.0'
    );

    // Localize script to pass AJAX URL and nonce
    wp_localize_script('chatgpt-assistant-js', 'chatgpt_assistant', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('chatgpt_assistant_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'chatgpt_assistant_enqueue_scripts');

/**
 * Register Shortcode to Display Chat Interface
 */
function chatgpt_assistant_chatbox($atts) {
    $atts = shortcode_atts(array(
        'assistant' => '', // Assistant name parameter
    ), $atts);

    // Debug
    error_log('Shortcode assistants: ' . print_r(get_option('chatgpt_assistant_assistants'), true));
    error_log('Requested assistant: ' . $atts['assistant']);

    // Get the assistant ID based on name
    $assistant_id = '';
    $assistants = get_option('chatgpt_assistant_assistants', array());
    
    if (!empty($atts['assistant'])) {
        // Look for specific assistant
        foreach ($assistants as $assistant) {
            if ($assistant['name'] === $atts['assistant']) {
                $assistant_id = $assistant['id'];
                break;
            }
        }
    } else {
        // Use first assistant as default
        if (!empty($assistants[0]['id'])) {
            $assistant_id = $assistants[0]['id'];
        }
    }

    error_log('Selected assistant ID: ' . $assistant_id);

    if (empty($assistant_id)) {
        return '<div class="error">Assistant not found. Please configure an assistant in the settings.</div>';
    }

    $display_mode = get_option('chatgpt_assistant_display_mode', 'full-page');
    
    ob_start();
    ?>
    <div id="assistant-chat-box" class="display-mode-<?php echo esc_attr($display_mode); ?>" data-assistant-id="<?php echo esc_attr($assistant_id); ?>">
        <?php if ($display_mode === 'floating-icon'): ?>
            <div id="chat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2Z" fill="currentColor"/>
                </svg>
            </div>
            <div id="chat-container" style="display: none;">
                <div id="chat-header">
                    <span>Chat Assistant</span>
                    <button id="close-chat">×</button>
                </div>
                <div id="chat-display"></div>
                <div id="chat-input-container">
                    <input type="text" id="chat-input" placeholder="Ask the assistant..." />
                    <button id="send-chat">Send</button>
                </div>
            </div>
        <?php else: ?>
            <div id="chat-container">
                <div id="chat-display"></div>
                <div id="chat-input-container">
                    <input type="text" id="chat-input" placeholder="Ask the assistant..." />
                    <button id="send-chat">Send</button>
                    <button id="reset-chat">Reset Conversation</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('chatgpt_assistant', 'chatgpt_assistant_chatbox');

/**
 * Admin Menu for Plugin Settings
 */
function chatgpt_assistant_admin_menu() {
    add_options_page(
        'ChatGPT Assistant Settings',
        'ChatGPT Assistant',
        'manage_options',
        'chatgpt-assistant-settings',
        'chatgpt_assistant_render_settings_page'
    );
}
add_action('admin_menu', 'chatgpt_assistant_admin_menu');

/**
 * Register Settings
 */
function chatgpt_assistant_admin_init() {
    // Register settings
    register_setting(
        'chatgpt-assistant-settings',
        'chatgpt_assistant_api_key',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    register_setting(
        'chatgpt-assistant-settings',
        'chatgpt_assistant_display_mode',
        array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'full-page'
        )
    );

    register_setting(
        'chatgpt-assistant-settings',
        'chatgpt_assistant_assistants',
        array(
            'type' => 'array',
            'sanitize_callback' => 'chatgpt_assistant_sanitize_assistants',
            'default' => array()
        )
    );

    // Existing main settings section
    add_settings_section(
        'chatgpt_assistant_main_section',
        'Main Settings',
        null,
        'chatgpt-assistant-settings'
    );

    // Add settings fields
    add_settings_field(
        'chatgpt_assistant_api_key',
        'OpenAI API Key',
        'chatgpt_assistant_api_key_callback',
        'chatgpt-assistant-settings',
        'chatgpt_assistant_main_section'
    );

    add_settings_field(
        'chatgpt_assistant_display_mode',
        'Display Mode',
        'chatgpt_assistant_display_mode_callback',
        'chatgpt-assistant-settings',
        'chatgpt_assistant_main_section'
    );

    add_settings_field(
        'chatgpt_assistant_assistants',
        'Assistants',
        'chatgpt_assistant_assistants_callback',
        'chatgpt-assistant-settings',
        'chatgpt_assistant_main_section'
    );
}
add_action('admin_init', 'chatgpt_assistant_admin_init');

// Add this new callback function
function chatgpt_assistant_assistants_callback() {
    $assistants = get_option('chatgpt_assistant_assistants', array());
    ?>
    <div id="assistants-container">
        <?php
        if (!empty($assistants)) {
            foreach ($assistants as $index => $assistant) {
                ?>
                <div class="assistant-entry">
                    <input type="text" 
                           name="chatgpt_assistant_assistants[<?php echo $index; ?>][name]" 
                           value="<?php echo esc_attr($assistant['name']); ?>"
                           placeholder="Assistant Name"
                           style="width: 200px;" />
                    <input type="text" 
                           name="chatgpt_assistant_assistants[<?php echo $index; ?>][id]" 
                           value="<?php echo esc_attr($assistant['id']); ?>"
                           placeholder="Assistant ID"
                           style="width: 300px;" />
                    <button type="button" class="button remove-assistant">Remove</button>
                </div>
                <?php
            }
        }
        ?>
    </div>
    <button type="button" class="button" id="add-assistant">Add Assistant</button>
    
    <p class="description" style="margin-top: 15px;">
        <strong>How to use:</strong> Add your assistants above, then use the shortcode with the assistant name:<br>
        <code>[chatgpt_assistant assistant="your_assistant_name"]</code><br>
        Example: If you named an assistant "support", use: <code>[chatgpt_assistant assistant="support"]</code>
    </p>
    <script>
    jQuery(document).ready(function($) {
        $('#add-assistant').click(function() {
            const index = $('.assistant-entry').length;
            const html = `
                <div class="assistant-entry">
                    <input type="text" 
                           name="chatgpt_assistant_assistants[${index}][name]" 
                           placeholder="Assistant Name"
                           style="width: 200px;" />
                    <input type="text" 
                           name="chatgpt_assistant_assistants[${index}][id]" 
                           placeholder="Assistant ID"
                           style="width: 300px;" />
                    <button type="button" class="button remove-assistant">Remove</button>
                </div>
            `;
            $('#assistants-container').append(html);
        });

        $(document).on('click', '.remove-assistant', function() {
            $(this).closest('.assistant-entry').remove();
        });
    });
    </script>
    <?php
}

// Sanitization function for assistants
function chatgpt_assistant_sanitize_assistants($assistants) {
    if (!is_array($assistants)) {
        return array();
    }

    $sanitized = array();
    foreach ($assistants as $assistant) {
        if (isset($assistant['name']) && isset($assistant['id'])) {
            $sanitized[] = array(
                'name' => sanitize_text_field($assistant['name']),
                'id' => sanitize_text_field($assistant['id'])
            );
        }
    }
    return $sanitized;
}

/**
 * Render Settings Page
 */
function chatgpt_assistant_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get message stats
    global $wpdb;
    $today = date('Y-m-d');
    $total_messages = 0;
    $user_stats = array();

    // Get all message count transients for today
    $transients = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name, option_value FROM $wpdb->options 
            WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_chatgpt_message_count_') . '%' . $today
        )
    );

    foreach ($transients as $transient) {
        $count = (int)$transient->option_value;
        $total_messages += $count;
        
        // Extract user identifier from transient name
        preg_match('/chatgpt_message_count_(.+)_' . $today . '/', str_replace('_transient_', '', $transient->option_name), $matches);
        if (isset($matches[1])) {
            $user_id = $matches[1];
            $user_stats[$user_id] = $count;
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <!-- Message Stats Card -->
        <div class="card" style="max-width: 100%; margin-bottom: 20px; padding: 10px 20px; background: #fff;">
            <h2>Today's Message Statistics</h2>
            <p><strong>Total Messages Sent Today:</strong> <?php echo esc_html($total_messages); ?></p>
            
            <?php if (!empty($user_stats)): ?>
                <h3>Messages by User</h3>
                <table class="widefat striped" style="max-width: 500px;">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Messages</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_stats as $user_id => $count): ?>
                            <tr>
                                <td>
                                    <?php 
                                    if (strpos($user_id, 'visitor_') === 0) {
                                        echo 'Guest User (' . esc_html(substr($user_id, 0, 15)) . '...)';
                                    } else {
                                        $user = get_user_by('id', $user_id);
                                        echo $user ? esc_html($user->display_name) : 'Unknown User';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($count); ?></td>
                                <td><?php echo esc_html(50 - $count); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No messages sent today.</p>
            <?php endif; ?>
        </div>

        <!-- Settings Form -->
        <form method="post" action="options.php">
            <?php
            settings_fields('chatgpt-assistant-settings');
            do_settings_sections('chatgpt-assistant-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * AJAX Handler to Create a New Thread
 */
function chatgpt_assistant_create_thread() {
    // Check nonce for security
    check_ajax_referer('chatgpt_assistant_nonce', '_ajax_nonce');

    // Retrieve API Key and Assistant ID from options
    $api_key = get_option('chatgpt_assistant_api_key', '');
    $assistant_id = get_option('chatgpt_assistant_assistant_id', '');

    if(empty($assistant_id)) {
        $assistants = get_option('chatgpt_assistant_assistants', array());
        if (!empty($assistants)) {
            $assistant_id = $assistants[0]['id'];
        }
    }


    if (empty($api_key) || empty($assistant_id)) {
        wp_send_json_error(['message' => 'OpenAI API key or Assistant ID is missing. Please configure it in the plugin settings.']);
    }

    try {

        $client = OpenAI::factory()
            ->withApiKey($api_key)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
            ->make();
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Error initializing OpenAI client: ' . $e->getMessage()]);
    }

    try {
        // Create a new thread
        $thread = $client->threads()->create([]);
        $thread_id = $thread->id;

        // Store the Assistant ID associated with this thread
        update_option('chatgpt_assistant_assistant_id_' . $thread_id, $assistant_id);

        wp_send_json_success(['thread_id' => $thread_id]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Error creating thread: ' . $e->getMessage()]);
    }
}
add_action('wp_ajax_chatgpt_assistant_create_thread', 'chatgpt_assistant_create_thread');
add_action('wp_ajax_nopriv_chatgpt_assistant_create_thread', 'chatgpt_assistant_create_thread');

/**
 * AJAX Handler for Server-Sent Events (SSE) Streaming
 */
function chatgpt_assistant_stream() {
    check_ajax_referer('chatgpt_assistant_nonce', '_ajax_nonce');

    // Add limiter check here
    $limiter = new ChatGPT_Assistant_Limiter();
    $can_send = $limiter->can_send_message();
    
    if (!$can_send['allowed']) {
        echo "event: error\n";
        echo "data: " . json_encode(['content' => $can_send['message']]) . "\n\n";
        flush();
        exit;
    }

    // Clear output buffer and set headers
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');

    $thread_id = isset($_GET['thread_id']) ? sanitize_text_field($_GET['thread_id']) : '';
    $message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
    $assistant_id = isset($_GET['assistant_id']) ? sanitize_text_field($_GET['assistant_id']) : '';

    $api_key = get_option('chatgpt_assistant_api_key', '');

    // Validate API key
    if (empty($api_key)) {
        echo "event: error\n";
        echo "data: " . json_encode(['content' => 'API key not configured']) . "\n\n";
        flush();
        exit;
    }

    try {
        $client = OpenAI::factory()
            ->withApiKey($api_key)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
            ->make();

        // Add message to thread
        $client->threads()->messages()->create(
            threadId: $thread_id,
            parameters: [
                'role' => 'user',
                'content' => $message
            ]
        );

        $run = null;
        $currentMessage = '';

        do {
            $stream = $client->threads()->runs()->createStreamed(
                threadId: $thread_id,
                parameters: [
                    'assistant_id' => $assistant_id,
                ],
            );

            foreach ($stream as $response) {
                $event = $response->event;
                $data = $response->response;

                switch ($event) {
                    case 'thread.message.delta':
                        if (isset($data->delta->content)) {
                            $messageChunk = $data->delta->content[0]->text->value;
                            $currentMessage .= $messageChunk;
                            echo "event: message\n";
                            echo "data: " . json_encode(['content' => $messageChunk]) . "\n\n";
                            flush();
                        }
                        break;

                    case 'thread.run.completed':
                        echo "event: completed\n";
                        echo "data: " . json_encode(['content' => 'completed']) . "\n\n";
                        $run = $data;
                        break;

                    case 'thread.run.failed':
                        echo "data: " . json_encode(['message' => 'Conversation failed. Please try again.']) . "\n\n";
                        $run = $data;
                        break;
                }
            }
            usleep(100000); // 0.1 seconds
        } while ($run === null);

    } catch (Exception $e) {
        error_log('ChatGPT Assistant Error: ' . $e->getMessage());
        echo "event: error\n";
        echo "data: " . json_encode(['content' => $e->getMessage()]) . "\n\n";
        flush();
    }

    exit;
}
add_action('wp_ajax_chatgpt_assistant_stream', 'chatgpt_assistant_stream');
add_action('wp_ajax_nopriv_chatgpt_assistant_stream', 'chatgpt_assistant_stream');

// Callback for display mode field
function chatgpt_assistant_display_mode_callback() {
    $display_mode = get_option('chatgpt_assistant_display_mode', 'full-page');
    ?>
    <select name="chatgpt_assistant_display_mode">
        <option value="full-page" <?php selected($display_mode, 'full-page'); ?>>Full Page</option>
        <option value="floating-icon" <?php selected($display_mode, 'floating-icon'); ?>>Floating Icon</option>
    </select>
    <?php
}

// Callback for API key field
function chatgpt_assistant_api_key_callback() {
    $api_key = get_option('chatgpt_assistant_api_key', '');
    echo "<input type='password' name='chatgpt_assistant_api_key' value='$api_key' size='50' />";
}

// Callback for assistant ID field
function chatgpt_assistant_assistant_id_callback() {
    $assistant_id = get_option('chatgpt_assistant_assistant_id', '');
    echo "<input type='text' name='chatgpt_assistant_assistant_id' value='$assistant_id' size='50' />";
}



?>
