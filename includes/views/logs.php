<?php

/**
 * Logs View
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Get logs with pagination
$per_page     = 20;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset       = ($current_page - 1) * $per_page;



// Get logs
global $wpdb;



// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$total_logs = (int) $wpdb->get_var(
    "
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}aiapg_logs l
    LEFT JOIN {$wpdb->prefix}aiapg_schedules s ON l.schedule_id = s.id
    "
);

// --------------------
// Logs with pagination
// --------------------
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$logs = $wpdb->get_results(
    $wpdb->prepare(
        "
        SELECT l.*, s.name as schedule_name
        FROM {$wpdb->prefix}aiapg_logs l
        LEFT JOIN {$wpdb->prefix}aiapg_schedules s ON l.schedule_id = s.id
        ORDER BY l.run_time DESC
        LIMIT %d OFFSET %d
        ",
        $per_page,
        $offset
    )
);
// Calculate pagination
$total_pages    = ceil($total_logs / $per_page);
$pagination_args = array(
    'base'      => add_query_arg('paged', '%#%'),
    'format'    => '',
    'prev_text' => esc_html__('&laquo; Previous', 'ai-auto-post-image-generator'),
    'next_text' => esc_html__('Next &raquo;', 'ai-auto-post-image-generator'),
    'total'     => $total_pages,
    'current'   => $current_page,
);

// Get log statistics
$stats = array(
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    'total_runs'      => (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aiapg_logs"),
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    'successful_runs' => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aiapg_logs WHERE status = %s", 'success')),
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    'failed_runs'     => (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aiapg_logs WHERE status = %s", 'error')),
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    'total_posts'     => (int) $wpdb->get_var($wpdb->prepare("SELECT SUM(posts_created) FROM {$wpdb->prefix}aiapg_logs WHERE posts_created > %d", 0)),
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Statistics Cards -->
    <div class="aiapg-stats-grid">
        <div class="aiapg-stat-card">
            <div class="stat-icon dashicons dashicons-clock"></div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_runs']); ?></h3>
                <p><?php esc_html_e('Total Runs', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <div class="aiapg-stat-card success">
            <div class="stat-icon dashicons dashicons-yes-alt"></div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['successful_runs']); ?></h3>
                <p><?php esc_html_e('Successful Runs', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <div class="aiapg-stat-card failed-run">
            <div class="stat-icon dashicons dashicons-no-alt"></div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['failed_runs']); ?></h3>
                <p><?php esc_html_e('Failed Runs', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <div class="aiapg-stat-card">
            <div class="stat-icon dashicons dashicons-admin-post"></div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_posts'] ?: 0); ?></h3>
                <p><?php esc_html_e('Posts Created', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>
    </div>



    <!-- Logs Table -->
    <div class="aiapg-logs-table">
        <?php if (empty($logs)): ?>
            <div class="aiapg-no-logs">
                <div class="dashicons dashicons-info"></div>
                <h3><?php esc_html_e('No logs found', 'ai-auto-post-image-generator'); ?></h3>
                <p><?php esc_html_e('No activity logs match your current filters.', 'ai-auto-post-image-generator'); ?></p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date/Time', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Schedule', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Status', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Posts Created', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Models Used', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Duration', 'ai-auto-post-image-generator'); ?></th>
                        <th><?php esc_html_e('Actions', 'ai-auto-post-image-generator'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html(date_i18n('M j, Y', strtotime($log->run_time))); ?></strong><br>
                                <small><?php echo esc_html(date_i18n('g:i:s A', strtotime($log->run_time))); ?></small>
                            </td>
                            <td>
                                <?php if ($log->schedule_name): ?>
                                    <strong><?php echo esc_html($log->schedule_name); ?></strong>
                                <?php else: ?>
                                    <em><?php esc_html_e('Manual Run', 'ai-auto-post-image-generator'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="log-status log-status-<?php echo esc_attr($log->status); ?>">
                                    <?php if ($log->status === 'success'): ?>
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Success', 'ai-auto-post-image-generator'); ?>
                                    <?php else: ?>
                                        <span class="dashicons dashicons-no-alt"></span>
                                        <?php esc_html_e('Error', 'ai-auto-post-image-generator'); ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($log->posts_created > 0): ?>
                                    <span class="posts-count"><?php echo number_format($log->posts_created); ?></span>
                                <?php else: ?>
                                    <span class="posts-count zero">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="models-used">
                                    <?php if ($log->text_model): ?>
                                        <span class="model-badge text-model">
                                            <?php echo esc_html($log->text_model); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($log->image_model): ?>
                                        <span class="model-badge image-model">
                                            <?php echo esc_html($log->image_model); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $details = !empty($log->details) ? maybe_unserialize($log->details) : array();
                                if (!is_array($details)) {
                                    $details = array();
                                }
                                if (is_array($details) && isset($details['duration'])) {
                                    echo esc_html($details['duration']);
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="log-actions">
                                    <button type="button" class="button button-small view-log-details" data-log-id="<?php echo esc_attr($log->id); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                        <?php esc_html_e('Details', 'ai-auto-post-image-generator'); ?>
                                    </button>
                                    <?php if ($log->status === 'error'): ?>
                                        <button type="button" class="button button-small retry-schedule" data-schedule-id="<?php echo esc_attr($log->schedule_id); ?>">
                                            <span class="dashicons dashicons-update"></span>
                                            <?php esc_html_e('Retry', 'ai-auto-post-image-generator'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="aiapg-pagination">
                    <?php
                    echo wp_kses_post(paginate_links($pagination_args));
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Log Details Modal -->
<div id="log-details-modal" class="aiapg-modal">
    <div class="aiapg-modal-content">
        <div class="aiapg-modal-header">
            <h2><?php esc_html_e('Log Details', 'ai-auto-post-image-generator'); ?></h2>
            <button type="button" class="aiapg-modal-close">&times;</button>
        </div>
        <div class="aiapg-modal-body">
            <div id="log-details-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>
