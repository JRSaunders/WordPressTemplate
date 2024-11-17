<?php
/*
Plugin Name: AI Blog Writer
Description: Write blog posts with AI automatically using OpenAI Assistants API
Version: 1.0
Author: John R Saunders
*/

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if Composer autoload exists
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use OpenAI\Client;

// Add this near your other add_action hooks
add_action('wp_enqueue_scripts', 'ai_blog_writer_enqueue_styles');
add_action('admin_enqueue_scripts', 'ai_blog_writer_enqueue_styles'); // Also load in admin

function ai_blog_writer_enqueue_styles() {
    wp_enqueue_style(
        'ai-blog-writer-styles',
        plugins_url('css/blog-writer.css', __FILE__),
        array(),
        '1.0.0'
    );
}

// Add after the plugin header and autoload
require_once plugin_dir_path(__FILE__) . 'includes/cron.php';

// Add these hooks right after the require_once
register_activation_hook(__FILE__, 'ai_blog_writer_schedule_posts');
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('ai_blog_writer_cron_hook');
});

// Add this helper function at the top level (not inside any other function)
function ai_blog_writer_get_recent_titles($count = 3) {
    $recent_posts = get_posts(array(
        'numberposts' => $count,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_type' => 'post',
        'post_status' => 'publish'
    ));

    $titles = array();
    foreach ($recent_posts as $post) {
        $titles[] = $post->post_title;
    }

    return $titles;
}

// Basic plugin functionality first
function ai_blog_writer_admin_menu() {
    add_menu_page(
        'AI Blog Writer', // Page title
        'AI Blog Writer', // Menu title
        'manage_options', // Capability required
        'ai-blog-writer', // Menu slug
        'ai_blog_writer_settings_page', // Function to display the page
        'dashicons-edit', // Icon
        25 // Position (25 puts it between Posts and Media)
    );
}
add_action('admin_menu', 'ai_blog_writer_admin_menu');

// Register settings
function ai_blog_writer_register_settings() {
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_api_key', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_assistant_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_site_topic');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_keywords');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_tone');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_length');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_frequency');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_plug_title');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_plug_text');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_plug_link');
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_plug_image');

    // Add spelling setting
    add_settings_field(
        'ai_blog_writer_spelling',
        'Spelling',
        function() {
            ?>
            <select name="ai_blog_writer_spelling">
                <option value="US" <?php selected(get_option('ai_blog_writer_spelling'), 'US'); ?>>US English</option>
                <option value="UK" <?php selected(get_option('ai_blog_writer_spelling'), 'UK'); ?>>UK English</option>
            </select>
            <p class="description">Choose between US or UK English spelling</p>
            <?php
        },
        'ai-blog-writer-settings',
        'ai_blog_writer_section'
    );
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_spelling');

    // Add auto publish setting
    register_setting('ai-blog-writer-settings', 'ai_blog_writer_auto_publish', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'no'
    ]);

    add_settings_field(
        'ai_blog_writer_auto_publish',
        'Auto Publish',
        'ai_blog_writer_auto_publish_callback',
        'ai-blog-writer-settings',
        'ai_blog_writer_section'
    );
}
add_action('admin_init', 'ai_blog_writer_register_settings');

// Add the callback for the auto publish setting
function ai_blog_writer_auto_publish_callback() {
    $auto_publish = get_option('ai_blog_writer_auto_publish', 'no');
    ?>
    <input type="checkbox" 
           id="ai_blog_writer_auto_publish" 
           name="ai_blog_writer_auto_publish" 
           value="yes" 
           <?php checked('yes', $auto_publish); ?>>
    <label for="ai_blog_writer_auto_publish">Automatically publish generated posts</label>
    <p class="description">If checked, posts will be published immediately. If unchecked, posts will be saved as drafts.</p>
    <?php
}

