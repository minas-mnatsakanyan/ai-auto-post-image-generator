<?php
/**
 * Admin Settings Class
 *
 * Handles the admin interface, menu pages, and settings management.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Admin_Settings Class
 *
 * @since 1.0.0
 */
class AIAPG_Admin_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_aiapg_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_aiapg_export_settings', array($this, 'export_settings'));
        add_action('wp_ajax_aiapg_import_settings', array($this, 'import_settings'));
        
        // Schedule AJAX handlers
        add_action('wp_ajax_aiapg_save_schedule', array($this, 'save_schedule'));
        add_action('wp_ajax_aiapg_get_schedule', array($this, 'get_schedule'));
        add_action('wp_ajax_aiapg_delete_schedule', array($this, 'delete_schedule'));
        add_action('wp_ajax_aiapg_run_schedule', array($this, 'run_schedule'));
        add_action('wp_ajax_aiapg_toggle_schedule', array($this, 'toggle_schedule'));
        add_action('wp_ajax_aiapg_test_api_key', array($this, 'test_api_key'));
        add_action('wp_ajax_aiapg_clear_schedule_lock', array($this, 'clear_schedule_lock'));
        add_action('wp_ajax_aiapg_fetch_gemini_models', array($this, 'fetch_gemini_models'));
        
        // Log management AJAX handlers
        add_action('wp_ajax_aiapg_get_log_details', array($this, 'get_log_details'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_menu_icon_fix'), 5);
    }

    /**
     * Sidebar menu icon fix — load on all admin screens (main admin.css only loads on AIAPG pages).
     */
    public function enqueue_admin_menu_icon_fix( $hook_suffix = '' ) {
        unset( $hook_suffix );
        if (!current_user_can('manage_options')) {
            return;
        }
        wp_enqueue_style(
            'aiapg-admin-menu-icon',
            aiapg()->get_url('assets/admin-menu-icon-fix.css'),
            array(),
            AIAPG_VERSION
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('AI Auto Post Generator', 'ai-auto-post-image-generator'),
            __('AI Auto Post', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-admin-generic',
            30
        );

        // Submenu pages
        add_submenu_page(
            'aiapg-dashboard',
            __('Dashboard', 'ai-auto-post-image-generator'),
            __('Dashboard', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-dashboard',
            array($this, 'dashboard_page')
        );

        add_submenu_page(
            'aiapg-dashboard',
            __('Schedule Manager', 'ai-auto-post-image-generator'),
            __('Schedule Manager', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-schedules',
            array($this, 'schedules_page')
        );

        add_submenu_page(
            'aiapg-dashboard',
            __('Settings', 'ai-auto-post-image-generator'),
            __('Settings', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-settings',
            array($this, 'settings_page')
        );

        add_submenu_page(
            'aiapg-dashboard',
            __('Logs', 'ai-auto-post-image-generator'),
            __('Logs', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-logs',
            array($this, 'logs_page')
        );

        add_submenu_page(
            'aiapg-dashboard',
            __('Help', 'ai-auto-post-image-generator'),
            __('Help', 'ai-auto-post-image-generator'),
            'manage_options',
            'aiapg-help',
            array($this, 'help_page')
        );


    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Debug: Log the hook name to see what WordPress is passing
        
        
        if (empty($hook) || strpos($hook, 'aiapg') === false) {
            
            return;
        }

        wp_enqueue_style(
            'aiapg-admin',
            aiapg()->get_url('assets/admin.css'),
            array(),
            AIAPG_VERSION
        );
        
        // Enqueue jQuery UI CSS for datepicker (using WordPress bundled version)
        wp_enqueue_style('jquery-ui-core');
        wp_enqueue_style('jquery-ui-datepicker');

        wp_enqueue_script(
            'aiapg-admin',
            aiapg()->get_url('assets/admin.js'),
            array('jquery', 'jquery-ui-datepicker', 'wp-util'),
            AIAPG_VERSION,
            true
        );

        wp_localize_script('aiapg-admin', 'aiapg_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiapg_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'ai-auto-post-image-generator'),
                'saved' => __('Settings saved!', 'ai-auto-post-image-generator'),
                'error' => __('Error occurred. Please try again.', 'ai-auto-post-image-generator'),
                'confirm_delete' => __('Are you sure you want to delete this schedule?', 'ai-auto-post-image-generator'),
                'confirm_run' => __('Are you sure you want to run this schedule now?', 'ai-auto-post-image-generator')
            )
        ));

        // Add ajaxurl for backward compatibility
        wp_localize_script('aiapg-admin', 'aiapg_ajaxurl', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    /**
     * Dashboard page
     */
    public function dashboard_page() {
        $stats = $this->get_dashboard_stats();
        include aiapg()->get_path('includes/views/dashboard.php');
    }

    /**
     * Schedules page
     */
    public function schedules_page() {
        $schedules = aiapg()->schedule_manager->get_all_schedules();
        include aiapg()->get_path('includes/views/schedules.php');
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $settings = $this->get_settings();
        include aiapg()->get_path('includes/views/settings.php');
    }

    /**
     * Logs page
     */
    public function logs_page() {
        $logs = $this->get_logs();
        include aiapg()->get_path('includes/views/logs.php');
    }

    /**
     * Help page
     */
    public function help_page() {
        include aiapg()->get_path('includes/views/help.php');
    }



    /**
     * Get dashboard statistics
     *
     * @return array
     */
    private function get_dashboard_stats() {
        global $wpdb;

        $stats = array(
            'total_schedules' => 0,
            'active_schedules' => 0,
            'total_posts_created' => 0,
            'total_runs' => 0,
            'recent_logs' => array()
        );

        try {
            // Get schedule stats
            $table_schedules = $wpdb->prefix . 'aiapg_schedules';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $stats['total_schedules'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM " . esc_sql($table_schedules)
            ) ?: 0;
            
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $stats['active_schedules'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM " . esc_sql($table_schedules) . " WHERE is_active = 1"
            ) ?: 0;

            // Get log stats
            $table_logs = $wpdb->prefix . 'aiapg_logs';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $stats['total_posts_created'] = $wpdb->get_var(
                "SELECT SUM(posts_created) FROM " . esc_sql($table_logs) . " WHERE status = 'success'"
            ) ?: 0;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $stats['total_runs'] = $wpdb->get_var(
                "SELECT COUNT(*) FROM " . esc_sql($table_logs)
            ) ?: 0;

            // Get recent logs
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $recent_logs = $wpdb->get_results(
                "SELECT l.*, s.name as schedule_name 
                 FROM " . esc_sql($table_logs) . " l 
                 LEFT JOIN " . esc_sql($table_schedules) . " s ON l.schedule_id = s.id 
                 ORDER BY l.run_time DESC 
                 LIMIT 10"
            );
            
            // Ensure we have an array of valid objects
            if (is_array($recent_logs)) {
                $stats['recent_logs'] = array_filter($recent_logs, function($log) {
                    return is_object($log) && isset($log->status);
                });
            } else {
                $stats['recent_logs'] = array();
            }
        } catch (Exception $e) {
            // If there's an error, return default stats
            
        }

        return $stats;
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    private function get_settings() {
        return array(
            'openai_api_key' => get_option('aiapg_openai_api_key', ''),
            'gemini_api_key' => get_option('aiapg_gemini_api_key', ''),
            'leonardo_api_key' => get_option('aiapg_leonardo_api_key', ''),
            'pollinations_api_key' => get_option('aiapg_pollinations_api_key', ''),
            'default_text_model' => get_option('aiapg_default_text_model', 'gpt-3.5-turbo'),
            'default_image_model' => get_option('aiapg_default_image_model', 'dall-e-2'),
            'auto_publish' => get_option('aiapg_auto_publish', true),
            'post_status' => AIAPG_Utils::get_default_post_status(),
            'post_author' => get_option('aiapg_post_author', get_current_user_id()),
            'enable_logging' => get_option('aiapg_enable_logging', true),
            'log_retention_days' => get_option('aiapg_log_retention_days', 30)
        );
    }

    /**
     * Get logs
     *
     * @return array
     */
    private function get_logs() {
        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        $per_page = 20;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination, sanitized below
        $paged = isset($_GET['paged']) ? sanitize_text_field(wp_unslash($_GET['paged'])) : 1;
        $page  = max(1, intval($paged));
        $offset = ($page - 1) * $per_page;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT l.*, s.name as schedule_name 
                 FROM " . esc_sql($table_logs) . " l 
                 LEFT JOIN " . esc_sql($table_schedules) . " s ON l.schedule_id = s.id 
                 ORDER BY l.run_time DESC 
                 LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($table_logs));

        return array(
            'logs' => $logs,
            'total' => $total_logs,
            'per_page' => $per_page,
            'current_page' => $page,
            'total_pages' => ceil($total_logs / $per_page)
        );
    }

    /**
     * Save settings via AJAX
     */
    public function save_settings() {
        check_ajax_referer('aiapg_nonce', 'nonce') || check_ajax_referer('aiapg_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only pagination, sanitized below
        $raw_data = isset($_POST['data']) ? sanitize_textarea_field(wp_unslash($_POST['data'])) : '';
        parse_str($raw_data, $data);   

        // Save API keys
        if (isset($data['openai_api_key'])) {
            update_option('aiapg_openai_api_key', sanitize_text_field($data['openai_api_key']));
        }
        if (isset($data['gemini_api_key'])) {
            update_option('aiapg_gemini_api_key', sanitize_text_field($data['gemini_api_key']));
        }

        if (isset($data['leonardo_api_key'])) {
            update_option('aiapg_leonardo_api_key', sanitize_text_field($data['leonardo_api_key']));
        }
        if (isset($data['pollinations_api_key'])) {
            update_option('aiapg_pollinations_api_key', sanitize_text_field($data['pollinations_api_key']));
        }

                        // Save default settings
                if (isset($data['default_text_model'])) {
                    $default_text_model = sanitize_text_field($data['default_text_model']);
                    $custom_default_text_model = isset($data['custom_default_text_model'])
                        ? sanitize_text_field($data['custom_default_text_model'])
                        : '';
                    if ($default_text_model === '__custom__') {
                        $default_text_model = $custom_default_text_model;
                    }
                    if ($default_text_model !== '' && $default_text_model !== '__custom__') {
                        update_option('aiapg_default_text_model', $default_text_model);
                    }
                }
                if (isset($data['default_image_model'])) {
                    update_option('aiapg_default_image_model', sanitize_text_field($data['default_image_model']));
                }
                if (isset($data['post_status'])) {
                    update_option(
                        'aiapg_post_status',
                        AIAPG_Utils::normalize_post_status($data['post_status'], 'publish')
                    );
                }
                if (isset($data['post_author'])) {
                    update_option('aiapg_post_author', intval($data['post_author']));
                }
                if (isset($data['posts_per_run'])) {
                    update_option(
                        'aiapg_default_posts_per_run',
                        max(1, min(10, absint($data['posts_per_run'])))
                    );
                }
                if (isset($data['content_length'])) {
                    $presets = array_keys(AIAPG_Post_Generator::get_content_length_presets());
                    $content_length = sanitize_key($data['content_length']);
                    if (in_array($content_length, $presets, true)) {
                        update_option('aiapg_default_content_length', $content_length);
                    }
                }
                if (isset($data['enable_images'])) {
                    update_option('aiapg_default_enable_images', true);
                } else {
                    update_option('aiapg_default_enable_images', false);
                }
                if (isset($data['image_placement'])) {
                    update_option('aiapg_default_image_placement', sanitize_text_field($data['image_placement']));
                }
                if (isset($data['image_size'])) {
                    update_option('aiapg_default_image_size', sanitize_text_field($data['image_size']));
                }
                if (isset($data['images_per_post'])) {
                    update_option('aiapg_default_images_per_post', intval($data['images_per_post']));
                }

                        // Save advanced settings
                if (isset($data['enable_logging'])) {
                    update_option('aiapg_enable_logging', true);
                } else {
                    update_option('aiapg_enable_logging', false);
                }
                if (isset($data['log_retention_days'])) {
                    update_option('aiapg_log_retention_days', intval($data['log_retention_days']));
                }
                if (isset($data['max_retries'])) {
                    update_option('aiapg_max_retries', intval($data['max_retries']));
                }
                if (isset($data['timeout_seconds'])) {
                    update_option('aiapg_timeout_seconds', intval($data['timeout_seconds']));
                }
                if (isset($data['enable_debug_log'])) {
                    update_option('aiapg_enable_debug_log', true);
                } else {
                    update_option('aiapg_enable_debug_log', false);
                }



        wp_send_json_success(esc_html__('Settings saved successfully!', 'ai-auto-post-image-generator'));
    }

    /**
     * Export settings via AJAX
     */
    public function export_settings() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $export_data = array(
            'settings' => $this->get_settings(),
            'schedules' => aiapg()->schedule_manager->get_all_schedules(),
            'export_date' => current_time('mysql'),
            'version' => AIAPG_VERSION
        );

        wp_send_json_success($export_data);
    }

    /**
     * Import settings via AJAX
     */
    public function import_settings() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $raw_data = isset($_POST['import_data']) ? sanitize_textarea_field(wp_unslash($_POST['import_data'])) : '';
        $import_data = json_decode(stripslashes($raw_data), true);

        if (!$import_data || !isset($import_data['settings'])) {
            wp_send_json_error(__('Invalid import data.', 'ai-auto-post-image-generator'));
        }

        // Import settings
        foreach ($import_data['settings'] as $key => $value) {  
            update_option('aiapg_' . $key, $value);
        }

        // Import schedules if provided
        if (isset($import_data['schedules']) && is_array($import_data['schedules'])) {
            aiapg()->schedule_manager->import_schedules($import_data['schedules']);
        }

        wp_send_json_success(__('Settings imported successfully!', 'ai-auto-post-image-generator'));
    }

    /**
     * Save schedule via AJAX
     */
    public function save_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }
    
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw data parsed and sanitized below
        $raw_data = isset($_POST['data']) ? wp_unslash($_POST['data']) : '';
    
        // Handle both URL-encoded string and array data
        if (is_array($raw_data)) {
            $data = $raw_data;
        } else {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        parse_str($raw_data, $data);
        }
        
    
        // ✅ Sanitize individual fields
        $schedule_id   = isset($data['schedule_id']) ? intval($data['schedule_id']) : 0;
        $name          = isset($data['name']) ? sanitize_text_field($data['name']) : '';
        $description   = isset($data['description']) ? sanitize_textarea_field($data['description']) : '';
        $content_src   = isset($data['content_source']) ? sanitize_text_field($data['content_source']) : '';
        $posts_per_run = max(1, min(10, absint($data['posts_per_run'] ?? 1)));
        $text_model    = isset($data['text_model']) ? sanitize_text_field($data['text_model']) : '';
        $custom_text_model = isset($data['custom_text_model']) ? sanitize_text_field($data['custom_text_model']) : '';
        if ($text_model === '__custom__' || ($text_model === '' && $custom_text_model !== '')) {
            $text_model = $custom_text_model;
        }
        // Allow custom Gemini model IDs entered via the custom field even when a preset was also selected.
        if ($custom_text_model !== '' && strpos($custom_text_model, 'gemini') === 0 && $text_model === '__custom__') {
            $text_model = $custom_text_model;
        }
        $image_model   = isset($data['image_model']) ? sanitize_text_field($data['image_model']) : '';
        $fallback_img  = isset($data['fallback_image_model']) ? sanitize_text_field($data['fallback_image_model']) : get_option('aiapg_default_fallback_image_model', 'pollinations');
        $image_place   = isset($data['image_placement']) ? sanitize_text_field($data['image_placement']) : 'both';
        $image_size    = isset($data['image_size']) ? sanitize_text_field($data['image_size']) : '1024x1024';
        $images_per_post = isset($data['images_per_post']) ? intval($data['images_per_post']) : 1;
        $content_length_presets = array_keys(AIAPG_Post_Generator::get_content_length_presets());
        $content_length = isset($data['content_length']) ? sanitize_key($data['content_length']) : 'long';
        if (!in_array($content_length, $content_length_presets, true)) {
            $content_length = 'long';
        }
        $post_status = AIAPG_Utils::normalize_post_status(
            isset($data['post_status']) ? $data['post_status'] : '',
            AIAPG_Utils::get_default_post_status()
        );
        $interval_type = isset($data['interval_type']) ? sanitize_text_field($data['interval_type']) : 'daily';
        $interval_val  = isset($data['interval_value']) ? intval($data['interval_value']) : 1;
        $custom_cron   = isset($data['custom_cron']) ? sanitize_text_field($data['custom_cron']) : '';
        $scheduled_at  = isset($data['scheduled_at']) ? AIAPG_Utils::normalize_datetime(sanitize_text_field($data['scheduled_at'])) : '';
        $is_active     = !empty($data['is_active']) ? 1 : 0;
    
        // ✅ Sanitize categories/prompts properly
        // Handle both 'categories' and 'categories[]' field names
        $categories = [];
        if (isset($data['categories[]'])) {
            $categories = $data['categories[]'];
        } elseif (isset($data['categories'])) {
            $categories = $data['categories'];
        }
        
        // Ensure categories is always an array
        if (!is_array($categories)) {
            $categories = [$categories];
        }
        
        // Handle nested array structure for categories (categories[][])
        if (!empty($categories) && is_array($categories[0])) {
            $categories = $categories[0]; // Extract the inner array
        }
        
        // Handle double nested array structure (categories[][][])
        if (!empty($categories) && is_array($categories[0]) && is_array($categories[0][0])) {
            $categories = $categories[0]; // Extract the inner array
        }
        
        $categories = array_map('intval', $categories);
        $categories = array_filter($categories, function($cat) { return $cat > 0; }); // remove invalids (0 or negative)
        
        
    
        // Handle new structure for custom prompts with categories
        $custom_prompts = array();
        
        // Only process custom prompts if content source is 'prompts'
        if ($content_src === 'prompts') {
            // Handle custom prompts data - check for both formats
            $custom_prompts_data = [];
            if (isset($data['custom_prompts'])) {
                $custom_prompts_data = $data['custom_prompts'];
            }
            
            // Ensure custom_prompts is always an array
            if (!is_array($custom_prompts_data)) {
                $custom_prompts_data = [$custom_prompts_data];
            }
            
            // Look for the new structure first (custom_prompts[0][categories])
            $has_new_structure = false;
            foreach ($custom_prompts_data as $key => $value) {
                if (is_array($value) && isset($value['categories'])) {
                    $has_new_structure = true;
                    break;
                }
            }
            
            if ($has_new_structure) {
                // Process new structure: array with text and categories
                foreach ($custom_prompts_data as $index => $prompt_data) {
                    if (is_array($prompt_data) && isset($prompt_data['categories'])) {
                        $prompt_text = isset($prompt_data['text']) ? sanitize_textarea_field($prompt_data['text']) : '';
                        
                                // Handle nested array structure for categories
                                $prompt_categories = array();
                                if (isset($prompt_data['categories'])) {
                                    $categories_data = $prompt_data['categories'];

                                    // Check if categories is a nested array (from form submission)
                                    if (is_array($categories_data) && !empty($categories_data)) {
                                        // Handle double nested array structure (categories[][][])
                                        if (is_array($categories_data[0]) && is_array($categories_data[0][0])) {
                                            $prompt_categories = array_map('intval', $categories_data[0]);
                                        }
                                        // If the first element is an array, it's nested
                                        elseif (is_array($categories_data[0])) {
                                            $prompt_categories = array_map('intval', $categories_data[0]);
                                        } else {
                                            // It's a flat array
                                            $prompt_categories = array_map('intval', $categories_data);
                                        }
                                    }
                                }
                        
                        $prompt_categories = array_filter($prompt_categories, function($cat) { return $cat > 0; }); // remove invalids
                        
                        if (!empty($prompt_text)) {
                            $prompt_publish_at = '';
                            if (!empty($prompt_data['publish_at'])) {
                                $prompt_publish_at = AIAPG_Utils::normalize_datetime(sanitize_text_field($prompt_data['publish_at']));
                            }

                            $custom_prompts[] = array(
                                'text' => $prompt_text,
                                'categories' => $prompt_categories,
                                'publish_at' => $prompt_publish_at,
                            );
                        }
                    }
                }
            } else {
                // Process old structure: just text
                foreach ($custom_prompts_data as $index => $prompt_data) {
                    $prompt_text = sanitize_textarea_field($prompt_data);
                    if (!empty($prompt_text)) {
                        $custom_prompts[] = $prompt_text;
                    }
                }
            }
        }
        
        // Special handling for mixed format (old text + new categories)
        // This happens when the form sends both custom_prompts[] and custom_prompts[0][categories][]
        if ($content_src === 'prompts' && empty($custom_prompts) && !empty($custom_prompts_data)) {
            // Look for text in the old format
            $prompt_texts = array();
            foreach ($custom_prompts_data as $key => $value) {
                if (is_string($value) && !empty(trim($value))) {
                    $prompt_texts[] = sanitize_textarea_field($value);
                }
            }
            
                    // Look for categories in the new format
                    $prompt_categories = array();
                    if (isset($data['custom_prompts'][0]['categories'])) {
                        $categories_data = $data['custom_prompts'][0]['categories'];

                        // Handle nested array structure
                        if (is_array($categories_data) && !empty($categories_data)) {
                            // Handle double nested array structure (categories[][][])
                            if (is_array($categories_data[0]) && is_array($categories_data[0][0])) {
                                $prompt_categories = array_map('intval', $categories_data[0]);
                            }
                            elseif (is_array($categories_data[0])) {
                                $prompt_categories = array_map('intval', $categories_data[0]);
                            } else {
                                $prompt_categories = array_map('intval', $categories_data);
                            }
                        }

                        $prompt_categories = array_filter($prompt_categories, function($cat) { return $cat > 0; });
                    }
            
            // Combine them
            if (!empty($prompt_texts)) {
                foreach ($prompt_texts as $index => $text) {
                    if (!empty($text)) {
                        $custom_prompts[] = array(
                            'text' => $text,
                            'categories' => $prompt_categories
                        );
                    }
                }
            }
        }
    
        // Validate required fields
        if (empty($name)) {
            wp_send_json_error(__('Schedule name is required.', 'ai-auto-post-image-generator'));
        }
    
        if (empty($content_src)) {
            wp_send_json_error(__('Please select a content source.', 'ai-auto-post-image-generator'));
        }

        if ($content_src === 'categories' && empty($categories)) {
            wp_send_json_error(__('Please select at least one category.', 'ai-auto-post-image-generator'));
        }
    
        if ($content_src === 'prompts' && empty($custom_prompts)) {
            wp_send_json_error(__('Please add at least one custom prompt.', 'ai-auto-post-image-generator'));
        }
        
        // For categories content source, ignore empty custom prompts
        if ($content_src === 'categories') {
            $custom_prompts = array(); // Clear any empty custom prompts
        }

        // Validate that prompts with categories have at least one category selected
        if ($content_src === 'prompts' && !empty($custom_prompts)) {
            foreach ($custom_prompts as $prompt_data) {
                if (is_array($prompt_data) && isset($prompt_data['categories']) && empty($prompt_data['categories'])) {
                    wp_send_json_error(__('Please select at least one category for each custom prompt.', 'ai-auto-post-image-generator'));
                }
            }
        }

        $allowed_intervals = array('hourly', 'daily', 'weekly', 'monthly', 'once', 'custom');
        if (!in_array($interval_type, $allowed_intervals, true)) {
            wp_send_json_error(__('Invalid interval type.', 'ai-auto-post-image-generator'));
        }

        if ($interval_type === 'once') {
            if ($scheduled_at === '') {
                wp_send_json_error(__('Please choose a specific date and time for one-time schedules.', 'ai-auto-post-image-generator'));
            }
            $scheduled_ts = AIAPG_Utils::local_datetime_to_gmt_timestamp($scheduled_at);
            if (!$scheduled_ts) {
                wp_send_json_error(__('Invalid scheduled date and time.', 'ai-auto-post-image-generator'));
            }
        }

        if (empty($text_model) || $text_model === '__custom__') {
            wp_send_json_error(__('Please select a valid text model (or enter a custom Gemini model ID).', 'ai-auto-post-image-generator'));
        }
    
        // Build final data array
        $schedule_data = array(
            'name'               => $name,
            'description'        => $description,
            'posts_per_run'      => $posts_per_run,
            'text_model'         => $text_model,
            'image_model'        => $image_model,
            'fallback_image_model'=> $fallback_img,
            'enable_images'      => isset($data['enable_images']) ? 1 : 0,
            'image_placement'    => $image_place,
            'image_size'         => $image_size,
            'images_per_post'    => $images_per_post,
            'content_length'     => $content_length,
            'post_status'        => $post_status,
            'interval_type'      => $interval_type,
            'interval_value'     => $interval_val,
            'custom_cron'        => $custom_cron,
            'scheduled_at'       => $interval_type === 'once' ? $scheduled_at : '',
            'is_active'          => $is_active,
            'categories'         => $content_src === 'categories' ? $categories : [],
            'custom_prompts'     => $content_src === 'prompts' ? $custom_prompts : [],
        );
    
        // Save or update
        if ($schedule_id > 0) {
            $result = aiapg()->schedule_manager->update_schedule($schedule_id, $schedule_data);
            if ($result) {
                wp_send_json_success(__('Schedule updated successfully!', 'ai-auto-post-image-generator'));
            } else {
                wp_send_json_error(__('Error updating schedule. Please try again.', 'ai-auto-post-image-generator'));
            }
        } else {
            $result = aiapg()->schedule_manager->create_schedule($schedule_data);
            if ($result) {
                wp_send_json_success(__('Schedule created successfully!', 'ai-auto-post-image-generator'));
            } else {
                wp_send_json_error(__('Error creating schedule. Please try again.', 'ai-auto-post-image-generator'));
            }
        }
    }


    /**
     * Get schedule via AJAX
     */
    public function get_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = isset($_POST['schedule_id']) ? intval(wp_unslash($_POST['schedule_id'])) : 0;
        if(!$schedule_id) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }

        $schedule = aiapg()->schedule_manager->get_schedule($schedule_id);

        if ($schedule) {
            // Unserialize categories and custom_prompts before sending to frontend
            if (!empty($schedule->categories)) {
                $schedule->categories = maybe_unserialize($schedule->categories);
                if (!is_array($schedule->categories)) {
                    $schedule->categories = array();
                }
            } else {
                $schedule->categories = array();
            }

            if (!empty($schedule->custom_prompts)) {
                $schedule->custom_prompts = maybe_unserialize($schedule->custom_prompts);
                if (!is_array($schedule->custom_prompts)) {
                    $schedule->custom_prompts = array();
                }
            } else {
                $schedule->custom_prompts = array();
            }

            wp_send_json_success($schedule);
        } else {
            wp_send_json_error(__('Schedule not found.', 'ai-auto-post-image-generator'));
        }
    }

    /**
     * Delete schedule via AJAX
     */
    public function delete_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = isset($_POST['schedule_id']) ? intval(wp_unslash($_POST['schedule_id'])) : 0;
        if(!$schedule_id) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }

        $result = aiapg()->schedule_manager->delete_schedule($schedule_id);

        if ($result['success']) {
            wp_send_json_success(__('Schedule deleted successfully!', 'ai-auto-post-image-generator'));
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Run schedule via AJAX
     */
    public function run_schedule() {
        try {
            // Check nonce
            if (!check_ajax_referer('aiapg_nonce', 'nonce', false)) {
                wp_send_json_error('Invalid nonce');
                return;
            }

            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Insufficient permissions');
                return;
            }

            // Validate schedule_id
            $schedule_id = intval(wp_unslash($_POST['schedule_id'] ?? 0));
            if ($schedule_id <= 0) {
                wp_send_json_error('Invalid schedule ID');
                return;
            }

            // Check if scheduler class exists
            if (!class_exists('AIAPG_Scheduler')) {
                wp_send_json_error('Scheduler class not found');
                return;
            }

            // Check if aiapg() function exists
            if (!function_exists('aiapg')) {
                wp_send_json_error('Plugin main function not found');
                return;
            }

            // Get scheduler instance
            $scheduler = aiapg()->scheduler;
            if (!$scheduler) {
                wp_send_json_error('Scheduler instance not available');
                return;
            }

            // Run the schedule
            $result = $scheduler->run_schedule($schedule_id);

            // Ensure result is an array
            if (!is_array($result)) {
                $result = array(
                    'success' => false,
                    'message' => 'Invalid result format from scheduler'
                );
            }

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result['message'] ?? 'Unknown error occurred');
            }

        } catch (Exception $e) {
            
            wp_send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            
            wp_send_json_error('Fatal error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Toggle schedule status via AJAX
     */
    public function toggle_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce') || check_ajax_referer('aiapg_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = isset($_POST['schedule_id']) ? intval(wp_unslash($_POST['schedule_id'])) : 0;
        $is_active = isset($_POST['is_active']) ? intval(wp_unslash($_POST['is_active'])) : 0;

        if(!$schedule_id) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }

        $result = aiapg()->schedule_manager->toggle_schedule($schedule_id, $is_active);

        if ($result['success']) {
            wp_send_json_success(__('Schedule status updated successfully!', 'ai-auto-post-image-generator'));
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Get log details via AJAX
     */
    public function get_log_details() {
        check_ajax_referer('aiapg_nonce', 'nonce') || check_ajax_referer('aiapg_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $log_id = intval(wp_unslash($_POST['log_id'] ?? 0));

        if ($log_id <= 0) {
            wp_send_json_error(__('Invalid log ID.', 'ai-auto-post-image-generator'));
        }

        global $wpdb;
        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $log = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT l.*, s.name as schedule_name 
                 FROM " . esc_sql($table_logs) . " l 
                 LEFT JOIN " . esc_sql($table_schedules) . " s ON l.schedule_id = s.id 
                 WHERE l.id = %d",
                $log_id
            )
        );

        if (!$log) {
            wp_send_json_error(__('Log not found.', 'ai-auto-post-image-generator'));
        }

        // Format the details for display
        $details = maybe_unserialize($log->details);
        
        ob_start();
        ?>
        <div class="log-details-content">
            <table class="widefat">
                <tr>
                    <th><?php esc_html_e('Schedule:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html($log->schedule_name ?: __('Unknown', 'ai-auto-post-image-generator')); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Run Time:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $log->run_time)); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Status:', 'ai-auto-post-image-generator'); ?></th>
                    <td>
                        <span class="status-badge <?php echo esc_attr($log->status); ?>">
                            <?php echo esc_html(ucfirst($log->status)); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Posts Created:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html($log->posts_created); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Text Model:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html($log->text_model ?: __('Not specified', 'ai-auto-post-image-generator')); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Image Model:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html($log->image_model ?: __('Not specified', 'ai-auto-post-image-generator')); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Message:', 'ai-auto-post-image-generator'); ?></th>
                    <td><?php echo esc_html($log->message ?: __('No message', 'ai-auto-post-image-generator')); ?></td>
                </tr>
                
                <?php 
                // Display image generation details if available
                if (is_array($details) && isset($details['image_generation'])): 
                    $image_gen = $details['image_generation'];
                    $regular_images = is_array($image_gen['regular_images'] ?? null) ? $image_gen['regular_images'] : array();
                    $shortcode_images = is_array($image_gen['shortcode_images'] ?? null) ? $image_gen['shortcode_images'] : array();
                ?>
                <tr>
                    <th><?php esc_html_e('Image Generation:', 'ai-auto-post-image-generator'); ?></th>
                    <td>
                        <?php if (!empty($regular_images) || !empty($shortcode_images)): ?>
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">
                                
                                <?php if (!empty($regular_images)): ?>
                                <h4 style="margin: 0 0 10px 0; color: #23282d;"><?php esc_html_e('Featured/Regular Images:', 'ai-auto-post-image-generator'); ?></h4>
                                <div style="margin-bottom: 15px;">
                                    <strong><?php esc_html_e('Requested:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($regular_images['requested_images'] ?? 0); ?><br>
                                    <strong><?php esc_html_e('Successful:', 'ai-auto-post-image-generator'); ?></strong> <span style="color: #46b450;"><?php echo esc_html($regular_images['successful_images'] ?? 0); ?></span><br>
                                    <strong><?php esc_html_e('Failed:', 'ai-auto-post-image-generator'); ?></strong> <span style="color: #dc3232;"><?php echo esc_html($regular_images['failed_images'] ?? 0); ?></span><br>
                                    <strong><?php esc_html_e('Model Used:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($regular_images['model_used'] ?? __('Unknown', 'ai-auto-post-image-generator')); ?>
                                    
                                    <?php if (!empty($regular_images['errors'])): ?>
                                    <details style="margin-top: 10px;">
                                        <summary style="cursor: pointer; color: #dc3232; font-weight: bold;"><?php esc_html_e('Error Details', 'ai-auto-post-image-generator'); ?></summary>
                                        <div style="margin-top: 5px; padding: 8px; background: #ffeaea; border-radius: 3px;">
                                            <?php foreach ($regular_images['errors'] as $error): ?>
                                                <div style="margin-bottom: 8px; padding: 5px; background: white; border-radius: 3px;">
                                                    <strong><?php esc_html_e('Image', 'ai-auto-post-image-generator'); ?> <?php echo esc_html($error['image_index']); ?>:</strong><br>
                                                    <strong><?php esc_html_e('Model:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($error['model']); ?><br>
                                                    <strong><?php esc_html_e('Error:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($error['error']); ?><br>
                                                    <strong><?php esc_html_e('Title:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($error['title']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($shortcode_images)): ?>
                                <h4 style="margin: 0 0 10px 0; color: #23282d;"><?php esc_html_e('Inline/Shortcode Images:', 'ai-auto-post-image-generator'); ?></h4>
                                <div>
                                    <strong><?php esc_html_e('Requested:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($shortcode_images['requested'] ?? 0); ?><br>
                                    <strong><?php esc_html_e('Successful:', 'ai-auto-post-image-generator'); ?></strong> <span style="color: #46b450;"><?php echo esc_html($shortcode_images['successful'] ?? 0); ?></span><br>
                                    <strong><?php esc_html_e('Failed:', 'ai-auto-post-image-generator'); ?></strong> <span style="color: #dc3232;"><?php echo esc_html($shortcode_images['failed'] ?? 0); ?></span>
                                    
                                    <?php if (!empty($shortcode_images['errors'])): ?>
                                    <details style="margin-top: 10px;">
                                        <summary style="cursor: pointer; color: #dc3232; font-weight: bold;"><?php esc_html_e('Error Details', 'ai-auto-post-image-generator'); ?></summary>
                                        <div style="margin-top: 5px; padding: 8px; background: #ffeaea; border-radius: 3px;">
                                            <?php foreach ($shortcode_images['errors'] as $error): ?>
                                                <div style="margin-bottom: 8px; padding: 5px; background: white; border-radius: 3px;">
                                                    <strong><?php esc_html_e('Prompt:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html(substr($error['prompt'], 0, 100) . (strlen($error['prompt']) > 100 ? '...' : '')); ?><br>
                                                    <strong><?php esc_html_e('Model:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($error['model']); ?><br>
                                                    <strong><?php esc_html_e('Error:', 'ai-auto-post-image-generator'); ?></strong> <?php echo esc_html($error['error']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span style="color: #666;"><?php esc_html_e('No image generation requested', 'ai-auto-post-image-generator'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if (is_array($details) && !empty($details)): ?>
                <tr>
                    <th><?php esc_html_e('Raw Details:', 'ai-auto-post-image-generator'); ?></th>
                    <td>
                        <details>
                            <summary style="cursor: pointer; color: #0073aa; font-weight: bold;"><?php esc_html_e('Show Raw JSON Data', 'ai-auto-post-image-generator'); ?></summary>
                            <pre style="background: #f1f1f1; padding: 10px; border-radius: 4px; overflow-x: auto; white-space: pre-wrap; margin-top: 10px;"><?php echo esc_html(wp_json_encode($details, JSON_PRETTY_PRINT)); ?></pre>
                        </details>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php
        $html = ob_get_clean();

        wp_send_json_success($html);
    }

    /**
     * Test API key via AJAX
     */
    public function test_api_key() {
        check_ajax_referer('aiapg_nonce', 'nonce') || check_ajax_referer('aiapg_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        // Support both parameter names for compatibility
        $provider = sanitize_text_field(wp_unslash($_POST['provider'] ?? $_POST['api_service'] ?? ''));
        $api_key = sanitize_text_field(wp_unslash($_POST['api_key'] ?? ''));

        if (empty($api_key)) {
            wp_send_json_error(__('Please enter an API key to test.', 'ai-auto-post-image-generator'));
        }

        $result = $this->test_api_connection($provider, $api_key);

        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Test API connection
     *
     * @param string $provider
     * @param string $api_key
     * @return array
     */
    private function test_api_connection($provider, $api_key) {
        switch ($provider) {
            case 'openai':
                return $this->test_openai_api($api_key);
            case 'gemini':
                return $this->test_gemini_api($api_key);
            case 'leonardo':
                return $this->test_leonardo_api($api_key);
            default:
                return array(
                    'success' => false,
                    'message' => __('Unknown provider.', 'ai-auto-post-image-generator')
                );
        }
    }

    /**
     * Test OpenAI API
     *
     * @param string $api_key
     * @return array
     */
    private function test_openai_api($api_key) {
        // List models — validates the key without depending on a specific chat model ID.
        $response = wp_remote_get('https://api.openai.com/v1/models', array(
            'timeout' => 30,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
            ),
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($code >= 200 && $code < 300 && isset($data['data']) && is_array($data['data'])) {
            return array(
                'success' => true,
                'message' => __('OpenAI API connection successful!', 'ai-auto-post-image-generator')
            );
        }

        $error_message = isset($data['error']['message']) ? $data['error']['message'] : __('Unknown error occurred.', 'ai-auto-post-image-generator');
        return array(
            'success' => false,
            'message' => $error_message
        );
    }

    /**
     * Test Gemini API
     *
     * @param string $api_key
     * @return array
     */
    private function test_gemini_api($api_key) {
        $model = AIAPG_Utils::get_default_gemini_model();
        $candidate_models = array_unique(
            array_filter(
                array(
                    $model,
                    'gemini-3.5-flash',
                    'gemini-flash-latest',
                    'gemini-3.1-flash-lite',
                )
            )
        );

        $last_error = __('Unknown error occurred.', 'ai-auto-post-image-generator');

        foreach ($candidate_models as $candidate_model) {
            $response = AIAPG_Utils::make_post_request(
                'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($candidate_model) . ':generateContent?key=' . rawurlencode($api_key),
                array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                    'body' => wp_json_encode(
                        array(
                            'contents' => array(
                                array(
                                    'parts' => array(
                                        array(
                                            'text' => 'Hello',
                                        ),
                                    ),
                                ),
                            ),
                            'generationConfig' => array(
                                'maxOutputTokens' => 10,
                            ),
                        )
                    ),
                ),
                'Gemini API Test'
            );

            if (is_wp_error($response)) {
                $last_error = $response->get_error_message();
                continue;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($status_code === 200 && AIAPG_Utils::extract_gemini_text_from_response($data) !== '') {
                return array(
                    'success' => true,
                    'message' => sprintf(
                        /* translators: %s: Gemini model ID that succeeded */
                        __('Gemini API connection successful! (tested with %s)', 'ai-auto-post-image-generator'),
                        $candidate_model
                    ),
                );
            }

            if (isset($data['error']['message'])) {
                $last_error = $data['error']['message'];
            } elseif ($status_code !== 200) {
                $last_error = sprintf(
                    /* translators: 1: HTTP status code, 2: model id */
                    __('HTTP %1$d while testing model %2$s', 'ai-auto-post-image-generator'),
                    $status_code,
                    $candidate_model
                );
            }
        }

        return array(
            'success' => false,
            'message' => $last_error,
        );
    }

    /**
     * Fetch live Gemini models via AJAX
     */
    public function fetch_gemini_models() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $force = !empty($_POST['force']);
        $result = AIAPG_Utils::fetch_gemini_models_from_api('', (bool) $force);

        if (empty($result['success'])) {
            wp_send_json_error($result['message'] ?? __('Could not fetch Gemini models.', 'ai-auto-post-image-generator'));
        }

        wp_send_json_success(
            array(
                'models'  => $result['models'],
                'message' => sprintf(
                    /* translators: %d: number of models */
                    __('Loaded %d Gemini models from Google.', 'ai-auto-post-image-generator'),
                    count($result['models'])
                ),
            )
        );
    }

    /**
     * Test Leonardo.AI API
     *
     * @param string $api_key
     * @return array
     */
    private function test_leonardo_api($api_key) {
        $response = AIAPG_Utils::make_get_request('https://cloud.leonardo.ai/api/rest/v1/me', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Accept'        => 'application/json',
            )
        ), 'Leonardo.AI API Test');

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Connection failed: ', 'ai-auto-post-image-generator') . $response->get_error_message()
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if ($status_code === 200 && isset($data['user_details'][0]['user']['id'])) {
            return array(
                'success' => true,
                'message' => __('Leonardo.AI API connection successful!', 'ai-auto-post-image-generator')
            );
        } else {
            $error_message = __('Invalid API key or connection failed.', 'ai-auto-post-image-generator');
            
            if (isset($data['error'])) {
                $error_message = $data['error'];
            } elseif (isset($data['message'])) {
                $error_message = $data['message'];
            } elseif (isset($data['detail'])) {
                $error_message = $data['detail'];
            } elseif ($status_code !== 200) {
                $error_message = sprintf(
                    /* translators: 1: HTTP status code, 2: HTTP response message */
                    __('HTTP Error %1$d: %2$s', 'ai-auto-post-image-generator'),
                    $status_code,
                    wp_remote_retrieve_response_message($response)
                );
            }
            
            return array(
                'success' => false,
                'message' => $error_message
            );
        }
    }

    /**
     * Clear schedule lock via AJAX
     */
    public function clear_schedule_lock() {
        check_ajax_referer('aiapg_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = isset($_POST['schedule_id']) ? intval(wp_unslash($_POST['schedule_id'])) : 0;
        if(!$schedule_id) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }
        
        // Clear the lock manually
        $lock_key = 'aiapg_schedule_running_' . $schedule_id;
        delete_transient($lock_key);
        
        wp_send_json_success(array(
            'message' => __('Schedule lock cleared successfully. You can now run the schedule again.', 'ai-auto-post-image-generator')
        ));
    }

}
