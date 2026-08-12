<?php
/**
 * Plugin Name: AI Auto Post & Image Generator
 * Description: Automatically generate and publish WordPress posts with AI-generated content and images using OpenAI, Gemini, DALL·E, and other AI providers.
 * Version: 1.5.7
 * Author: AI Auto Post Generator
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ai-auto-post-image-generator
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 7.0
 * Requires PHP: 7.4
 *
 * @package AI_Auto_Post_Generator
 * @version 1.5.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AIAPG_VERSION', '1.5.7');
define('AIAPG_PLUGIN_FILE', __FILE__);
define('AIAPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIAPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AIAPG_PLUGIN_BASENAME', plugin_basename(__FILE__));
/** Built-in Pollinations key used when Settings field is empty. */
define('AIAPG_DEFAULT_POLLINATIONS_API_KEY', 'sk_PgIabugMiZ1uBWrP2CjptbA2JMpltSEM');

/**
 * Main AI Auto Post Generator Plugin Class
 *
 * @since 1.1.1
 */
final class AI_Auto_Post_Generator {

    /**
     * Plugin instance
     *
     * @var AI_Auto_Post_Generator
     */
    private static $instance = null;

    /**
     * Admin settings instance
     *
     * @var AIAPG_Admin_Settings
     */
    public $admin_settings;

    /**
     * Schedule manager instance
     *
     * @var AIAPG_Schedule_Manager
     */
    public $schedule_manager;

    /**
     * Post generator instance
     *
     * @var AIAPG_Post_Generator
     */
    public $post_generator;

    /**
     * Image generator instance
     *
     * @var AIAPG_Image_Generator
     */
    public $image_generator;

    /**
     * Scheduler instance
     *
     * @var AIAPG_Scheduler
     */
    public $scheduler;

    /**
     * Get plugin instance
     *
     * @return AI_Auto_Post_Generator
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Load required files
        require_once AIAPG_PLUGIN_DIR . 'includes/class-utils.php';
        require_once AIAPG_PLUGIN_DIR . 'includes/class-admin-settings.php';
        require_once AIAPG_PLUGIN_DIR . 'includes/class-schedule-manager.php';
        require_once AIAPG_PLUGIN_DIR . 'includes/class-post-generator.php';
        require_once AIAPG_PLUGIN_DIR . 'includes/class-image-generator.php';
        require_once AIAPG_PLUGIN_DIR . 'includes/class-scheduler.php';
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Ensure DB schema/options are up to date even when files are updated without reactivation.
        $this->check_version_updates();
        $this->ensure_schedule_table_columns();

        // Initialize components
        $this->admin_settings = new AIAPG_Admin_Settings();
        $this->schedule_manager = new AIAPG_Schedule_Manager();
        $this->post_generator = new AIAPG_Post_Generator();
        $this->image_generator = new AIAPG_Image_Generator();
        $this->scheduler = new AIAPG_Scheduler();

        // Register shortcodes
        add_shortcode('aiapg_image', array($this, 'handle_image_shortcode'));

    }

    /**
     * Handle image shortcode
     *
     * @param array $atts
     * @return string
     */
    public function handle_image_shortcode($atts) {
        // This shortcode is primarily for AI processing during post generation
        // If it appears in the frontend, we'll show a placeholder or nothing
        $atts = shortcode_atts(array(
            'prompt' => '',
        ), $atts);

        // In frontend, return empty string as images should already be processed
        if (!is_admin()) {
            return '';
        }

        // In admin/editor, show placeholder
        return sprintf(
            '<div class="aiapg-image-placeholder" style="background: #f0f0f0; border: 2px dashed #ccc; padding: 20px; text-align: center; margin: 10px 0;">
                <strong>AI Image Placeholder</strong><br>
                <em>Prompt: %s</em><br>
                <small>This will be replaced with a generated image when the post is published.</small>
            </div>',
            esc_html($atts['prompt'])
        );
    }



    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Check for version updates
        $this->check_version_updates();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('aiapg_schedule_run');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Check for version updates and run migrations
     */
    private function check_version_updates() {
        $current_version = get_option('aiapg_version', '1.0.0');
        $plugin_version = AIAPG_VERSION;
        
        if (version_compare($current_version, $plugin_version, '<')) {
            // Run version-specific updates
            $this->run_version_updates($current_version, $plugin_version);
            
            // Update stored version
            update_option('aiapg_version', $plugin_version);
        }
    }

    /**
     * Run version-specific updates
     */
    private function run_version_updates($from_version, $to_version) {
        // Update from 1.0.0 to 1.1.0
        if (version_compare($from_version, '1.1.0', '<')) {
            $this->update_to_1_1_0();
        }
        
        // Update from 1.1.0 to 1.1.1
        if (version_compare($from_version, '1.1.1', '<')) {
            $this->update_to_1_1_1();
        }

        // Ensure schema/options for 1.5.0 features
        if (version_compare($from_version, '1.5.0', '<')) {
            $this->update_to_1_5_0();
        }

        if (version_compare($from_version, '1.5.1', '<')) {
            $this->update_to_1_5_1();
        }

        if (version_compare($from_version, '1.5.4', '<')) {
            $this->update_to_1_5_4();
        }

        if (version_compare($from_version, '1.5.6', '<')) {
            $this->update_to_1_5_6();
        }

        if (version_compare($from_version, '1.5.7', '<')) {
            $this->update_to_1_5_7();
        }
    }

