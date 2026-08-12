<?php
/**
 * Scheduler Class
 *
 * Handles the cron job execution and logging.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Scheduler Class
 *
 * @since 1.0.0
 */
class AIAPG_Scheduler {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('aiapg_schedule_run', array($this, 'run_schedule_cron'), 10, 1);
        // Removed wp_loaded hook to prevent auto-execution when saving schedules
        // Schedules now only run via cron jobs or manual "Run Now" button
    }

    /**
     * Run schedule via cron
     *
     * @param int $schedule_id
     */
    public function run_schedule_cron($schedule_id) {
        $this->run_schedule($schedule_id);
    }

    /**
     * Check for due schedules (manual method - no longer auto-called)
     * 
     * This method can be called manually if needed, but is no longer 
     * automatically triggered to prevent auto-execution when saving schedules.
     */
    public function check_due_schedules() {
        $due_schedules = aiapg()->schedule_manager->get_due_schedules();
        
        foreach ($due_schedules as $schedule) {
            $this->run_schedule($schedule->id);
        }
    }

    /**
     * Run a schedule
     *
     * @param int $schedule_id
     * @return array
     */
    public function run_schedule($schedule_id) {
        $result = array(
            'success' => false,
            'posts_created' => 0,
            'message' => ''
        );

        try {
            // Debug logging at start
            
            
            // Check if this schedule is already running (prevent race conditions)
            $lock_key = 'aiapg_schedule_running_' . $schedule_id;
            if (get_transient($lock_key)) {
                $result['message'] = __('Schedule is already running. Please wait for it to complete.', 'ai-auto-post-image-generator');
                
                return $result;
            }

            // Set a lock to prevent concurrent execution (expires in 5 minutes)
            set_transient($lock_key, true, 300);
            

            // Get schedule
            $schedule = aiapg()->schedule_manager->get_schedule($schedule_id);
            
            
            if (!$schedule) {
                $result['message'] = __('Schedule not found.', 'ai-auto-post-image-generator');
                delete_transient($lock_key);
                $this->log_run($schedule_id, $result);
                
                return $result;
            }

            if (!$schedule->is_active) {
                $result['message'] = __('Schedule is not active.', 'ai-auto-post-image-generator');
                delete_transient($lock_key);
                $this->log_run($schedule_id, $result);
                
                return $result;
            }

            // Check if API keys are configured
            $text_model = $schedule->text_model;
            $api_key_configured = false;
            
            if (!empty($text_model) && strpos($text_model, 'gpt') === 0) {
                $api_key_configured = !empty(get_option('aiapg_openai_api_key'));
            } elseif (!empty($text_model) && strpos($text_model, 'gemini') === 0) {
                $api_key_configured = !empty(get_option('aiapg_gemini_api_key'));
            } else {
                // Assume a model is selected, but no specific API key is required (e.g., if a future free model is added)
                $api_key_configured = true; 
            }

            

            if (!$api_key_configured) {
                $result['message'] = sprintf(
                    /* translators: %s: AI model name */
                    __('API key for %s is not configured. Please set it in plugin settings.', 'ai-auto-post-image-generator'),
                    $text_model
                );
                delete_transient($lock_key);
                $this->log_run($schedule_id, $result);
                
                return $result;
            }

            // Debug logging
            if (get_option('aiapg_enable_debug_log', false)) {
                
                
            }

            // Check if post generator exists
            if (!aiapg()->post_generator) {
                $result['message'] = 'Post generator not available';
                delete_transient($lock_key);
                $this->log_run($schedule_id, $result);
                
                return $result;
            }

            // Generate posts
            
            $generation_result = aiapg()->post_generator->generate_posts_for_schedule($schedule);
            
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            
            // Check if image generator exists
            if (!aiapg()->image_generator) {
                
                $image_stats = array(
                    'requested_images' => 0,
                    'successful_images' => 0,
                    'failed_images' => 0,
                    'errors' => array('Image generator not available'),
                    'model_used' => ''
                );
            } else {
                // Collect image generation statistics
                $image_stats = aiapg()->image_generator->get_last_generation_stats();
                
            }
            
            // Check if post generator exists for shortcode stats
            if (!aiapg()->post_generator) {
                
                $shortcode_stats = array(
                    'requested' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'errors' => array('Post generator not available')
                );
            } else {
                $shortcode_stats = aiapg()->post_generator->get_shortcode_image_stats();
                
            }
            
            // Ensure both are arrays
            if (!is_array($image_stats)) {
                $image_stats = array(
                    'requested_images' => 0,
                    'successful_images' => 0,
                    'failed_images' => 0,
                    'errors' => array('Invalid image stats format'),
                    'model_used' => ''
                );
            }
            
            if (!is_array($shortcode_stats)) {
                $shortcode_stats = array(
                    'requested' => 0,
                    'successful' => 0,
                    'failed' => 0,
                    'errors' => array('Invalid shortcode stats format')
                );
            }
            
            if ($generation_result['success']) {
                $result['success'] = true;
                $result['posts_created'] = $generation_result['posts_created'];
                $result['message'] = $generation_result['message'];
                
                // Add image generation details to result
                $result['image_generation'] = array(
                    'regular_images' => $image_stats,
                    'shortcode_images' => $shortcode_stats
                );
            } else {
                $result['message'] = $generation_result['message'];
                // Still include image stats even on failure
                $result['image_generation'] = array(
                    'regular_images' => $image_stats,
                    'shortcode_images' => $shortcode_stats
                );
            }

            // Update schedule last/next run. One-time schedules deactivate after a successful run.
            if (isset($schedule->interval_type) && $schedule->interval_type === 'once') {
                if (!empty($result['success'])) {
                    aiapg()->schedule_manager->mark_once_schedule_complete($schedule_id);
                } else {
                    aiapg()->schedule_manager->update_last_run($schedule_id, $schedule->next_run);
                }
            } else {
                $next_run = $this->calculate_next_run($schedule);
                aiapg()->schedule_manager->update_last_run($schedule_id, $next_run);
            }

        } catch (Exception $e) {
            
            
            $result['message'] = $e->getMessage();
        } catch (Error $e) {
            
            
            $result['message'] = 'Fatal error: ' . $e->getMessage();
        }

        // Release the lock
        delete_transient($lock_key);
        

        // Log the run
        $this->log_run($schedule_id, $result);
        

        return $result;
    }

    /**
     * Calculate next run time for a schedule
     *
     * @param object $schedule
     * @return string|null
     */
    private function calculate_next_run($schedule) {
        return aiapg()->schedule_manager->calculate_next_run(
            array(
                'interval_type'  => $schedule->interval_type,
                'interval_value' => $schedule->interval_value,
                'custom_cron'    => $schedule->custom_cron,
                'scheduled_at'   => isset($schedule->scheduled_at) ? $schedule->scheduled_at : '',
            )
        );
    }

    /**
     * Log a schedule run
     *
     * @param int $schedule_id
     * @param array $result
     */
    private function log_run($schedule_id, $result) {
        if (!get_option('aiapg_enable_logging', true)) {
            return;
        }

        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $schedule = aiapg()->schedule_manager->get_schedule($schedule_id);

        $log_data = array(
            'schedule_id' => $schedule_id,
            'posts_created' => $result['posts_created'],
            'text_model' => $schedule ? $schedule->text_model : '',
            'image_model' => $schedule ? $schedule->image_model : '',
            'status' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
            'details' => maybe_serialize($result)
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert($table_logs, $log_data);

        // Clean old logs
        $this->clean_old_logs();
    }

    /**
     * Clean old logs
     */
    private function clean_old_logs() {
        $retention_days = get_option('aiapg_log_retention_days', 30);
        
        if ($retention_days <= 0) {
            return;
        }

        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $cutoff_date = gmdate('Y-m-d H:i:s', strtotime("-{$retention_days} days"));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM " . esc_sql($table_logs) . " WHERE run_time < %s",
                $cutoff_date
            )
        );
    }



    /**
     * Get recent logs
     *
     * @param int $limit
     * @return array
     */
    public function get_recent_logs($limit = 20) {
        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';
        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT l.*, s.name as schedule_name 
                 FROM " . esc_sql($table_logs) . " l 
                 LEFT JOIN " . esc_sql($table_schedules) . " s ON l.schedule_id = s.id 
                 ORDER BY l.run_time DESC 
                 LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Get log statistics
     *
     * @return array
     */
    public function get_log_statistics() {
        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';

        $stats = array(
            'total_runs' => 0,
            'successful_runs' => 0,
            'failed_runs' => 0,
            'total_posts_created' => 0,
            'average_posts_per_run' => 0,
            'last_run' => null
        );

        // Get basic counts
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $stats['total_runs'] = $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($table_logs));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $stats['successful_runs'] = $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($table_logs) . " WHERE status = 'success'");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $stats['failed_runs'] = $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($table_logs) . " WHERE status = 'error'");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $stats['total_posts_created'] = $wpdb->get_var("SELECT SUM(posts_created) FROM " . esc_sql($table_logs) . " WHERE status = 'success'");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $stats['last_run'] = $wpdb->get_var("SELECT MAX(run_time) FROM " . esc_sql($table_logs));

        // Calculate average posts per run
        if ($stats['successful_runs'] > 0) {
            $stats['average_posts_per_run'] = round($stats['total_posts_created'] / $stats['successful_runs'], 2);
        }

        return $stats;
    }





    /**
     * Clear all logs
     */
    public function clear_all_logs() {
        global $wpdb;

        $table_logs = $wpdb->prefix . 'aiapg_logs';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query("TRUNCATE TABLE " . esc_sql($table_logs));
    }


}
