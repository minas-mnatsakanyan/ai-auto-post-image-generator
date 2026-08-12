<?php
/**
 * Schedules View
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get categories for JavaScript
$categories = get_categories(array('hide_empty' => false));
$category_options = '';
foreach ($categories as $category) {
    $category_options .= '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
}

// The aiapg_get_available_models function is now available via the utility class

// Get available models
$available_models = aiapg_get_available_models();

// Get default settings for new schedules
$default_text_model = get_option('aiapg_default_text_model', 'gpt-3.5-turbo');
$default_image_model = get_option('aiapg_default_image_model', 'dall-e-2');
$default_fallback_image_model = get_option('aiapg_default_fallback_image_model', 'pollinations');

// Check if default models are available, if not, use first available or empty
$available_text_model_values = array_column($available_models['text_models'], 'value');
$available_image_model_values = array_column($available_models['image_models'], 'value');

if (!empty($available_text_model_values) && !in_array($default_text_model, $available_text_model_values)) {
    $default_text_model = $available_text_model_values[0];
} elseif (empty($available_text_model_values)) {
    $default_text_model = '';
}

if (!empty($available_image_model_values) && !in_array($default_image_model, $available_image_model_values)) {
    $default_image_model = $available_image_model_values[0];
} elseif (empty($available_image_model_values)) {
    $default_image_model = '';
}

if (!empty($available_image_model_values) && !in_array($default_fallback_image_model, $available_image_model_values)) {
    $default_fallback_image_model = $available_image_model_values[0];
} elseif (empty($available_image_model_values)) {
    $default_fallback_image_model = '';
}

$default_settings = array(
    'default_text_model' => $default_text_model,
    'default_image_model' => $default_image_model,
    'default_fallback_image_model' => $default_fallback_image_model,
    'posts_per_run' => get_option('aiapg_default_posts_per_run', 1),
    'content_length' => get_option('aiapg_default_content_length', 'long'),
    'post_status' => AIAPG_Utils::get_default_post_status(),
    'enable_images' => get_option('aiapg_default_enable_images', 1),
    'image_placement' => get_option('aiapg_default_image_placement', 'featured'),
    'image_size' => get_option('aiapg_default_image_size', '1024x1024'),
    'images_per_post' => get_option('aiapg_default_images_per_post', 1)
);
?>

<div class="wrap aiapg-schedules">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php esc_html_e('Schedule Manager', 'ai-auto-post-image-generator'); ?>
    </h1>
    <a href="#" class="page-title-action" id="aiapg-add-schedule">
        <?php esc_html_e('Add New Schedule', 'ai-auto-post-image-generator'); ?>
    </a>

    <!-- Schedule List -->
    <div class="aiapg-schedule-list">
        <?php if (!empty($schedules)) : ?>
            <?php foreach ($schedules as $schedule) : ?>
                <div class="aiapg-schedule-card" data-schedule-id="<?php echo esc_attr($schedule->id); ?>">
                    <div class="schedule-header">
                        <div class="schedule-title">
                            <h3><?php echo esc_html($schedule->name); ?></h3>
                            <?php if ($schedule->is_active) : ?>
                                <span class="schedule-status active">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php esc_html_e('Active', 'ai-auto-post-image-generator'); ?>
                                </span>
                            <?php else : ?>
                                <span class="schedule-status inactive">
                                    <span class="dashicons dashicons-no-alt"></span>
                                    <?php esc_html_e('Inactive', 'ai-auto-post-image-generator'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="schedule-actions">
                            <button class="button button-secondary run-schedule" data-schedule-id="<?php echo esc_attr($schedule->id); ?>">
                                <span class="dashicons dashicons-controls-play"></span>
                                <?php esc_html_e('Run Now', 'ai-auto-post-image-generator'); ?>
                            </button>
                            <button class="button button-secondary clear-schedule-lock" data-schedule-id="<?php echo esc_attr($schedule->id); ?>" title="<?php esc_attr_e('Use only when a previous run timed out or is stuck.', 'ai-auto-post-image-generator'); ?>">
                                <span class="dashicons dashicons-unlock"></span>
                                <?php esc_html_e('Clear Stuck Lock', 'ai-auto-post-image-generator'); ?>
                            </button>
                            <button class="button button-secondary edit-schedule" data-schedule-id="<?php echo esc_attr($schedule->id); ?>">
                                <span class="dashicons dashicons-edit"></span>
                                <?php esc_html_e('Edit', 'ai-auto-post-image-generator'); ?>
                            </button>
                            <button class="button button-link-delete delete-schedule" data-schedule-id="<?php echo esc_attr($schedule->id); ?>">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Delete', 'ai-auto-post-image-generator'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="schedule-content">
                        <?php if (!empty($schedule->description)) : ?>
                            <p class="aiapg-schedule-description"><?php echo esc_html($schedule->description); ?></p>
                        <?php endif; ?>

                        <div class="schedule-details">
                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Posts per run:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value"><?php echo esc_html($schedule->posts_per_run); ?></span>
                            </div>
                            <?php
                            $length_presets = AIAPG_Post_Generator::get_content_length_presets();
                            $schedule_length = !empty($schedule->content_length) ? $schedule->content_length : 'long';
                            $length_label = isset($length_presets[$schedule_length]['label'])
                                ? $length_presets[$schedule_length]['label']
                                : $schedule_length;
                            ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Content length:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value"><?php echo esc_html($length_label); ?></span>
                            </div>

                            <?php
                            $status_choices = AIAPG_Utils::get_post_status_choices();
                            $schedule_status = !empty($schedule->post_status)
                                ? AIAPG_Utils::normalize_post_status($schedule->post_status)
                                : AIAPG_Utils::get_default_post_status();
                            $status_label = isset($status_choices[$schedule_status])
                                ? $status_choices[$schedule_status]
                                : $schedule_status;
                            ?>
                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Post status:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value"><?php echo esc_html($status_label); ?></span>
                            </div>

                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Text Model:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value"><?php echo esc_html($schedule->text_model); ?></span>
                            </div>

                            <?php if ($schedule->enable_images) : ?>
                                <div class="detail-item">
                                    <span class="detail-label"><?php esc_html_e('Image Model:', 'ai-auto-post-image-generator'); ?></span>
                                    <span class="detail-value"><?php echo esc_html($schedule->image_model); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label"><?php esc_html_e('Image Placement:', 'ai-auto-post-image-generator'); ?></span>
                                    <span class="detail-value"><?php echo esc_html(ucfirst($schedule->image_placement)); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Interval:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value">
                                    <?php
                                    $interval_text = '';
                                    switch ($schedule->interval_type) {
                                        case 'hourly':
                                            $interval_text = sprintf(
                                                /* translators: %d: Number of hours */
                                                _n('Every %d hour', 'Every %d hours', $schedule->interval_value, 'ai-auto-post-image-generator'),
                                                $schedule->interval_value
                                            );
                                            break;
                                        case 'daily':
                                            $interval_text = sprintf(
                                                /* translators: %d: Number of days */
                                                _n('Every %d day', 'Every %d days', $schedule->interval_value, 'ai-auto-post-image-generator'),
                                                $schedule->interval_value
                                            );
                                            break;
                                        case 'weekly':
                                            $interval_text = sprintf(
                                                /* translators: %d: Number of weeks */
                                                _n('Every %d week', 'Every %d weeks', $schedule->interval_value, 'ai-auto-post-image-generator'),
                                                $schedule->interval_value
                                            );
                                            break;
                                        case 'monthly':
                                            $interval_text = sprintf(
                                                /* translators: %d: Number of months */
                                                _n('Every %d month', 'Every %d months', $schedule->interval_value, 'ai-auto-post-image-generator'),
                                                $schedule->interval_value
                                            );
                                            break;
                                        case 'custom':
                                            $interval_text = esc_html__('Custom Cron', 'ai-auto-post-image-generator');
                                            break;
                                        case 'once':
                                            $once_at = !empty($schedule->scheduled_at) ? $schedule->scheduled_at : $schedule->next_run;
                                            $interval_text = $once_at
                                                ? sprintf(
                                                    /* translators: %s: scheduled datetime */
                                                    __('Once at %s', 'ai-auto-post-image-generator'),
                                                    $once_at
                                                )
                                                : __('Once (specific date & time)', 'ai-auto-post-image-generator');
                                            break;
                                    }
                                    echo esc_html($interval_text);
                                    ?>
                                </span>
                            </div>

                            <?php if ($schedule->last_run) : ?>
                                <div class="detail-item">
                                    <span class="detail-label"><?php esc_html_e('Last run:', 'ai-auto-post-image-generator'); ?></span>
                                    <span class="detail-value">
                                        <?php echo esc_html(human_time_diff(strtotime($schedule->last_run), current_time('timestamp'))); ?>
                                        <?php esc_html_e('ago', 'ai-auto-post-image-generator'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php if ($schedule->next_run) : ?>
                                <div class="detail-item">
                                    <span class="detail-label"><?php esc_html_e('Next run:', 'ai-auto-post-image-generator'); ?></span>
                                    <span class="detail-value">
                                        <?php echo esc_html(human_time_diff(current_time('timestamp'), strtotime($schedule->next_run))); ?>
                                        <?php esc_html_e('from now', 'ai-auto-post-image-generator'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <?php
                            // Get content source info for details
                            $categories = !empty($schedule->categories) ? maybe_unserialize($schedule->categories) : array();
                            $custom_prompts = !empty($schedule->custom_prompts) ? maybe_unserialize($schedule->custom_prompts) : array();
                            
                            // Ensure categories is an array
                            if (!is_array($categories)) {
                                $categories = array();
                            }
                            if (!is_array($custom_prompts)) {
                                $custom_prompts = array();
                            }
                            ?>

                            <div class="detail-item">
                                <span class="detail-label"><?php esc_html_e('Content Source:', 'ai-auto-post-image-generator'); ?></span>
                                <span class="detail-value">
                                    <?php if (!empty($categories)) : ?>
                                        <span class="aiapg-source-type categories">
                                            <span class="dashicons dashicons-category"></span>
                                            <?php esc_html_e('Categories', 'ai-auto-post-image-generator'); ?>
                                        </span>
                                    <?php elseif (!empty($custom_prompts)) : ?>
                                        <span class="aiapg-source-type prompts">
                                            <span class="dashicons dashicons-edit"></span>
                                            <?php esc_html_e('Custom Prompts', 'ai-auto-post-image-generator'); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="aiapg-source-type fallback">
                                            <span class="dashicons dashicons-admin-generic"></span>
                                            <?php esc_html_e('Fallback Topics', 'ai-auto-post-image-generator'); ?>
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($categories)) : ?>
                            <div class="aiapg-schedule-categories">
                                <span class="detail-label"><?php esc_html_e('Selected Categories:', 'ai-auto-post-image-generator'); ?></span>
                                <div class="aiapg-category-tags">
                                    <?php foreach ($categories as $category_id) : ?>
                                        <?php if (!empty($category_id)) : ?>
                                            <?php $category = get_term($category_id, 'category'); ?>
                                            <?php if ($category && !is_wp_error($category)) : ?>
                                                <span class="aiapg-category-tag">
                                                    <span class="dashicons dashicons-category"></span>
                                                    <?php echo esc_html($category->name); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($custom_prompts)) : ?>
                            <div class="aiapg-schedule-prompts">
                                <span class="detail-label"><?php esc_html_e('Custom Prompts:', 'ai-auto-post-image-generator'); ?></span>
                                <div class="aiapg-prompt-list">
                                    <?php foreach (array_slice($custom_prompts, 0, 3) as $index => $prompt_data) : ?>
                                        <div class="aiapg-prompt-item">
                                            <span class="aiapg-prompt-number"><?php echo esc_html($index + 1); ?>.</span>
                                            <div class="aiapg-prompt-content">
                                                <?php 
                                                // Handle both old format (string) and new format (object)
                                                if (is_string($prompt_data)) {
                                                    echo esc_html($prompt_data);
                                                } else if (is_array($prompt_data) && isset($prompt_data['text'])) {
                                                    echo esc_html($prompt_data['text']);
                                                    
                                                    // Show categories if they exist
                                                    if (!empty($prompt_data['categories']) && is_array($prompt_data['categories'])) {
                                                        $prompt_categories = array();
                                                        foreach ($prompt_data['categories'] as $cat_id) {
                                                            $category = get_category($cat_id);
                                                            if ($category && !is_wp_error($category)) {
                                                                $prompt_categories[] = $category->name;
                                                            }
                                                        }
                                                        if (!empty($prompt_categories)) {
                                                            echo '<div class="aiapg-prompt-categories">';
                                                            echo '<span class="aiapg-category-label">' . esc_html__('Assigned to:', 'ai-auto-post-image-generator') . '</span> ';
                                                            echo '<span class="aiapg-category-list">' . esc_html(implode(', ', $prompt_categories)) . '</span>';
                                                            echo '</div>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($custom_prompts) > 3) : ?>
                                        <div class="aiapg-prompt-more">
                                            <?php
                                            printf(
                                                /* translators: %d: number of additional prompts */
                                                esc_html__('+%d more prompts', 'ai-auto-post-image-generator'),
                                                count($custom_prompts) - 3
                                            );
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($categories) && empty($custom_prompts)) : ?>
                            <div class="aiapg-schedule-warning">
                                <div class="warning-message">
                                    <span class="dashicons dashicons-warning"></span>
                                    <strong><?php esc_html_e('No Content Source Configured', 'ai-auto-post-image-generator'); ?></strong>
                                    <p><?php esc_html_e('This schedule has no categories or custom prompts configured. It will use fallback topics, but you should edit it to select categories or add custom prompts for better results.', 'ai-auto-post-image-generator'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="aiapg-no-schedules">
                <div class="no-schedules-icon">
                    <span class="dashicons dashicons-calendar-alt"></span>
                </div>
                <h3><?php esc_html_e('No Schedules Found', 'ai-auto-post-image-generator'); ?></h3>
                <p><?php esc_html_e('Create your first schedule to start automatically generating posts.', 'ai-auto-post-image-generator'); ?></p>
                <a href="#" class="button button-primary" id="aiapg-add-first-schedule">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Create Your First Schedule', 'ai-auto-post-image-generator'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="aiapg-support-banner aiapg-support-banner--footer" role="complementary" aria-label="<?php esc_attr_e('Support the plugin', 'ai-auto-post-image-generator'); ?>">
        <span class="aiapg-support-banner__emoji" aria-hidden="true">&#x1F916;</span>
        <span class="aiapg-support-banner__text"><?php esc_html_e('If this plugin saves you time, a small tip helps keep it maintained and improving.', 'ai-auto-post-image-generator'); ?></span>
        <a class="aiapg-support-banner__btn" href="<?php echo esc_url('https://buymeacoffee.com/ai.auto.post.and.image'); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Buy me a coffee', 'ai-auto-post-image-generator'); ?>
        </a>
    </div>

    <!-- Schedule Modal -->
    <div id="aiapg-schedule-modal" class="aiapg-modal">
        <div class="aiapg-modal-content">
            <div class="aiapg-modal-header">
                <h2 id="modal-title"><?php esc_html_e('Add New Schedule', 'ai-auto-post-image-generator'); ?></h2>
                <button class="aiapg-modal-close">&times;</button>
            </div>
            <div class="aiapg-modal-body">
                <form id="aiapg-schedule-form">
                    <?php wp_nonce_field('aiapg_nonce', 'aiapg_nonce'); ?>
                    <input type="hidden" id="schedule_id" name="schedule_id" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="schedule_name"><?php esc_html_e('Schedule Name *', 'ai-auto-post-image-generator'); ?></label>
                            <input type="text" id="schedule_name" name="name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="schedule_description"><?php esc_html_e('Description', 'ai-auto-post-image-generator'); ?></label>
                            <textarea id="schedule_description" name="description" rows="3"></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><?php esc_html_e('Content Source', 'ai-auto-post-image-generator'); ?></label>
                            <div class="content-source-options">
                                <label>
                                    <input type="radio" name="content_source" value="categories" checked>
                                    <?php esc_html_e('By Categories', 'ai-auto-post-image-generator'); ?>
                                </label>
                                <label>
                                    <input type="radio" name="content_source" value="prompts">
                                    <?php esc_html_e('By Custom Prompts', 'ai-auto-post-image-generator'); ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row" id="categories-section">
                        <div class="form-group">
                            <label for="schedule_categories"><?php esc_html_e('Select Categories', 'ai-auto-post-image-generator'); ?></label>
                            <select id="schedule_categories" name="categories[]" multiple>
                                <?php
                                $categories = get_categories(array('hide_empty' => false));
                                foreach ($categories as $category) :
                                ?>
                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row" id="prompts-section" style="display: none;">
                        <div class="form-group">
                            <label for="schedule_prompts"><?php esc_html_e('Custom Prompts', 'ai-auto-post-image-generator'); ?></label>
                            <div id="prompts-container">
                                <div class="prompt-input">
                                    <div class="prompt-content">
                                        <textarea name="custom_prompts[0][text]" placeholder="<?php esc_attr_e('Enter a prompt for content generation...', 'ai-auto-post-image-generator'); ?>"></textarea>
                                        <div class="prompt-categories">
                                            <label><?php esc_html_e('Assign posts to categories:', 'ai-auto-post-image-generator'); ?></label>
                                            <select name="custom_prompts[0][categories][]" multiple class="prompt-category-select">
                                                <?php
                                                $categories = get_categories(array('hide_empty' => false));
                                                foreach ($categories as $category) :
                                                ?>
                                                    <option value="<?php echo esc_attr($category->term_id); ?>">
                                                        <?php echo esc_html($category->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="prompt-publish-at">
                                            <label for="custom_prompts_0_publish_at"><?php esc_html_e('Publish at specific date & time (optional):', 'ai-auto-post-image-generator'); ?></label>
                                            <input type="datetime-local" id="custom_prompts_0_publish_at" name="custom_prompts[0][publish_at]" class="prompt-publish-at-input">
                                            <p class="description"><?php esc_html_e('Creates a WordPress scheduled post at this date/time. Leave empty to use the default post status.', 'ai-auto-post-image-generator'); ?></p>
                                        </div>
                                    </div>
                                    <button type="button" class="remove-prompt" style="display: none;">&times;</button>
                                </div>
                            </div>
                            <button type="button" class="button button-secondary" id="add-prompt" style="display: none;">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <?php esc_html_e('Add Prompt', 'ai-auto-post-image-generator'); ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label for="posts_per_run"><?php esc_html_e('Posts per Run', 'ai-auto-post-image-generator'); ?></label>
                            <input type="number" id="posts_per_run" name="posts_per_run" min="1" max="10" value="<?php echo esc_attr($default_settings['posts_per_run']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="post_status"><?php esc_html_e('Post Status', 'ai-auto-post-image-generator'); ?></label>
                            <select id="post_status" name="post_status">
                                <?php foreach (AIAPG_Utils::get_post_status_choices() as $status_value => $status_label) : ?>
                                    <option value="<?php echo esc_attr($status_value); ?>" <?php selected($default_settings['post_status'], $status_value); ?>>
                                        <?php echo esc_html($status_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Published goes live immediately. Choose Draft to review first.', 'ai-auto-post-image-generator'); ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="content_length"><?php esc_html_e('Generated Content Length', 'ai-auto-post-image-generator'); ?></label>
                            <select id="content_length" name="content_length">
                                <?php
                                $content_length_presets = AIAPG_Post_Generator::get_content_length_presets();
                                $default_content_length = get_option('aiapg_default_content_length', 'long');
                                foreach ($content_length_presets as $length_key => $length_preset) :
                                    ?>
                                    <option value="<?php echo esc_attr($length_key); ?>" <?php selected($default_content_length, $length_key); ?>>
                                        <?php echo esc_html($length_preset['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Controls how long each generated post should be.', 'ai-auto-post-image-generator'); ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="text_model"><?php esc_html_e('Text Model', 'ai-auto-post-image-generator'); ?></label>
                            <?php if (!empty($available_models['text_models'])) : ?>
                                <select id="text_model" name="text_model">
                                    <?php
                                    $available_text_models = $available_models['text_models'];
                                    foreach ($available_text_models as $model) {
                                        ?>
                                        <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($default_settings['default_text_model'], $model['value']); ?>>
                                            <?php echo esc_html($model['label']); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <div id="custom-text-model-group" class="aiapg-custom-model-group" style="display: none; margin-top: 8px;">
                                    <label for="custom_text_model"><?php esc_html_e('Custom model ID', 'ai-auto-post-image-generator'); ?></label>
                                    <input type="text" id="custom_text_model" name="custom_text_model" placeholder="gpt-5.4-mini or gemini-3.5-flash" autocomplete="off">
                                    <p class="description"><?php esc_html_e('Enter any Gemini model ID supported by your API key (Google changes these frequently).', 'ai-auto-post-image-generator'); ?></p>
                                </div>
                                <?php if (!empty(get_option('aiapg_gemini_api_key'))) : ?>
                                    <p class="description" style="margin-top: 8px;">
                                        <button type="button" class="button button-secondary" id="aiapg-refresh-gemini-models">
                                            <?php esc_html_e('Refresh Gemini models from API', 'ai-auto-post-image-generator'); ?>
                                        </button>
                                    </p>
                                <?php endif; ?>
                                <p class="description">
                                    <strong><?php esc_html_e('Cost tip:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('Free: Gemini 3.1 Flash-Lite. Paid OpenAI: GPT-5.4 nano or GPT-4o mini for lowest cost. One post can use several text calls (title, content, meta).', 'ai-auto-post-image-generator'); ?>
                                </p>
                            <?php else : ?>
                                <div class="aiapg-no-models-message">
                                    <p class="aiapg-no-models-text">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('No text models available. Please configure API keys in settings.', 'ai-auto-post-image-generator'); ?>
                                    </p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <?php esc_html_e('Configure API Keys', 'ai-auto-post-image-generator'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($available_models['text_models_require_keys'])) : ?>
                                <div class="aiapg-models-require-keys">
                                    <p class="description">
                                        <span class="dashicons dashicons-info"></span>
                                        <?php 
                                        $providers = array_unique(array_column($available_models['text_models_require_keys'], 'provider'));
                                        printf(
                                            /* translators: 1: Comma-separated list of providers, 2: Settings page URL */
                                            esc_html__('Additional models available: %1$s. <a href="%2$s">Configure API keys</a>', 'ai-auto-post-image-generator'),
                                            esc_html(implode(', ', $providers)),
                                            esc_url(admin_url('admin.php?page=aiapg-settings'))
                                        );
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="enable_images" name="enable_images" <?php checked($default_settings['enable_images'], 1); ?>>
                                <label for="enable_images"><?php esc_html_e('Enable Image Generation', 'ai-auto-post-image-generator'); ?></label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row image-settings" id="image-settings">
                        <div class="form-group">
                            <label for="image_model"><?php esc_html_e('Image Model', 'ai-auto-post-image-generator'); ?></label>
                            <?php if (!empty($available_models['image_models'])) : ?>
                                <select id="image_model" name="image_model">
                                    <?php
                                    $available_image_models = $available_models['image_models'];
                                    foreach ($available_image_models as $model) {
                                        ?>
                                        <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($default_settings['default_image_model'], $model['value']); ?>>
                                            <?php echo esc_html($model['label']); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="description">
                                    <strong><?php esc_html_e('Free tokens tip:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('For free/cheap images use Pollinations. Gemini image models are paid (free image quota is often 0).', 'ai-auto-post-image-generator'); ?>
                                </p>
                            <?php else : ?>
                                <div class="aiapg-no-models-message">
                                    <p class="aiapg-no-models-text">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('No image models available. Please configure API keys in settings.', 'ai-auto-post-image-generator'); ?>
                                    </p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <?php esc_html_e('Configure API Keys', 'ai-auto-post-image-generator'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($available_models['image_models_require_keys'])) : ?>
                                <div class="aiapg-models-require-keys">
                                    <p class="description">
                                        <span class="dashicons dashicons-info"></span>
                                        <?php 
                                        $providers = array_unique(array_column($available_models['image_models_require_keys'], 'provider'));
                                        printf(
                                            /* translators: 1: Comma-separated list of providers, 2: Settings page URL */
                                            esc_html__('Additional models available: %1$s. <a href="%2$s">Configure API keys</a>', 'ai-auto-post-image-generator'),
                                            esc_html(implode(', ', $providers)),
                                            esc_url(admin_url('admin.php?page=aiapg-settings'))
                                        );
                                        ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row image-settings">
                        <div class="form-group">
                            <label for="fallback_image_model"><?php esc_html_e('Fallback Image Model', 'ai-auto-post-image-generator'); ?></label>
                            <?php if (!empty($available_models['image_models'])) : ?>
                                <select id="fallback_image_model" name="fallback_image_model">
                                    <?php
                                    $available_fallback_models = $available_models['image_models'];
                                    foreach ($available_fallback_models as $model) {
                                        ?>
                                        <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($default_settings['default_fallback_image_model'], $model['value']); ?>>
                                            <?php echo esc_html($model['label']); ?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <p class="description"><?php esc_html_e('Used if the primary image model fails', 'ai-auto-post-image-generator'); ?></p>
                            <?php else : ?>
                                <div class="aiapg-no-models-message">
                                    <p class="aiapg-no-models-text">
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e('No fallback image models available. Please configure API keys in settings.', 'ai-auto-post-image-generator'); ?>
                                    </p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-secondary">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <?php esc_html_e('Configure API Keys', 'ai-auto-post-image-generator'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row two-columns image-settings">
                        <div class="form-group">
                            <label for="image_placement">
                                <?php esc_html_e('Image Placement', 'ai-auto-post-image-generator'); ?>
                                <span class="dashicons dashicons-info" style="color: #0073aa; cursor: help;" title="<?php echo esc_attr(esc_html__('"Featured Image Only" will hide the "Images per Post" field since only one image is needed.', 'ai-auto-post-image-generator')); ?>"></span>
                            </label>
                            <select id="image_placement" name="image_placement">
                                <option value="featured" <?php selected($default_settings['image_placement'], 'featured'); ?>><?php esc_html_e('Featured Image Only', 'ai-auto-post-image-generator'); ?></option>
                                <option value="inline" <?php selected($default_settings['image_placement'], 'inline'); ?>><?php esc_html_e('Inline Images Only', 'ai-auto-post-image-generator'); ?></option>
                                <option value="both" <?php selected($default_settings['image_placement'], 'both'); ?>><?php esc_html_e('Featured + Inline', 'ai-auto-post-image-generator'); ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="image_size"><?php esc_html_e('Image Size', 'ai-auto-post-image-generator'); ?></label>
                            <select id="image_size" name="image_size">
                                <option value="512x512" <?php selected($default_settings['image_size'], '512x512'); ?>>512x512</option>
                                <option value="1024x1024" <?php selected($default_settings['image_size'], '1024x1024'); ?>>1024x1024</option>
                                <option value="1792x1024" <?php selected($default_settings['image_size'], '1792x1024'); ?>>1792x1024</option>
                                <option value="1024x1792" <?php selected($default_settings['image_size'], '1024x1792'); ?>>1024x1792</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row image-settings">
                        <div class="form-group">
                            <label for="images_per_post"><?php esc_html_e('Images per Post', 'ai-auto-post-image-generator'); ?></label>
                            <input type="number" id="images_per_post" name="images_per_post" min="1" max="5" value="<?php echo esc_attr($default_settings['images_per_post']); ?>">
                            <p class="description" id="images-per-post-description">
                                <?php esc_html_e('Number of images to generate for each post. Hidden when "Featured Image Only" is selected.', 'ai-auto-post-image-generator'); ?>
                            </p>
                        </div>
                    </div>

                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label for="interval_type"><?php esc_html_e('Interval Type', 'ai-auto-post-image-generator'); ?></label>
                            <select id="interval_type" name="interval_type">
                                <option value="hourly"><?php esc_html_e('Hourly', 'ai-auto-post-image-generator'); ?></option>
                                <option value="daily" selected><?php esc_html_e('Daily', 'ai-auto-post-image-generator'); ?></option>
                                <option value="weekly"><?php esc_html_e('Weekly', 'ai-auto-post-image-generator'); ?></option>
                                <option value="monthly"><?php esc_html_e('Monthly', 'ai-auto-post-image-generator'); ?></option>
                                <option value="once"><?php esc_html_e('Once (specific date & time)', 'ai-auto-post-image-generator'); ?></option>
                                <option value="custom"><?php esc_html_e('Custom Cron', 'ai-auto-post-image-generator'); ?></option>
                            </select>
                        </div>

                        <div class="form-group" id="interval-value-group">
                            <label for="interval_value"><?php esc_html_e('Interval Value', 'ai-auto-post-image-generator'); ?></label>
                            <input type="number" id="interval_value" name="interval_value" min="1" value="1">
                        </div>
                    </div>

                    <div class="form-row" id="scheduled-at-group" style="display: none;">
                        <div class="form-group">
                            <label for="scheduled_at"><?php esc_html_e('Run at specific date & time', 'ai-auto-post-image-generator'); ?></label>
                            <input type="datetime-local" id="scheduled_at" name="scheduled_at">
                            <p class="description"><?php esc_html_e('The schedule will generate posts once at this date and time (site timezone), then deactivate.', 'ai-auto-post-image-generator'); ?></p>
                        </div>
                    </div>

                    <div class="form-row" id="custom-cron-group" style="display: none;">
                        <div class="form-group">
                            <label for="custom_cron"><?php esc_html_e('Custom Cron Schedule Name', 'ai-auto-post-image-generator'); ?></label>
                            <input type="text" id="custom_cron" name="custom_cron" placeholder="hourly">
                            <p class="description"><?php esc_html_e('Enter a registered WordPress cron schedule slug (e.g. hourly, daily, or a custom schedule registered by another plugin).', 'ai-auto-post-image-generator'); ?></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <label for="is_active"><?php esc_html_e('Active', 'ai-auto-post-image-generator'); ?></label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="aiapg-modal-footer">
                <button type="button" class="button button-secondary" id="cancel-schedule"><?php esc_html_e('Cancel', 'ai-auto-post-image-generator'); ?></button>
                <button type="button" class="button button-primary" id="save-schedule"><?php esc_html_e('Save Schedule', 'ai-auto-post-image-generator'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
// Make category options available to JavaScript
window.aiapgCategoryOptions = <?php echo json_encode($category_options); ?>;
</script>