// Settings page with full content
function ai_blog_writer_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <?php settings_errors(); // Display settings errors/updates ?>
        
        <form method="post" action="options.php">
            <?php
            settings_fields('ai-blog-writer-settings');
            do_settings_sections('ai-blog-writer-settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">OpenAI API Key</th>
                    <td>
                        <input type="password" 
                               name="ai_blog_writer_api_key" 
                               value="<?php echo esc_attr(get_option('ai_blog_writer_api_key')); ?>" 
                               class="regular-text" />
                        <p class="description">Your OpenAI API key for the blog writer</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Assistant ID</th>
                    <td>
                        <input type="text" 
                               name="ai_blog_writer_assistant_id" 
                               value="<?php echo esc_attr(get_option('ai_blog_writer_assistant_id')); ?>" 
                               class="regular-text" />
                        <p class="description">Your OpenAI Assistant ID</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Site Topic</th>
                    <td>
                        <input type="text" 
                               name="ai_blog_writer_site_topic" 
                               value="<?php echo esc_attr(get_option('ai_blog_writer_site_topic')); ?>" 
                               class="regular-text" />
                        <p class="description">Main topic of your website</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Keywords</th>
                    <td>
                        <textarea name="ai_blog_writer_keywords" 
                                  class="large-text" 
                                  rows="3"><?php echo esc_textarea(get_option('ai_blog_writer_keywords')); ?></textarea>
                        <p class="description">Comma-separated keywords to include in posts</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Writing Tone</th>
                    <td>
                        <select name="ai_blog_writer_tone">
                            <option value="professional" <?php selected(get_option('ai_blog_writer_tone'), 'professional'); ?>>Professional</option>
                            <option value="casual" <?php selected(get_option('ai_blog_writer_tone'), 'casual'); ?>>Casual</option>
                            <option value="academic" <?php selected(get_option('ai_blog_writer_tone'), 'academic'); ?>>Academic</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Post Length (words)</th>
                    <td>
                        <input type="number" 
                               name="ai_blog_writer_length" 
                               value="<?php echo esc_attr(get_option('ai_blog_writer_length', '1000')); ?>" 
                               min="500" max="3000" step="100" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Posting Frequency</th>
                    <td>
                        <select name="ai_blog_writer_frequency">
                            <option value="disabled" <?php selected(get_option('ai_blog_writer_frequency'), 'disabled'); ?>>Disabled</option>
                            <option value="daily" <?php selected(get_option('ai_blog_writer_frequency'), 'daily'); ?>>Daily</option>
                            <option value="twice_daily" <?php selected(get_option('ai_blog_writer_frequency'), 'twice_daily'); ?>>Twice Daily</option>
                            <option value="weekly" <?php selected(get_option('ai_blog_writer_frequency'), 'weekly'); ?>>Weekly</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Spelling</th>
                    <td>
                        <select name="ai_blog_writer_spelling">
                            <option value="US" <?php selected(get_option('ai_blog_writer_spelling'), 'US'); ?>>US English</option>
                            <option value="UK" <?php selected(get_option('ai_blog_writer_spelling'), 'UK'); ?>>UK English</option>
                        </select>
                        <p class="description">Choose between US or UK English spelling</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Auto Publish</th>
                    <td>
                        <?php ai_blog_writer_auto_publish_callback(); ?>
                    </td>
                </tr>
            </table>
            <h2>Site Plug Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Plug Title</th>
                    <td>
                        <input type="text" 
                               name="ai_blog_writer_plug_title" 
                               value="<?php echo esc_attr(get_option('ai_blog_writer_plug_title', 'Visit Our Shop')); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Plug Text</th>
                    <td>
                        <textarea name="ai_blog_writer_plug_text" rows="3" class="large-text"><?php 
                            echo esc_textarea(get_option('ai_blog_writer_plug_text', 
                            'Check out our amazing products in our online store!')); 
                        ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Plug Link</th>
                    <td>
                        <input type="url" 
                               name="ai_blog_writer_plug_link" 
                               value="<?php echo esc_url(get_option('ai_blog_writer_plug_link', home_url('/'))); ?>" 
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Plug Image URL</th>
                    <td>
                        <input type="url" 
                               name="ai_blog_writer_plug_image" 
                               value="<?php echo esc_url(get_option('ai_blog_writer_plug_image')); ?>" 
                               class="regular-text" />
                        <p class="description">Enter the URL of the image to display in the plug</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <hr>

        <h2>Generate Post Now</h2>
        <form method="post" action="">
            <?php wp_nonce_field('generate_post_now', 'generate_post_nonce'); ?>
            <input type="submit" name="generate_post_now" class="button button-primary" value="Generate Post Now">
        </form>

        <hr>
        <h3>Recent Posts</h3>
        <?php
        $recent_titles = ai_blog_writer_get_recent_titles(5);
        if (!empty($recent_titles)) {
            echo '<ul>';
            foreach ($recent_titles as $title) {
                echo '<li>' . esc_html($title) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No posts generated yet.</p>';
        }
        ?>
    </div>
    <?php
}

// Initialize plugin
function ai_blog_writer_init() {
    add_action('admin_init', 'ai_blog_writer_register_settings');
}
add_action('init', 'ai_blog_writer_init');

// Add to your ai_blog_writer_handle_actions function:

function ai_blog_writer_handle_actions() {
    // Handle assistant creation/update
    if (isset($_POST['create_update_assistant']) && check_admin_referer('ai_blog_writer_settings-options')) {
        $api_key = get_option('ai_blog_writer_api_key');
        $site_topic = get_option('ai_blog_writer_site_topic');
        
        if (empty($api_key) || empty($site_topic)) {
            add_settings_error(
                'ai_blog_writer',
                'missing_settings',
                'Please set your API key and Site Topic first.',
                'error'
            );
            return;
        }
        
        $assistant_id = ai_blog_writer_create_assistant($site_topic);
        
        if ($assistant_id) {
            add_settings_error(
                'ai_blog_writer',
                'assistant_created',
                'Assistant successfully ' . (get_option('ai_blog_writer_assistant_id') ? 'updated' : 'created') . '!',
                'success'
            );
            update_option('ai_blog_writer_assistant_id', $assistant_id);
        } else {
            add_settings_error(
                'ai_blog_writer',
                'assistant_creation_failed',
                'Failed to create/update assistant. Check your API key and settings.',
                'error'
            );
        }
    }

    // Your existing handle_actions code...
}

// Add action for handling the assistant creation
add_action('admin_init', 'ai_blog_writer_handle_actions');

// Then update the generate post function to include recent titles
function ai_blog_writer_generate_post() {
    try {
        // Set maximum execution time to 5 minutes
        set_time_limit(500);
        
        // Increase memory limit if needed
        ini_set('memory_limit', '512M');
        
        ai_blog_writer_validate_settings();
        
        // Get all settings
        $api_key = get_option('ai_blog_writer_api_key');
        $assistant_id = get_option('ai_blog_writer_assistant_id');
        $site_topic = get_option('ai_blog_writer_site_topic');
        $keywords = get_option('ai_blog_writer_keywords');
        $tone = get_option('ai_blog_writer_tone');
        $length = get_option('ai_blog_writer_length', '1000');
        $spelling = get_option('ai_blog_writer_spelling', 'US');
        
        // reduce keyowrds string delimited with commas to array
        $keywords_array = explode(',', $keywords);

        // now get 5 random keywords from the array
        $random_keywords = array_rand($keywords_array, 5);
        $random_keywords_text = implode(', ', $random_keywords);

        // Get recent titles to avoid duplication
        $recent_titles = ai_blog_writer_get_recent_titles(3);
        $recent_titles_text = !empty($recent_titles) ? 
            "Recent post titles to avoid duplicating:\n- " . implode("\n- ", $recent_titles) : 
            "No recent posts to reference.";

        // Construct the prompt
        $prompt = sprintf(
            "Generate a blog post with these requirements:
            - Topic area: %s
            - Target keywords: %s
            - Writing tone: %s
            - Target length: %d words
            - Use %s English spelling and conventions
            - Use proper HTML spacing between elements
            - Add margin-bottom to headings
            
            %s
            
            Return the blog post in this exact JSON format:
            {
                \"title\": \"An SEO-optimized title that includes a primary keyword\",
                \"meta_description\": \"A compelling meta description under 160 characters that includes keywords\",
                \"body\": \"The body of the blog post content with proper HTML formatting h2 and h3 headings between elements\",
                \"dalle_prompt\": \"A detailed prompt for DALL-E to generate a relevant featured image - no text in the image\"
            }",
            esc_html($site_topic),
            esc_html($random_keywords_text),
            esc_html($tone),
            intval($length),
            esc_html($spelling),
            $recent_titles_text
        );

        $client = OpenAI::factory()
            ->withApiKey($api_key)
            ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 120]))
            ->make();

        // Create thread with empty messages array
        $thread = $client->threads()->create([
            'messages' => []
        ]);

        // Store thread ID in case we need to resume
        update_option('ai_blog_writer_last_thread_id', $thread->id);

        // Add message to thread
        $message = $client->threads()->messages()->create(
            threadId: $thread->id,
            parameters: [
                'role' => 'user',
                'content' => $prompt
            ]
        );

        // Create and run the assistant
        $run = $client->threads()->runs()->create(
            threadId: $thread->id,
            parameters: [
                'assistant_id' => $assistant_id
            ]
        );

        // Store run ID
        update_option('ai_blog_writer_last_run_id', $run->id);

        // Poll for completion with better timeout handling
        $attempts = 0;
        $max_attempts = 60; // Increase max attempts
        $delay_seconds = 5; // Longer delay between checks
        
        do {
            // Check if we should continue
            if (!get_option('ai_blog_writer_generation_active', true)) {
                throw new Exception('Generation cancelled by user');
            }

            sleep($delay_seconds);
            
            $run_status = $client->threads()->runs()->retrieve(
                threadId: $thread->id,
                runId: $run->id
            );

            // Update status in options table
            update_option('ai_blog_writer_generation_status', [
                'status' => $run_status->status,
                'attempts' => $attempts,
                'max_attempts' => $max_attempts,
                'last_check' => current_time('mysql')
            ]);

            if ($run_status->status === 'completed') {
                // Get the messages
                $messages = $client->threads()->messages()->list(
                    threadId: $thread->id,
                    parameters: ['limit' => 1]
                );

                $content = $messages->data[0]->content[0]->text->value;
                
                // Clear status
                delete_option('ai_blog_writer_generation_status');
                delete_option('ai_blog_writer_generation_active');
                
                // Parse response
                $post_data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Save the post and return the ID
                    $post_id = ai_blog_writer_save_post($post_data);
                    if ($post_id) {
                        return $post_id;
                    }
                }
                throw new Exception('Failed to parse AI response: ' . json_last_error_msg());
            } else if ($run_status->status === 'failed') {
                throw new Exception('Assistant run failed: ' . ($run_status->lastError->message ?? 'Unknown error'));
            }

            $attempts++;
        } while ($attempts < $max_attempts);

        throw new Exception('Timeout waiting for assistant response');

    } catch (Exception $e) {
        // Clear status on error
        delete_option('ai_blog_writer_generation_status');
        delete_option('ai_blog_writer_generation_active');
        
        error_log('AI Blog Writer Error: ' . $e->getMessage());
        return false;
    }
}

