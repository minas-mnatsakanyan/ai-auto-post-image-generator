<?php
/**
 * Schedule Manager Class
 *
 * Handles CRUD operations for schedules and manages the scheduling system.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Schedule_Manager Class
 *
 * @since 1.0.0
 */
class AIAPG_Schedule_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_aiapg_delete_schedule', array($this, 'delete_schedule'));
        add_action('wp_ajax_aiapg_toggle_schedule', array($this, 'toggle_schedule'));
        add_filter('cron_schedules', array($this, 'register_cron_schedules'));
    }

    /**
     * Register custom WP-Cron intervals used by the plugin.
     *
     * @param array $schedules
     * @return array
     */
    public function register_cron_schedules($schedules) {
        if (!isset($schedules['monthly'])) {
            $schedules['monthly'] = array(
                'interval' => MONTH_IN_SECONDS,
                'display'  => __('Once Monthly', 'ai-auto-post-image-generator'),
            );
        }

        // Support every N hours/days when interval_value > 1.
        for ($hours = 2; $hours <= 24; $hours++) {
            $key = 'aiapg_every_' . $hours . '_hours';
            if (!isset($schedules[$key])) {
                $schedules[$key] = array(
                    'interval' => $hours * HOUR_IN_SECONDS,
                    'display'  => sprintf(
                        /* translators: %d: Number of hours */
                        __('Every %d hours', 'ai-auto-post-image-generator'),
                        $hours
                    ),
                );
            }
        }

        for ($days = 2; $days <= 30; $days++) {
            $key = 'aiapg_every_' . $days . '_days';
            if (!isset($schedules[$key])) {
                $schedules[$key] = array(
                    'interval' => $days * DAY_IN_SECONDS,
                    'display'  => sprintf(
                        /* translators: %d: Number of days */
                        __('Every %d days', 'ai-auto-post-image-generator'),
                        $days
                    ),
                );
            }
        }

        return $schedules;
    }

    /**
     * Get all schedules
     *
     * @return array
     */
    public function get_all_schedules() {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            "SELECT * FROM " . esc_sql($table_schedules) . " ORDER BY created_at DESC"
        );
    }

    /**
     * Get schedule by ID
     *
     * @param int $schedule_id
     * @return object|null
     */
    public function get_schedule($schedule_id) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM " . esc_sql($table_schedules) . " WHERE id = %d",
                $schedule_id
            )
        );
    }

    /**
     * Create new schedule
     *
     * @param array $data
     * @return int|false
     */
    public function create_schedule($data) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        $next_run        = $this->calculate_next_run($data);

        $insert_data = array(
            'name'                 => $data['name'],
            'description'          => $data['description'],
            'categories'           => maybe_serialize($data['categories']),
            'custom_prompts'       => maybe_serialize($data['custom_prompts']),
            'posts_per_run'        => $data['posts_per_run'],
            'text_model'           => $data['text_model'],
            'image_model'          => $data['image_model'],
            'fallback_image_model' => $data['fallback_image_model'] ?? get_option('aiapg_default_fallback_image_model', 'pollinations'),
            'enable_images'        => $data['enable_images'] ? 1 : 0,
            'image_placement'      => $data['image_placement'],
            'image_size'           => $data['image_size'],
            'images_per_post'      => $data['images_per_post'],
            'content_length'       => !empty($data['content_length']) ? $data['content_length'] : 'long',
            'post_status'          => AIAPG_Utils::normalize_post_status(
                $data['post_status'] ?? '',
                AIAPG_Utils::get_default_post_status()
            ),
            'interval_type'        => $data['interval_type'],
            'interval_value'       => $data['interval_value'],
            'custom_cron'          => $data['custom_cron'],
            'scheduled_at'         => !empty($data['scheduled_at']) ? $data['scheduled_at'] : null,
            'is_active'            => $data['is_active'] ? 1 : 0,
            'next_run'             => $next_run,
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $result = $wpdb->insert($table_schedules, $insert_data);

        if ($result) {
            $schedule_id = $wpdb->insert_id;

            if ($data['is_active']) {
                $this->schedule_cron_event($schedule_id, $data);
            }

            return $schedule_id;
        }

        return false;
    }

    /**
     * Update existing schedule
     *
     * @param int $schedule_id
     * @param array $data
     * @return bool
     */
    public function update_schedule($schedule_id, $data) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        $next_run        = $this->calculate_next_run($data);

        $update_data = array(
            'name'                 => $data['name'],
            'description'          => $data['description'],
            'categories'           => maybe_serialize($data['categories']),
            'custom_prompts'       => maybe_serialize($data['custom_prompts']),
            'posts_per_run'        => $data['posts_per_run'],
            'text_model'           => $data['text_model'],
            'image_model'          => $data['image_model'],
            'fallback_image_model' => $data['fallback_image_model'] ?? get_option('aiapg_default_fallback_image_model', 'pollinations'),
            'enable_images'        => $data['enable_images'] ? 1 : 0,
            'image_placement'      => $data['image_placement'],
            'image_size'           => $data['image_size'],
            'images_per_post'      => $data['images_per_post'],
            'content_length'       => !empty($data['content_length']) ? $data['content_length'] : 'long',
            'post_status'          => AIAPG_Utils::normalize_post_status(
                $data['post_status'] ?? '',
                AIAPG_Utils::get_default_post_status()
            ),
            'interval_type'        => $data['interval_type'],
            'interval_value'       => $data['interval_value'],
            'custom_cron'          => $data['custom_cron'],
            'scheduled_at'         => !empty($data['scheduled_at']) ? $data['scheduled_at'] : null,
            'is_active'            => $data['is_active'] ? 1 : 0,
            'next_run'             => $next_run,
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            $table_schedules,
            $update_data,
            array('id' => $schedule_id)
        );

        if ($result !== false) {
            wp_clear_scheduled_hook('aiapg_schedule_run', array($schedule_id));

            if ($data['is_active']) {
                $this->schedule_cron_event($schedule_id, $data);
            }

            return true;
        }

        return false;
    }

    /**
     * Delete schedule via AJAX
     */
    public function delete_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = intval($_POST['schedule_id'] ?? 0);

        if ($schedule_id <= 0) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }

        $result = $this->delete_schedule_by_id($schedule_id);

        if ($result) {
            wp_send_json_success(__('Schedule deleted successfully!', 'ai-auto-post-image-generator'));
        } else {
            wp_send_json_error(__('Error deleting schedule. Please try again.', 'ai-auto-post-image-generator'));
        }
    }

    /**
     * Delete schedule by ID
     *
     * @param int $schedule_id
     * @return bool
     */
    public function delete_schedule_by_id($schedule_id) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        wp_clear_scheduled_hook('aiapg_schedule_run', array($schedule_id));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->delete(
            $table_schedules,
            array('id' => $schedule_id),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Toggle schedule status via AJAX
     */
    public function toggle_schedule() {
        check_ajax_referer('aiapg_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ai-auto-post-image-generator'));
        }

        $schedule_id = intval($_POST['schedule_id'] ?? 0);
        $is_active = isset($_POST['is_active']) ? (bool) $_POST['is_active'] : false;

        if ($schedule_id <= 0) {
            wp_send_json_error(__('Invalid schedule ID.', 'ai-auto-post-image-generator'));
        }

        $result = $this->toggle_schedule_status($schedule_id, $is_active);

        if ($result) {
            $status_text = $is_active ? __('activated', 'ai-auto-post-image-generator') : __('deactivated', 'ai-auto-post-image-generator');
            wp_send_json_success(sprintf(
                /* translators: %s: Status text (activated/deactivated) */
                __('Schedule %s successfully!', 'ai-auto-post-image-generator'),
                $status_text
            ));
        } else {
            wp_send_json_error(__('Error updating schedule status. Please try again.', 'ai-auto-post-image-generator'));
        }
    }

    /**
     * Toggle schedule status
     *
     * @param int $schedule_id
     * @param bool $is_active
     * @return bool
     */
    public function toggle_schedule_status($schedule_id, $is_active) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        $schedule        = $this->get_schedule($schedule_id);

        if (!$schedule) {
            return false;
        }

        $update_data = array(
            'is_active' => $is_active ? 1 : 0,
        );

        if ($is_active) {
            $schedule_data = array(
                'interval_type'  => $schedule->interval_type,
                'interval_value' => $schedule->interval_value,
                'custom_cron'    => $schedule->custom_cron,
                'scheduled_at'   => isset($schedule->scheduled_at) ? $schedule->scheduled_at : '',
            );
            $update_data['next_run'] = $this->calculate_next_run($schedule_data);
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            $table_schedules,
            $update_data,
            array('id' => $schedule_id)
        );

        if ($result !== false) {
            wp_clear_scheduled_hook('aiapg_schedule_run', array($schedule_id));

            if ($is_active) {
                $this->schedule_cron_event($schedule_id, (array) $schedule);
            }

            return true;
        }

        return false;
    }

    /**
     * Calculate next run time
     *
     * @param array $data
     * @return string|null
     */
    public function calculate_next_run($data) {
        $interval_type  = isset($data['interval_type']) ? $data['interval_type'] : 'daily';
        $interval_value = max(1, absint($data['interval_value'] ?? 1));
        $now            = current_time('timestamp');

        if ($interval_type === 'once') {
            $scheduled_at = AIAPG_Utils::normalize_datetime($data['scheduled_at'] ?? '');
            if ($scheduled_at === '') {
                return null;
            }
            return $scheduled_at;
        }

        switch ($interval_type) {
            case 'hourly':
                $next_run = $now + ($interval_value * HOUR_IN_SECONDS);
                break;
            case 'daily':
                $next_run = $now + ($interval_value * DAY_IN_SECONDS);
                break;
            case 'weekly':
                $next_run = $now + ($interval_value * WEEK_IN_SECONDS);
                break;
            case 'monthly':
                $next_run = $now + ($interval_value * MONTH_IN_SECONDS);
                break;
            case 'custom':
                $next_run = $now + DAY_IN_SECONDS;
                break;
            default:
                $next_run = $now + DAY_IN_SECONDS;
        }

        // Store as wall-clock datetime consistent with current_time('timestamp') usage elsewhere.
        return gmdate('Y-m-d H:i:s', $next_run);
    }

    /**
     * Resolve WP-Cron recurrence slug for recurring schedules.
     *
     * @param array $data
     * @return string|false
     */
    private function get_recurrence_slug($data) {
        $interval_type  = $data['interval_type'] ?? 'daily';
        $interval_value = max(1, absint($data['interval_value'] ?? 1));

        switch ($interval_type) {
            case 'hourly':
                if ($interval_value === 1) {
                    return 'hourly';
                }
                if ($interval_value <= 24) {
                    return 'aiapg_every_' . $interval_value . '_hours';
                }
                return 'hourly';
            case 'daily':
                if ($interval_value === 1) {
                    return 'daily';
                }
                if ($interval_value <= 30) {
                    return 'aiapg_every_' . $interval_value . '_days';
                }
                return 'daily';
            case 'weekly':
                return 'weekly';
            case 'monthly':
                return 'monthly';
            case 'custom':
                return !empty($data['custom_cron']) ? $data['custom_cron'] : false;
            default:
                return false;
        }
    }

    /**
     * Schedule cron event
     *
     * @param int $schedule_id
     * @param array $data
     */
    private function schedule_cron_event($schedule_id, $data) {
        $hook = 'aiapg_schedule_run';
        $args = array($schedule_id);

        if (($data['interval_type'] ?? '') === 'once') {
            $timestamp = AIAPG_Utils::local_datetime_to_gmt_timestamp($data['scheduled_at'] ?? '');
            if ($timestamp && $timestamp > time()) {
                wp_schedule_single_event($timestamp, $hook, $args);
            } elseif ($timestamp && $timestamp <= time()) {
                // Already due — run on next page load via single event ASAP.
                wp_schedule_single_event(time() + 5, $hook, $args);
            }
            return;
        }

        $next_run = $this->calculate_next_run($data);
        if (empty($next_run)) {
            return;
        }

        $next_run_timestamp = AIAPG_Utils::local_datetime_to_gmt_timestamp($next_run);
        if (!$next_run_timestamp) {
            $next_run_timestamp = time() + DAY_IN_SECONDS;
        }

        $recurrence = $this->get_recurrence_slug($data);
        if ($recurrence) {
            wp_schedule_event($next_run_timestamp, $recurrence, $hook, $args);
        }
    }

    /**
     * Mark a one-time schedule as completed (inactive, no next run).
     *
     * @param int $schedule_id
     * @return bool
     */
    public function mark_once_schedule_complete($schedule_id) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        wp_clear_scheduled_hook('aiapg_schedule_run', array($schedule_id));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . esc_sql($table_schedules) . " SET is_active = 0, next_run = NULL, last_run = %s WHERE id = %d",
                current_time('mysql'),
                $schedule_id
            )
        );

        return $result !== false;
    }

    /**
     * Import schedules
     *
     * @param array $schedules
     * @return bool
     */
    public function import_schedules($schedules) {
        if (!is_array($schedules)) {
            return false;
        }

        foreach ($schedules as $schedule) {
            unset($schedule['id']);
            unset($schedule['created_at']);
            unset($schedule['updated_at']);
            unset($schedule['last_run']);
            unset($schedule['next_run']);

            $this->create_schedule($schedule);
        }

        return true;
    }

    /**
     * Get schedules that are due to run
     *
     * @return array
     */
    public function get_due_schedules() {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';
        $now = current_time('mysql');

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM " . esc_sql($table_schedules) . " 
                 WHERE is_active = 1 
                 AND (next_run IS NULL OR next_run <= %s)",
                $now
            )
        );
    }

    /**
     * Update last run time
     *
     * @param int $schedule_id
     * @param string|null $next_run
     * @return bool
     */
    public function update_last_run($schedule_id, $next_run = null) {
        global $wpdb;

        $table_schedules = $wpdb->prefix . 'aiapg_schedules';

        $update_data = array(
            'last_run' => current_time('mysql'),
        );

        if ($next_run !== null) {
            $update_data['next_run'] = $next_run;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->update(
            $table_schedules,
            $update_data,
            array('id' => $schedule_id)
        );

        return $result !== false;
    }
}
