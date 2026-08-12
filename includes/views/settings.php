<?php
/**
 * Settings View
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// The aiapg_get_available_models function is now available via the utility class

// Get current settings
$api_keys = array(
    'openai_api_key' => get_option('aiapg_openai_api_key', ''),
    'gemini_api_key' => get_option('aiapg_gemini_api_key', ''),
    'leonardo_api_key' => get_option('aiapg_leonardo_api_key', ''),
    'pollinations_api_key' => get_option('aiapg_pollinations_api_key', ''),
);

$default_settings = array(
    'default_text_model' => get_option('aiapg_default_text_model', 'gpt-3.5-turbo'),
    'default_image_model' => get_option('aiapg_default_image_model', 'dall-e-2'),
    'default_fallback_image_model' => get_option('aiapg_default_fallback_image_model', 'pollinations'),
    'post_status' => AIAPG_Utils::get_default_post_status(),
    'post_author' => get_option('aiapg_post_author', get_current_user_id()),
    'posts_per_run' => get_option('aiapg_default_posts_per_run', 1),
    'enable_images' => get_option('aiapg_default_enable_images', 1),
    'image_placement' => get_option('aiapg_default_image_placement', 'featured'),
    'image_size' => get_option('aiapg_default_image_size', '1024x1024'),
    'images_per_post' => get_option('aiapg_default_images_per_post', 1)
);

$advanced_settings = array(
    'enable_logging' => get_option('aiapg_enable_logging', true),
    'log_retention_days' => get_option('aiapg_log_retention_days', 30),
    'max_retries' => get_option('aiapg_max_retries', 3),
    'timeout_seconds' => get_option('aiapg_timeout_seconds', 60),
    'enable_debug_log' => get_option('aiapg_enable_debug_log', false)
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="aiapg-settings-container">
        <!-- Settings Tabs -->
        <nav class="nav-tab-wrapper">
            <a href="#api-keys" class="nav-tab nav-tab-active"><?php esc_html_e('API Keys', 'ai-auto-post-image-generator'); ?></a>
            <a href="#defaults" class="nav-tab"><?php esc_html_e('Default Settings', 'ai-auto-post-image-generator'); ?></a>
            <a href="#advanced" class="nav-tab"><?php esc_html_e('Advanced', 'ai-auto-post-image-generator'); ?></a>
            <a href="#import-export" class="nav-tab"><?php esc_html_e('Import/Export', 'ai-auto-post-image-generator'); ?></a>
        </nav>

        <!-- API Keys Tab -->
        <div id="api-keys" class="tab-content active">
            <div class="aiapg-card">
                <h2><?php esc_html_e('AI Service API Keys', 'ai-auto-post-image-generator'); ?></h2>
                <p><?php esc_html_e('Configure your API keys for the AI services you want to use. You only need to add keys for the services you plan to use.', 'ai-auto-post-image-generator'); ?></p>
                
                <form id="aiapg-api-keys-form">
                    <?php wp_nonce_field('aiapg_api_keys', 'aiapg_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="openai_key"><?php esc_html_e('OpenAI API Key', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="openai_api_key" name="openai_api_key" value="<?php echo esc_attr($api_keys['openai_api_key']); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Get your API key from', 'ai-auto-post-image-generator'); ?> 
                                    <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                    <br>
                                    <strong><?php esc_html_e('Supports:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('DALL·E 2/3, GPT-5.4 (nano/mini/full), GPT-4.1, GPT-4o, and legacy GPT-4 / 3.5. OpenAI is paid — use nano/mini for lowest cost.', 'ai-auto-post-image-generator'); ?>
                                </p>
                                <button type="button" class="button test-api-key" data-provider="openai">
                                    <?php esc_html_e('Test Connection', 'ai-auto-post-image-generator'); ?>
                                </button>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="gemini_key"><?php esc_html_e('Google Gemini API Key', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="gemini_api_key" name="gemini_api_key" value="<?php echo esc_attr($api_keys['gemini_api_key']); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Get your API key from', 'ai-auto-post-image-generator'); ?> 
                                    <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer">Google AI Studio</a>
                                    <br>
                                    <strong><?php esc_html_e('Free-tier tip:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('For more free text requests, choose Gemini 3.1 Flash-Lite. Gemini 3.5 Flash has a smaller free quota and can hit “quota exceeded” quickly. Gemini image models usually require paid billing (free image quota is often 0).', 'ai-auto-post-image-generator'); ?>
                                    <br>
                                    <a href="https://ai.dev/rate-limit" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Check your Gemini free-tier rate limits', 'ai-auto-post-image-generator'); ?></a>
                                </p>
                                <button type="button" class="button test-api-key" data-provider="gemini">
                                    <?php esc_html_e('Test Connection', 'ai-auto-post-image-generator'); ?>
                                </button>
                            </td>
                        </tr>
                        

                        
                        <tr>
                            <th scope="row">
                                <label for="leonardo_api_key"><?php esc_html_e('Leonardo.AI API Key', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="leonardo_api_key" name="leonardo_api_key" value="<?php echo esc_attr($api_keys['leonardo_api_key']); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Get your API key from', 'ai-auto-post-image-generator'); ?> 
                                    <a href="https://app.leonardo.ai/settings/api" target="_blank">Leonardo.AI Settings</a>
                                    <br>
                                    <strong><?php esc_html_e('Supports:', 'ai-auto-post-image-generator'); ?></strong> Phoenix, Lucid Origin, Kino XL models
                                </p>
                                <button type="button" class="button test-api-key" data-provider="leonardo">
                                    <?php esc_html_e('Test Connection', 'ai-auto-post-image-generator'); ?>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="pollinations_api_key"><?php esc_html_e('Pollinations API Key', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="pollinations_api_key" name="pollinations_api_key" value="<?php echo esc_attr($api_keys['pollinations_api_key']); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Optional. Leave blank to use the built-in default key. Or paste your own sk_ key from', 'ai-auto-post-image-generator'); ?>
                                    <a href="https://enter.pollinations.ai" target="_blank" rel="noopener noreferrer">enter.pollinations.ai</a>
                                    <?php esc_html_e('if the default key runs out of pollen.', 'ai-auto-post-image-generator'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save API Keys', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Default Settings Tab -->
        <div id="defaults" class="tab-content">
            <div class="aiapg-card">
                <h2><?php esc_html_e('Default Schedule Settings', 'ai-auto-post-image-generator'); ?></h2>
                <p><?php esc_html_e('These settings will be used as defaults when creating new schedules.', 'ai-auto-post-image-generator'); ?></p>
                
                <form id="aiapg-defaults-form">
                    <?php wp_nonce_field('aiapg_defaults', 'aiapg_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="default_text_model"><?php esc_html_e('Default Text Model', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <?php
                                $available_models_for_defaults = aiapg_get_available_models();
                                $default_text_models = !empty($available_models_for_defaults['text_models'])
                                    ? $available_models_for_defaults['text_models']
                                    : array_merge(
                                        AIAPG_Utils::get_openai_text_models(),
                                        AIAPG_Utils::get_gemini_text_models(),
                                        array(
                                            array('value' => '__custom__', 'label' => __('Custom model ID…', 'ai-auto-post-image-generator')),
                                        )
                                    );
                                $saved_default_text_model = $default_settings['default_text_model'];
                                $known_values = array_column($default_text_models, 'value');
                                $is_custom_default = $saved_default_text_model && !in_array($saved_default_text_model, $known_values, true);
                                ?>
                                <select id="default_text_model" name="default_text_model">
                                    <?php foreach ($default_text_models as $model) : ?>
                                        <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($is_custom_default ? '__custom__' : $saved_default_text_model, $model['value']); ?>>
                                            <?php echo esc_html($model['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="custom-default-text-model-group" class="aiapg-custom-model-group" style="<?php echo $is_custom_default || $saved_default_text_model === '__custom__' ? '' : 'display:none;'; ?> margin-top: 8px;">
                                    <label for="custom_default_text_model"><?php esc_html_e('Custom model ID', 'ai-auto-post-image-generator'); ?></label>
                                    <input type="text" id="custom_default_text_model" name="custom_default_text_model" value="<?php echo esc_attr($is_custom_default ? $saved_default_text_model : ''); ?>" placeholder="gpt-5.4-mini or gemini-3.5-flash" autocomplete="off">
                                    <p class="description"><?php esc_html_e('Use this for a new OpenAI or Gemini model ID that is not listed yet.', 'ai-auto-post-image-generator'); ?></p>
                                </div>
                                <?php if (!empty(get_option('aiapg_gemini_api_key'))) : ?>
                                    <p class="description" style="margin-top: 8px;">
                                        <button type="button" class="button button-secondary" id="aiapg-refresh-gemini-models-settings">
                                            <?php esc_html_e('Refresh Gemini models from API', 'ai-auto-post-image-generator'); ?>
                                        </button>
                                    </p>
                                <?php endif; ?>
                                <p class="description">
                                    <strong><?php esc_html_e('Cost tip:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('Free: prefer Gemini 3.1 Flash-Lite. Paid OpenAI: prefer GPT-5.4 nano or GPT-4o mini for lowest cost; GPT-5.4 for best quality.', 'ai-auto-post-image-generator'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_image_model"><?php esc_html_e('Default Image Model', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <?php
                                // Get available models
                                $available_models = aiapg_get_available_models();
                                $image_models = $available_models['image_models'];
                                $image_models_require_keys = $available_models['image_models_require_keys'];
                                ?>
                                <select id="default_image_model" name="default_image_model">
                                    <?php if (!empty($image_models)): ?>
                                        <?php foreach ($image_models as $model): ?>
                                            <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($default_settings['default_image_model'], $model['value']); ?>>
                                                <?php echo esc_html($model['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($image_models_require_keys)): ?>
                                    <p class="description">
                                        <?php esc_html_e('Additional models available with API keys:', 'ai-auto-post-image-generator'); ?>
                                        <?php foreach ($image_models_require_keys as $model): ?>
                                            <br><a href="#api-keys"><?php echo esc_html($model['label']); ?></a> (<?php echo esc_html($model['provider']); ?>)
                                        <?php endforeach; ?>
                                    </p>
                                <?php endif; ?>
                                <p class="description">
                                    <strong><?php esc_html_e('Free tokens tip:', 'ai-auto-post-image-generator'); ?></strong>
                                    <?php esc_html_e('Use Pollinations for free/cheap images. Gemini image models usually require paid Google billing.', 'ai-auto-post-image-generator'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_fallback_image_model"><?php esc_html_e('Default Fallback Image Model', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <select id="default_fallback_image_model" name="default_fallback_image_model">
                                    <?php if (!empty($image_models)): ?>
                                        <?php foreach ($image_models as $model): ?>
                                            <option value="<?php echo esc_attr($model['value']); ?>" <?php selected($default_settings['default_fallback_image_model'], $model['value']); ?>>
                                                <?php echo esc_html($model['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <p class="description"><?php esc_html_e('This model will be used if the primary image model fails.', 'ai-auto-post-image-generator'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_posts_per_run"><?php esc_html_e('Default Posts per Run', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="default_posts_per_run" name="posts_per_run" value="<?php echo esc_attr($default_settings['posts_per_run']); ?>" min="1" max="10" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="post_status"><?php esc_html_e('Default Post Status', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <select id="post_status" name="post_status">
                                    <?php foreach (AIAPG_Utils::get_post_status_choices() as $status_value => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_value); ?>" <?php selected($default_settings['post_status'], $status_value); ?>>
                                            <?php echo esc_html($status_label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Published makes posts live immediately. Choose Draft if you want to review them first. Can be overridden per schedule. If a prompt has a future publish date, WordPress uses Scheduled status.', 'ai-auto-post-image-generator'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="default_content_length"><?php esc_html_e('Default Content Length', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <select id="default_content_length" name="content_length">
                                    <?php
                                    $content_length_presets = AIAPG_Post_Generator::get_content_length_presets();
                                    $saved_content_length = get_option('aiapg_default_content_length', 'long');
                                    foreach ($content_length_presets as $length_key => $length_preset) :
                                        ?>
                                        <option value="<?php echo esc_attr($length_key); ?>" <?php selected($saved_content_length, $length_key); ?>>
                                            <?php echo esc_html($length_preset['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php esc_html_e('Default target word count for generated posts. Can be overridden per schedule.', 'ai-auto-post-image-generator'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_enable_images"><?php esc_html_e('Enable Images by Default', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="default_enable_images" name="enable_images" value="1" <?php checked($default_settings['enable_images'], 1); ?> />
                                <label for="default_enable_images"><?php esc_html_e('Generate images for posts by default', 'ai-auto-post-image-generator'); ?></label>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_image_placement"><?php esc_html_e('Default Image Placement', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <select id="default_image_placement" name="image_placement">
                                    <option value="featured" <?php selected($default_settings['image_placement'], 'featured'); ?>>
                                        <?php esc_html_e('Featured Image Only', 'ai-auto-post-image-generator'); ?>
                                    </option>
                                    <option value="inline" <?php selected($default_settings['image_placement'], 'inline'); ?>>
                                        <?php esc_html_e('Inline in Content', 'ai-auto-post-image-generator'); ?>
                                    </option>
                                    <option value="both" <?php selected($default_settings['image_placement'], 'both'); ?>>
                                        <?php esc_html_e('Both Featured and Inline', 'ai-auto-post-image-generator'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_image_size"><?php esc_html_e('Default Image Size', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <select id="default_image_size" name="image_size">
                                    <option value="256x256" <?php selected($default_settings['image_size'], '256x256'); ?>>
                                        256x256
                                    </option>
                                    <option value="512x512" <?php selected($default_settings['image_size'], '512x512'); ?>>
                                        512x512
                                    </option>
                                    <option value="1024x1024" <?php selected($default_settings['image_size'], '1024x1024'); ?>>
                                        1024x1024
                                    </option>
                                    <option value="1024x1792" <?php selected($default_settings['image_size'], '1024x1792'); ?>>
                                        1024x1792 (Portrait)
                                    </option>
                                    <option value="1792x1024" <?php selected($default_settings['image_size'], '1792x1024'); ?>>
                                        1792x1024 (Landscape)
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="default_images_per_post"><?php esc_html_e('Default Images per Post', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="default_images_per_post" name="images_per_post" value="<?php echo esc_attr($default_settings['images_per_post']); ?>" min="1" max="4" />
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Default Settings', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Advanced Tab -->
        <div id="advanced" class="tab-content">
            <div class="aiapg-card">
                <h2><?php esc_html_e('Advanced Settings', 'ai-auto-post-image-generator'); ?></h2>
                <p><?php esc_html_e('Configure advanced options for the plugin.', 'ai-auto-post-image-generator'); ?></p>
                
                <form id="aiapg-advanced-form">
                    <?php wp_nonce_field('aiapg_advanced', 'aiapg_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="log_retention_days"><?php esc_html_e('Log Retention (Days)', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="log_retention_days" name="log_retention_days" value="<?php echo esc_attr($advanced_settings['log_retention_days']); ?>" min="1" max="365" />
                                <p class="description"><?php esc_html_e('How long to keep log entries before automatic cleanup.', 'ai-auto-post-image-generator'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="max_retries"><?php esc_html_e('Maximum Retries', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="max_retries" name="max_retries" value="<?php echo esc_attr($advanced_settings['max_retries']); ?>" min="1" max="10" />
                                <p class="description"><?php esc_html_e('Number of retry attempts for failed API calls.', 'ai-auto-post-image-generator'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="timeout_seconds"><?php esc_html_e('API Timeout (Seconds)', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="timeout_seconds" name="timeout_seconds" value="<?php echo esc_attr($advanced_settings['timeout_seconds']); ?>" min="10" max="300" />
                                <p class="description"><?php esc_html_e('Timeout for API requests in seconds.', 'ai-auto-post-image-generator'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="enable_debug_log"><?php esc_html_e('Enable Debug Logging', 'ai-auto-post-image-generator'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="enable_debug_log" name="enable_debug_log" value="1" <?php checked($advanced_settings['enable_debug_log'], 1); ?> />
                                <label for="enable_debug_log"><?php esc_html_e('Log detailed debug information (for troubleshooting)', 'ai-auto-post-image-generator'); ?></label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Save Advanced Settings', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>

        <!-- Import/Export Tab -->
        <div id="import-export" class="tab-content">
            <div class="aiapg-card">
                <h2><?php esc_html_e('Import/Export Settings', 'ai-auto-post-image-generator'); ?></h2>
                <p><?php esc_html_e('Export your plugin settings to a JSON file or import settings from a previously exported file.', 'ai-auto-post-image-generator'); ?></p>
                
                <div class="aiapg-export-section">
                    <h3><?php esc_html_e('Export Settings', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Download a JSON file containing all your plugin settings, schedules, and configurations.', 'ai-auto-post-image-generator'); ?></p>
                    
                    <form id="aiapg-export-form">
                        <?php wp_nonce_field('aiapg_export', 'aiapg_nonce'); ?>
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Export Settings', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </form>
                </div>
                
                <div class="aiapg-import-section">
                    <h3><?php esc_html_e('Import Settings', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Upload a previously exported JSON file to restore your settings.', 'ai-auto-post-image-generator'); ?></p>
                    
                    <form id="aiapg-import-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('aiapg_import', 'aiapg_nonce'); ?>
                        <input type="file" name="import_file" accept=".json" required />
                        <button type="submit" class="button button-secondary">
                            <span class="dashicons dashicons-upload"></span>
                            <?php esc_html_e('Import Settings', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </form>
                </div>
                
                <div class="aiapg-reset-section">
                    <h3><?php esc_html_e('Reset Settings', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Warning: This will permanently delete all plugin settings and schedules. This action cannot be undone.', 'ai-auto-post-image-generator'); ?></p>
                    
                    <form id="aiapg-reset-form">
                        <?php wp_nonce_field('aiapg_reset', 'aiapg_nonce'); ?>
                        <button type="submit" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e('Are you sure you want to reset all settings? This action cannot be undone.', 'ai-auto-post-image-generator'); ?>')">
                            <span class="dashicons dashicons-trash"></span>
                            <?php esc_html_e('Reset All Settings', 'ai-auto-post-image-generator'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript functionality is handled by admin.js -->
