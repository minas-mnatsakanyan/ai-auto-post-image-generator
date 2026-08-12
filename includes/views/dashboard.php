<?php
/**
 * Dashboard View
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap aiapg-dashboard">
    <div class="aiapg-support-banner" role="complementary" aria-label="<?php esc_attr_e('Support the plugin', 'ai-auto-post-image-generator'); ?>">
        <span class="aiapg-support-banner__emoji" aria-hidden="true">&#x1F916;</span>
        <span class="aiapg-support-banner__text"><?php esc_html_e('If this plugin saves you time, a small tip helps keep it maintained and improving.', 'ai-auto-post-image-generator'); ?></span>
        <a class="aiapg-support-banner__btn" href="<?php echo esc_url('https://buymeacoffee.com/ai.auto.post.and.image'); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Buy me a coffee', 'ai-auto-post-image-generator'); ?>
        </a>
    </div>

    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-generic"></span>
        <?php esc_html_e('AI Auto Post Generator Dashboard', 'ai-auto-post-image-generator'); ?>
    </h1>

    <div class="aiapg-stats-grid">
        <!-- Total Schedules -->
        <div class="aiapg-stat-card">
            <div class="stat-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['total_schedules']); ?></h3>
                <p><?php esc_html_e('Total Schedules', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <!-- Active Schedules -->
        <div class="aiapg-stat-card">
            <div class="stat-icon active">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['active_schedules']); ?></h3>
                <p><?php esc_html_e('Active Schedules', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <!-- Total Posts Created -->
        <div class="aiapg-stat-card">
            <div class="stat-icon success">
                <span class="dashicons dashicons-admin-post"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['total_posts_created'] ?: 0); ?></h3>
                <p><?php esc_html_e('Posts Created', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>

        <!-- Total Runs -->
        <div class="aiapg-stat-card">
            <div class="stat-icon info">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="stat-content">
                <h3><?php echo esc_html($stats['total_runs'] ?: 0); ?></h3>
                <p><?php esc_html_e('Total Runs', 'ai-auto-post-image-generator'); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="aiapg-quick-actions">
        <h2><?php esc_html_e('Quick Actions', 'ai-auto-post-image-generator'); ?></h2>
        <div class="aiapg-action-buttons">
            <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-schedules')); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php esc_html_e('Create New Schedule', 'ai-auto-post-image-generator'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-secondary">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php esc_html_e('Settings', 'ai-auto-post-image-generator'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-logs')); ?>" class="button button-secondary">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('View Logs', 'ai-auto-post-image-generator'); ?>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="aiapg-recent-activity">
        <h2><?php esc_html_e('Recent Activity', 'ai-auto-post-image-generator'); ?></h2>
        
        <?php if (!empty($stats['recent_logs']) && is_array($stats['recent_logs'])) : ?>
            <div class="aiapg-activity-list">
                <?php foreach ($stats['recent_logs'] as $log) : ?>
                    <?php if (is_object($log) && isset($log->status)) : ?>
                        <div class="aiapg-activity-item aiapg-schedule-<?php echo esc_attr($log->status); ?>">
                            <div class="activity-icon aiapg-schedule-<?php echo esc_attr($log->status); ?>">
                                <span class="dashicons dashicons-<?php echo $log->status === 'success' ? 'yes-alt' : 'warning'; ?>"></span>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <strong><?php echo esc_html(isset($log->schedule_name) ? $log->schedule_name : esc_html__('Unknown Schedule', 'ai-auto-post-image-generator')); ?></strong>
                                    <span class="activity-time">
                                        <?php echo esc_html(human_time_diff(strtotime($log->run_time), current_time('timestamp'))); ?>
                                        <?php esc_html_e('ago', 'ai-auto-post-image-generator'); ?>
                                    </span>
                                </div>
                                <div class="activity-details">
                                    <?php if ($log->status === 'success') : ?>
                                        <?php 
                                        printf(
                                            /* translators: 1: Number of posts created, 2: AI model name */
                                            esc_html__('Created %1$d posts using %2$s', 'ai-auto-post-image-generator'),
                                            intval($log->posts_created),
                                            esc_html($log->text_model)
                                        ); ?>
                                    <?php else : ?>
                                        <?php echo esc_html(isset($log->message) ? $log->message : esc_html__('Unknown error', 'ai-auto-post-image-generator')); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="aiapg-no-activity">
                <p><?php esc_html_e('No recent activity. Create your first schedule to get started!', 'ai-auto-post-image-generator'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- System Status -->
    <div class="aiapg-system-status">
        <h2><?php esc_html_e('System Status', 'ai-auto-post-image-generator'); ?></h2>
        <div class="aiapg-status-grid">
            <?php
            $openai_key = get_option('aiapg_openai_api_key');
            $gemini_key = get_option('aiapg_gemini_api_key');
            $leonardo_key = get_option('aiapg_leonardo_api_key');
            ?>
            
            <div class="aiapg-status-item">
                <span class="status-label"><?php esc_html_e('OpenAI API', 'ai-auto-post-image-generator'); ?></span>
                <span class="status-value <?php echo !empty($openai_key) ? 'connected' : 'disconnected'; ?>">
                    <?php echo !empty($openai_key) ? esc_html__('Connected', 'ai-auto-post-image-generator') : esc_html__('Not Configured', 'ai-auto-post-image-generator'); ?>
                </span>
            </div>

            <div class="aiapg-status-item">
                <span class="status-label"><?php esc_html_e('Gemini API', 'ai-auto-post-image-generator'); ?></span>
                <span class="status-value <?php echo !empty($gemini_key) ? 'connected' : 'disconnected'; ?>">
                    <?php echo !empty($gemini_key) ? esc_html__('Connected', 'ai-auto-post-image-generator') : esc_html__('Not Configured', 'ai-auto-post-image-generator'); ?>
                </span>
            </div>

            <div class="aiapg-status-item">
                <span class="status-label"><?php esc_html_e('Leonardo.AI API', 'ai-auto-post-image-generator'); ?></span>
                <span class="status-value <?php echo !empty($leonardo_key) ? 'connected' : 'disconnected'; ?>">
                    <?php echo !empty($leonardo_key) ? esc_html__('Connected', 'ai-auto-post-image-generator') : esc_html__('Not Configured', 'ai-auto-post-image-generator'); ?>
                </span>
            </div>

            <div class="aiapg-status-item">
                <span class="status-label"><?php esc_html_e('Pollinations API', 'ai-auto-post-image-generator'); ?></span>
                <span class="status-value connected">
                    <?php
                    echo AIAPG_Utils::is_using_default_pollinations_key()
                        ? esc_html__('Using Built-in Key', 'ai-auto-post-image-generator')
                        : esc_html__('Using Your Key', 'ai-auto-post-image-generator');
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Help & Support -->
    <div class="aiapg-help-support">
        <h2><?php esc_html_e('Need Help?', 'ai-auto-post-image-generator'); ?></h2>
        <div class="aiapg-help-links">
            <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-help')); ?>" class="button button-secondary">
                <span class="dashicons dashicons-editor-help"></span>
                <?php esc_html_e('Documentation', 'ai-auto-post-image-generator'); ?>
            </a>
            <a href="https://github.com/minas-mnatsakanyan/ai-auto-post-image-generator/issues" target="_blank" class="button button-secondary">
                <span class="dashicons dashicons-external"></span>
                <?php esc_html_e('GitHub Issues', 'ai-auto-post-image-generator'); ?>
            </a>
        </div>
    </div>
</div>