// Handle the post generation and show admin notices
function ai_blog_writer_handle_generate_post() {
    if (isset($_POST['generate_post_now']) && check_admin_referer('generate_post_now', 'generate_post_nonce')) {
        $post_id = ai_blog_writer_generate_post();
        
        if ($post_id) {
            $edit_link = get_edit_post_link($post_id);
            $view_link = get_permalink($post_id);
            
            add_settings_error(
                'ai_blog_writer',
                'post_generated',
                sprintf(
                    'Post generated successfully! <a href="%s">Edit Post</a> | <a href="%s">View Post</a>',
                    esc_url($edit_link),
                    esc_url($view_link)
                ),
                'success'
            );
        } else {
            add_settings_error(
                'ai_blog_writer',
                'post_generation_failed',
                'Failed to generate post. Please check your settings and try again.',
                'error'
            );
        }
    }
}
add_action('admin_init', 'ai_blog_writer_handle_generate_post');

function ai_blog_writer_validate_settings() {
    $required_settings = [
        'ai_blog_writer_api_key' => 'API Key',
        'ai_blog_writer_assistant_id' => 'Assistant ID',
        'ai_blog_writer_site_topic' => 'Site Topic',
    ];

    foreach ($required_settings as $option => $label) {
        if (empty(get_option($option))) {
            throw new Exception("$label is required but not set.");
        }
    }

    // Validate length is within reasonable bounds
    $length = intval(get_option('ai_blog_writer_length', '1000'));
    if ($length < 500 || $length > 3000) {
        throw new Exception("Post length must be between 500 and 3000 words.");
    }

    // Validate spelling setting
    $spelling = get_option('ai_blog_writer_spelling', 'US');
    if (!in_array($spelling, ['US', 'UK'])) {
        throw new Exception("Invalid spelling option selected.");
    }
}