    /**
     * Update to version 1.1.0
     */
    private function update_to_1_1_0() {
        global $wpdb;
        
        // Migrate existing custom prompts from old format to new format
        $this->migrate_custom_prompts_to_new_format();
        
        // Update any other data structures if needed
        $this->update_schedule_data_structure();
    }

    /**
     * Migrate custom prompts from old string format to new object format
     */
    private function migrate_custom_prompts_to_new_format() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'aiapg_schedules';
        
        // Get all schedules with custom prompts
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration requires direct database access without caching
        $schedules = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, custom_prompts FROM %s WHERE custom_prompts IS NOT NULL AND custom_prompts != ''",
                $table_name
            )
        );
        
        if (empty($schedules)) {
            return;
        }
        
        $updated_count = 0;
        
        foreach ($schedules as $schedule) {
            $custom_prompts = maybe_unserialize($schedule->custom_prompts);
            
            // Skip if already in new format or empty
            if (empty($custom_prompts) || !is_array($custom_prompts)) {
                continue;
            }
            
            // Check if already in new format (has objects with 'text' key)
            $is_new_format = false;
            foreach ($custom_prompts as $prompt) {
                if (is_array($prompt) && isset($prompt['text'])) {
                    $is_new_format = true;
                    break;
                }
            }
            
            // Skip if already in new format
            if ($is_new_format) {
                continue;
            }
            
            // Convert old format (array of strings) to new format (array of objects)
            $new_custom_prompts = array();
            foreach ($custom_prompts as $prompt_text) {
                if (is_string($prompt_text) && !empty(trim($prompt_text))) {
                    $new_custom_prompts[] = array(
                        'text' => $prompt_text,
                        'categories' => array() // Empty categories for migrated prompts
                    );
                }
            }
            
            // Update the schedule with new format
            if (!empty($new_custom_prompts)) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Migration requires direct database access without caching
                $result = $wpdb->update(
                    $table_name,
                    array('custom_prompts' => maybe_serialize($new_custom_prompts)),
                    array('id' => $schedule->id),
                    array('%s'),
                    array('%d')
                );
                
                if ($result !== false) {
                    $updated_count++;
                }
            }
        }
    }

    /**
     * Update schedule data structure for version 1.1.0
     */
    private function update_schedule_data_structure() {
        // Add any additional data structure updates here
        // For now, the migration is handled by the custom prompts migration above
        
        // Future: Add any other structural changes needed for 1.1.0
        // For example: new database columns, new options, etc.
    }

    /**
     * Update to version 1.1.1
     */
    private function update_to_1_1_1() {
        // Version 1.1.1 is a minor update with bug fixes and improvements
        // No database migrations needed for this version
    }

    /**
     * Update to version 1.5.0 — scheduled_at column + Gemini model defaults
     */
    private function update_to_1_5_0() {
        $this->create_tables();
        $this->ensure_schedule_table_columns();

        $current_default = get_option('aiapg_default_text_model', '');
        if ($current_default === 'gemini-2.0-flash' || $current_default === 'gemini-pro') {
            update_option('aiapg_default_text_model', 'gemini-3.5-flash');
        }
    }

    /**
     * Update to version 1.5.1 — content_length column + default option
     */
    private function update_to_1_5_1() {
        $this->create_tables();
        $this->ensure_schedule_table_columns();

        if (get_option('aiapg_default_content_length') === false) {
            add_option('aiapg_default_content_length', 'long');
        }
    }

    /**
     * Update to version 1.5.4 — migrate Gemini 2.5 text models blocked for new API keys
     */
    private function update_to_1_5_4() {
        $retired = AIAPG_Utils::get_retired_gemini_text_models();
        $replacement = 'gemini-3.1-flash-lite';

        $current_default = get_option('aiapg_default_text_model', '');
        if (in_array($current_default, $retired, true)) {
            update_option('aiapg_default_text_model', $replacement);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'aiapg_schedules';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name));
        if ($table_exists !== $table_name) {
            return;
        }

        foreach ($retired as $old_model) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->update(
                $table_name,
                array('text_model' => $replacement),
                array('text_model' => $old_model),
                array('%s'),
                array('%s')
            );
        }
    }

    /**
     * Update to version 1.5.6 — selectable generated post status (publish by default)
     */
    private function update_to_1_5_6() {
        $this->create_tables();
        $this->ensure_schedule_table_columns();

        if (get_option('aiapg_post_status') === false) {
            add_option('aiapg_post_status', 'publish');
        }
    }

    /**
     * Update to version 1.5.7 — default generated post status is Published
     */
    private function update_to_1_5_7() {
        $this->ensure_schedule_table_columns();

        // Force Published as the global default (replaces temporary Draft default from 1.5.6).
        update_option('aiapg_post_status', 'publish');

        global $wpdb;
        $table_name = $wpdb->prefix . 'aiapg_schedules';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name));
        if ($table_exists !== $table_name) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $has_column = $wpdb->get_results(
            $wpdb->prepare('SHOW COLUMNS FROM `' . esc_sql($table_name) . '` LIKE %s', 'post_status')
        );
        if (empty($has_column)) {
            return;
        }

        // Normalize empty/null/draft schedule statuses to publish.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                'UPDATE `' . esc_sql($table_name) . '` SET post_status = %s WHERE post_status IS NULL OR post_status = %s OR post_status = %s',
                'publish',
                '',
                'draft'
            )
        );
    }

    /**
     * Ensure newer schedule columns exist (dbDelta can miss ALTER on some hosts).
     */
    private function ensure_schedule_table_columns() {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var(
            $wpdb->prepare('SHOW TABLES LIKE %s', $table_schedules)
        );

        if ($table_exists !== $table_schedules) {
            $this->create_tables();
            return;
        }

        $required_columns = array(
            'content_length' => 'ALTER TABLE `' . esc_sql($table_schedules) . '` ADD COLUMN content_length varchar(20) DEFAULT \'long\' AFTER images_per_post',
            'post_status'    => 'ALTER TABLE `' . esc_sql($table_schedules) . '` ADD COLUMN post_status varchar(20) DEFAULT \'publish\' AFTER content_length',
            'scheduled_at'   => 'ALTER TABLE `' . esc_sql($table_schedules) . '` ADD COLUMN scheduled_at datetime DEFAULT NULL AFTER custom_cron',
        );

        foreach ($required_columns as $column => $alter_sql) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $exists = $wpdb->get_results(
                $wpdb->prepare('SHOW COLUMNS FROM `' . esc_sql($table_schedules) . '` LIKE %s', $column)
            );

            if (empty($exists)) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->query($alter_sql);
            }
        }

        if (get_option('aiapg_default_content_length') === false) {
            add_option('aiapg_default_content_length', 'long');
        }

        if (get_option('aiapg_post_status') === false) {
            add_option('aiapg_post_status', 'publish');
        }
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Schedules table
        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        $sql_schedules = "CREATE TABLE $table_schedules (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            categories longtext,
            custom_prompts longtext,
            posts_per_run int(11) DEFAULT 1,
            text_model varchar(150) DEFAULT 'gpt-3.5-turbo',
            image_model varchar(100) DEFAULT 'dall-e-2',
            fallback_image_model varchar(100) DEFAULT 'pollinations',
            enable_images tinyint(1) DEFAULT 1,
            image_placement varchar(50) DEFAULT 'featured',
            image_size varchar(20) DEFAULT '1024x1024',
            images_per_post int(11) DEFAULT 1,
            content_length varchar(20) DEFAULT 'long',
            post_status varchar(20) DEFAULT 'publish',
            interval_type varchar(20) DEFAULT 'daily',
            interval_value int(11) DEFAULT 1,
            custom_cron varchar(255),
            scheduled_at datetime DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            last_run datetime DEFAULT NULL,
            next_run datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Logs table
        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            schedule_id mediumint(9),
            run_time datetime DEFAULT CURRENT_TIMESTAMP,
            posts_created int(11) DEFAULT 0,
            text_model varchar(100),
            image_model varchar(100),
            status varchar(20) DEFAULT 'success',
            message text,
            details longtext,
            PRIMARY KEY (id),
            KEY schedule_id (schedule_id),
            KEY run_time (run_time)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_schedules);
        dbDelta($sql_logs);
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $default_options = array(
            'openai_api_key' => '',
            'gemini_api_key' => '',
            'leonardo_api_key' => '',
            'pollinations_api_key' => '',
            'default_text_model' => 'gpt-3.5-turbo',
            'default_image_model' => 'dall-e-2',
            'default_fallback_image_model' => 'pollinations',
            'default_content_length' => 'long',
            'post_status' => 'publish',
            'setup_completed' => true,
            'version' => AIAPG_VERSION
        );

        foreach ($default_options as $key => $value) {
            if (get_option('aiapg_' . $key) === false) {
                add_option('aiapg_' . $key, $value);
            }
        }
    }

    /**
     * Check if setup is complete - always true since no wizard
     *
     * @return bool
     */
    public function is_setup_complete() {
        return true;
    }

    /**
     * Get plugin URL
     *
     * @param string $path
     * @return string
     */
    public function get_url($path = '') {
        return AIAPG_PLUGIN_URL . $path;
    }

    /**
     * Get plugin path
     *
     * @param string $path
     * @return string
     */
    public function get_path($path = '') {
        return AIAPG_PLUGIN_DIR . $path;
    }
}

/**
 * Get plugin instance
 *
 * @return AI_Auto_Post_Generator
 */
function aiapg() {
    return AI_Auto_Post_Generator::get_instance();
}

// Initialize plugin
aiapg();