// Add status display to admin page
function ai_blog_writer_add_status_display() {
    $status = get_option('ai_blog_writer_generation_status');
    if ($status) {
        ?>
        <div class="notice notice-info">
            <p>
                Generation Status: <?php echo esc_html(ucfirst($status['status'])); ?>
                (Attempt <?php echo esc_html($status['attempts']); ?> of <?php echo esc_html($status['max_attempts']); ?>)
                <button class="button" onclick="cancelGeneration()">Cancel Generation</button>
            </p>
        </div>
        <script>
        function cancelGeneration() {
            jQuery.post(ajaxurl, {
                action: 'cancel_ai_generation',
                nonce: '<?php echo wp_create_nonce('cancel_ai_generation'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
        </script>
        <?php
    }
}
add_action('admin_notices', 'ai_blog_writer_add_status_display');

// Add AJAX handler for cancellation
function ai_blog_writer_cancel_generation() {
    check_ajax_referer('cancel_ai_generation', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    update_option('ai_blog_writer_generation_active', false);
    wp_send_json_success();
}
add_action('wp_ajax_cancel_ai_generation', 'ai_blog_writer_cancel_generation');

// Shortcode implementation
function ai_blog_writer_register_shortcodes() {
    add_shortcode('blog_site_plug', 'ai_blog_writer_plug_shortcode');
}
add_action('init', 'ai_blog_writer_register_shortcodes');

function ai_blog_writer_plug_shortcode($atts) {
    // Get settings
    $title = get_option('ai_blog_writer_plug_title', 'Visit Our Shop');
    $text = get_option('ai_blog_writer_plug_text', 'Check out our amazing products in our online store!');
    $link = get_option('ai_blog_writer_plug_link', home_url('/'));
    $image = get_option('ai_blog_writer_plug_image');

    // Build HTML
    $output = '<div class="blog-site-plug">';
    
    if ($image) {
        $output .= sprintf(
            '<a href="%s"><img src="%s" alt="%s" class="plug-image" /></a>',
            esc_url($link),
            esc_url($image),
            esc_attr($title)
        );
    }
    
    $output .= sprintf(
        '<h3 class="plug-title"><a href="%s">%s</a></h3>',
        esc_url($link),
        esc_html($title)
    );
    
    $output .= sprintf(
        '<p class="plug-text">%s</p>',
        esc_html($text)
    );
    
    $output .= sprintf(
        '<a href="%s" class="button add-to-cart-button">Learn More</a>',
        esc_url($link)
    );
    
    $output .= '</div>';

    // Add default styles
    $output .= '
    <style>
        .blog-site-plug {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .blog-site-plug .plug-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .blog-site-plug .plug-title {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #333;
        }
        .blog-site-plug .plug-title a {
            text-decoration: none;
            color: inherit;
        }
        .blog-site-plug .plug-text {
            margin: 0 0 20px 0;
            color: #666;
            line-height: 1.6;
        }
        .blog-site-plug .plug-button {
            display: inline-block;
            padding: 10px 20px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }
        .blog-site-plug .plug-button:hover {
            background: #005177;
        }
    </style>';

    return $output;
}

function ai_blog_create_dalle_image($prompt) {
    try {
        if (!class_exists('OpenAI\Client')) {
            error_log('AI Blog Writer - OpenAI client class not found');
            throw new Exception('OpenAI client not properly loaded');
        }
        $api_key = get_option('ai_blog_writer_api_key');
        
        error_log('AI Blog Writer - Starting DALL-E image generation');
        error_log('AI Blog Writer - API Key exists: ' . (!empty($api_key) ? 'yes' : 'no'));
        
        if (empty($api_key)) {
            throw new Exception('API key is missing');
        }

        $client = OpenAI::factory()
            ->withApiKey($api_key)
            ->withHttpClient(new \GuzzleHttp\Client(['timeout' => 300]))
            ->make();

        error_log('AI Blog Writer - About to make DALL-E request');
        
        try {
            $response = $client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
                'response_format' => 'url',
            ]);
            error_log('AI Blog Writer - DALL-E request successful');
        } catch (Exception $e) {
            error_log('AI Blog Writer - DALL-E API Error: ' . $e->getMessage());
            throw $e;
        }

        if (empty($response->data[0]->url)) {
            throw new Exception('No image URL in response');
        }

        $image_url = $response->data[0]->url;
        error_log('AI Blog Writer - Image URL received: ' . $image_url);

        // Download image with increased timeout
        $args = array(
            'timeout' => 60,  // Increase timeout to 60 seconds
            'sslverify' => false, // Sometimes needed in local development
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
        );

        error_log('AI Blog Writer - Attempting to download image with 60s timeout');
        $response = wp_remote_get($image_url, $args);

        if (is_wp_error($response)) {
            error_log('AI Blog Writer - wp_remote_get error: ' . $response->get_error_message());
            
            // Fallback method using curl directly
            error_log('AI Blog Writer - Trying fallback curl method');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $image_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36');
            
            $image_data = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if (!empty($curl_error)) {
                error_log('AI Blog Writer - Curl error: ' . $curl_error);
                throw new Exception('Failed to download image via curl: ' . $curl_error);
            }
            
            if (empty($image_data)) {
                throw new Exception('No image data received from curl');
            }
        } else {
            $image_data = wp_remote_retrieve_body($response);
            if (empty($image_data)) {
                throw new Exception('No image data received from wp_remote_get');
            }
        }

        error_log('AI Blog Writer - Image download successful, size: ' . strlen($image_data) . ' bytes');

        // Save to WordPress uploads
        $upload = wp_upload_bits('dalle-' . time() . '.png', null, $image_data);
        
        if (!empty($upload['error'])) {
            throw new Exception('Failed to upload image: ' . $upload['error']);
        }

        // Prepare attachment
        $wp_filetype = wp_check_filetype(basename($upload['file']), null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name(basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Insert attachment
        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (is_wp_error($attach_id)) {
            throw new Exception('Failed to insert attachment: ' . $attach_id->get_error_message());
        }

        // Generate metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        error_log('AI Blog Writer - Successfully created image with ID: ' . $attach_id);
        return $attach_id;

    } catch (Exception $e) {
        error_log('AI Blog Writer - Error: ' . $e->getMessage());
        error_log('AI Blog Writer - Error trace: ' . $e->getTraceAsString());
        return false;
    }
}

// Update save post function to include featured image
function ai_blog_writer_save_post($post_data) {
    try {
        $post_content = '';
        
        // Store image_id outside the if block so we can use it later
        $image_id = null;
        
        // Generate image if prompt exists
        if (!empty($post_data['dalle_prompt'])) {
            $image_id = ai_blog_create_dalle_image($post_data['dalle_prompt']);
            
            if ($image_id) {
                $image_html = wp_get_attachment_image($image_id, 'full', false, array(
                    'class' => 'ai-generated-image',
                    'alt' => esc_attr($post_data['title'])
                ));
                
                $post_content .= sprintf(
                    '<figure class="wp-block-image ai-image-container">%s</figure>',
                    $image_html
                );
            }
        }

        // Add main content
        $post_content .= wp_kses_post($post_data['body']);

        // Check auto publish setting
        $auto_publish = get_option('ai_blog_writer_auto_publish', 'no');
        $post_status = ($auto_publish === 'yes') ? 'publish' : 'draft';

        // Create post
        $post_args = array(
            'post_title'    => wp_strip_all_tags($post_data['title']),
            'post_content'  => $post_content,
            'post_status'   => $post_status,
            'post_author'   => get_current_user_id(),
            'post_type'     => 'post'
        );

        $post_id = wp_insert_post($post_args);

        if (is_wp_error($post_id)) {
            return false;
        }

        // Set featured image if we have one
        if ($image_id) {
            set_post_thumbnail($post_id, $image_id);
        }

        return $post_id;

    } catch (Exception $e) {
        error_log('AI Blog Writer - Error saving post: ' . $e->getMessage());
        return false;
    }
}

// Update the blog site plug style
function ai_blog_writer_site_plug_shortcode() {
    $output = '<div class="blog-site-plug">
        <h3 class="plug-title"><a href="' . esc_url(home_url('/')) . '">' . esc_html(get_option('ai_blog_writer_plug_title', 'Visit Our Shop')) . '</a></h3>
        <p class="plug-text">' . esc_html(get_option('ai_blog_writer_plug_text', 'Check out our amazing products!')) . '</p>
        <a href="' . esc_url(home_url('/')) . '" class="plug-button">Learn More</a>
    </div>
    <style>
        .blog-site-plug {
            margin: 40px 0;  /* Increased margin */
            padding: 30px;   /* Increased padding */
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
            background: #f9f9f9;  /* Lighter background */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .blog-site-plug .plug-title {
            margin: 0 0 15px 0;
            font-size: 1.5em;
            color: #333;
        }
        .blog-site-plug .plug-title a {
            text-decoration: none;
            color: inherit;
        }
        .blog-site-plug .plug-text {
            margin: 0 0 25px 0;
            color: #666;
            line-height: 1.6;
        }
        .blog-site-plug .plug-button {
            display: inline-block;
            padding: 12px 24px;
            background: #0073aa;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .blog-site-plug .plug-button:hover {
            background: #005177;
            transform: translateY(-1px);
        }
    </style>';

    return $output;
}




