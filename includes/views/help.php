<?php
/**
 * Help View
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="aiapg-help-container">
        <!-- Quick Start Guide -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Quick Start Guide', 'ai-auto-post-image-generator'); ?></h2>
            
            <div class="aiapg-help-steps">
                <div class="help-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3><?php esc_html_e('Configure API Keys', 'ai-auto-post-image-generator'); ?></h3>
                        <p><?php esc_html_e('Go to Settings and add your API keys for the AI services you want to use. You only need keys for the services you plan to use.', 'ai-auto-post-image-generator'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-primary">
                            <?php esc_html_e('Go to Settings', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="help-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3><?php esc_html_e('Create Your First Schedule', 'ai-auto-post-image-generator'); ?></h3>
                        <p><?php esc_html_e('Navigate to Schedule Manager and create a new schedule. Choose between category-based or custom prompt content generation.', 'ai-auto-post-image-generator'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-schedules')); ?>" class="button button-primary">
                            <?php esc_html_e('Create Schedule', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="help-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3><?php esc_html_e('Test Your Setup', 'ai-auto-post-image-generator'); ?></h3>
                        <p><?php esc_html_e('Use the "Run Now" button to test your schedule immediately. Check the Logs section to monitor the results.', 'ai-auto-post-image-generator'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-logs')); ?>" class="button button-secondary">
                            <?php esc_html_e('View Logs', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features Overview -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Features Overview', 'ai-auto-post-image-generator'); ?></h2>
            
            <div class="aiapg-features-grid">
                <div class="feature-card">
                    <div class="feature-icon dashicons dashicons-admin-post"></div>
                    <h3><?php esc_html_e('AI Content Generation', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Generate engaging blog posts using OpenAI GPT models or Google Gemini. Create content based on categories or custom prompts.', 'ai-auto-post-image-generator'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon dashicons dashicons-format-image"></div>
                    <h3><?php esc_html_e('AI Image Generation', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Automatically generate relevant images using DALL-E or Leonardo.AI. Add as featured images or inline content with shortcodes.', 'ai-auto-post-image-generator'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon dashicons dashicons-clock"></div>
                    <h3><?php esc_html_e('Flexible Scheduling', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Schedule posts hourly, daily, weekly, monthly, once at a specific date/time, or with custom intervals. For custom prompts, optionally set a WordPress publish date/time per prompt.', 'ai-auto-post-image-generator'); ?></p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon dashicons dashicons-chart-line"></div>
                    <h3><?php esc_html_e('Comprehensive Logging', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Track all automation activities with detailed logs. Monitor success rates, posts created, and troubleshoot issues.', 'ai-auto-post-image-generator'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- API Providers -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Supported AI Providers', 'ai-auto-post-image-generator'); ?></h2>
            
            <div class="aiapg-providers-grid">
                <div class="provider-card">
                    <div class="provider-header">
                        <div class="provider-icon dashicons dashicons-admin-generic"></div>
                        <h3>OpenAI</h3>
                    </div>
                    <div class="provider-content">
                        <h4><?php esc_html_e('Text Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li>GPT-5.4 nano / mini (lowest cost)</li>
                            <li>GPT-5.4 (recommended quality)</li>
                            <li>GPT-4.1 / GPT-4o / GPT-4o mini</li>
                            <li>Legacy GPT-4 Turbo / GPT-3.5 Turbo</li>
                            <li><?php esc_html_e('Custom model ID', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <h4><?php esc_html_e('Image Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li>DALL·E 2</li>
                            <li>DALL·E 3</li>
                        </ul>
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="button button-secondary">
                            <?php esc_html_e('Get API Key', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="provider-card">
                    <div class="provider-header">
                        <div class="provider-icon dashicons dashicons-admin-generic"></div>
                        <h3>Google Gemini</h3>
                    </div>
                    <div class="provider-content">
                        <h4><?php esc_html_e('Text Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li>Gemini 3.1 Flash-Lite (best free-tier quota)</li>
                            <li>Gemini 3.5 Flash (higher quality, lower free quota)</li>
                            <li>Gemini Flash Latest / Pro Preview</li>
                            <li><?php esc_html_e('Custom model ID + live API model list', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <p class="description">
                            <?php esc_html_e('Free tip: choose Gemini 3.1 Flash-Lite for more free text requests. One post uses multiple Gemini calls, so quotas can finish quickly.', 'ai-auto-post-image-generator'); ?>
                        </p>
                        <h4><?php esc_html_e('Image Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li>Gemini 3.1 Flash Image (paid API)</li>
                            <li>Gemini 3.1 Flash Lite Image (paid API)</li>
                            <li>Gemini 3 Pro Image (paid API)</li>
                            <li>Gemini 2.5 Flash Image (legacy / paid)</li>
                        </ul>
                        <p class="description">
                            <?php esc_html_e('Gemini image generation usually needs Google billing. For free/cheap images, use Pollinations.', 'ai-auto-post-image-generator'); ?>
                        </p>
                        <a href="https://makersuite.google.com/app/apikey" target="_blank" class="button button-secondary">
                            <?php esc_html_e('Get API Key', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="provider-card">
                    <div class="provider-header">
                        <div class="provider-icon dashicons dashicons-admin-generic"></div>
                        <h3>Leonardo.AI</h3>
                    </div>
                    <div class="provider-content">
                        <h4><?php esc_html_e('Text Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Not available', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <h4><?php esc_html_e('Image Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li>Leonardo.AI (Multiple Models)</li>
                        </ul>
                        <a href="https://leonardo.ai/" target="_blank" class="button button-secondary">
                            <?php esc_html_e('Get API Key', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="provider-card">
                    <div class="provider-header">
                        <div class="provider-icon dashicons dashicons-admin-generic"></div>
                        <h3>Pollinations</h3>
                    </div>
                    <div class="provider-content">
                        <h4><?php esc_html_e('Text Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Not available', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <h4><?php esc_html_e('Image Models', 'ai-auto-post-image-generator'); ?></h4>
                        <ul>
                            <li><?php esc_html_e('Flux (built-in key + optional your own key)', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <a href="https://enter.pollinations.ai" target="_blank" rel="noopener noreferrer" class="button button-secondary">
                            <?php esc_html_e('Get Your Own API Key', 'ai-auto-post-image-generator'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Frequently Asked Questions', 'ai-auto-post-image-generator'); ?></h2>
            
            <div class="aiapg-faq">
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('How much do the AI services cost?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Costs vary by provider and model:', 'ai-auto-post-image-generator'); ?></p>
                        <ul>
                            <li><strong>OpenAI:</strong> <?php esc_html_e('Paid API. GPT-5.4 nano / GPT-4o mini are cheapest; GPT-5.4 is higher quality. DALL·E priced per image.', 'ai-auto-post-image-generator'); ?></li>
                            <li><strong>Google Gemini:</strong> <?php esc_html_e('Gemini 3.1 Flash-Lite: best free-tier text quota; then pay-per-use. Image models usually need billing.', 'ai-auto-post-image-generator'); ?></li>
                            <li><strong>Leonardo.AI:</strong> <?php esc_html_e('Pay-per-image, varies by model and quality', 'ai-auto-post-image-generator'); ?></li>
                            <li><strong>Pollinations:</strong> <?php esc_html_e('Free API key required; usage may consume pollen credits', 'ai-auto-post-image-generator'); ?></li>
                        </ul>
                        <p><?php esc_html_e('Check each provider\'s pricing page for current rates.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('Can I use multiple AI providers at once?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Yes! You can configure different schedules to use different providers. For example, use OpenAI for text and Leonardo.AI for images.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('How do I ensure content quality?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Use custom prompts to guide the AI, and set Post Status to Draft if you want to review content before it goes live. Published is the default post status.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('What happens if an API call fails?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Failed runs are logged with error details. You can retry failed schedules manually from the Logs page. The plugin includes retry logic with exponential backoff for temporary failures.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('Can I edit posts before they publish?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Yes. Set Default Post Status to Draft in Settings (or per schedule), then review, edit, and publish manually when ready. The default is Published.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('How do I backup my settings?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Use the Export feature in Settings to download all your configurations as a JSON file. You can import this file to restore settings on another site.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('How do image shortcodes work?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('When you enable inline images, the plugin automatically inserts [aiapg_image] shortcodes into your content. These shortcodes are processed to generate and display images at those locations.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <h3 class="faq-question"><?php esc_html_e('What\'s the difference between featured and inline images?', 'ai-auto-post-image-generator'); ?></h3>
                    <div class="faq-answer">
                        <p><?php esc_html_e('Featured images appear at the top of your post and are set as the WordPress featured image. Inline images are inserted within the content using shortcodes. You can use both simultaneously.', 'ai-auto-post-image-generator'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Troubleshooting -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Troubleshooting', 'ai-auto-post-image-generator'); ?></h2>
            
            <div class="aiapg-troubleshooting">
                <div class="trouble-item">
                    <h3><?php esc_html_e('API Key Issues', 'ai-auto-post-image-generator'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Verify your API key is correct and has sufficient credits', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Check if your API key has the necessary permissions', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Ensure your account is not suspended or rate-limited', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Use the "Test Connection" button in Settings to verify your keys', 'ai-auto-post-image-generator'); ?></li>
                    </ul>
                </div>
                
                <div class="trouble-item">
                    <h3><?php esc_html_e('Content Generation Problems', 'ai-auto-post-image-generator'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Try different prompts or categories', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Check if your prompts are clear and specific', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Verify your WordPress categories exist and have content', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Check the logs for specific error messages', 'ai-auto-post-image-generator'); ?></li>
                    </ul>
                </div>
                
                <div class="trouble-item">
                    <h3><?php esc_html_e('Scheduling Issues', 'ai-auto-post-image-generator'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Ensure WordPress cron is working properly', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Check if your server timezone is set correctly', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Verify schedules are set to active status', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Check if schedules are not locked due to previous failures', 'ai-auto-post-image-generator'); ?></li>
                    </ul>
                </div>
                
                <div class="trouble-item">
                    <h3><?php esc_html_e('Image Generation Failures', 'ai-auto-post-image-generator'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Check if your image API key has sufficient credits', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Verify the image model supports your chosen size', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Ensure your server can download and process images', 'ai-auto-post-image-generator'); ?></li>
                        <li><?php esc_html_e('Try using Pollinations (free) as a fallback option', 'ai-auto-post-image-generator'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Support -->
        <div class="aiapg-help-section">
            <h2><?php esc_html_e('Support & Contact', 'ai-auto-post-image-generator'); ?></h2>
            <p class="description aiapg-support-hint">
                <?php esc_html_e('To support ongoing development, use the “Buy me a coffee” banner on the Dashboard or at the bottom of Schedule Manager.', 'ai-auto-post-image-generator'); ?>
            </p>
            <div class="aiapg-support-grid">
                <div class="support-card">
                    <div class="support-icon dashicons dashicons-email"></div>
                    <h3><?php esc_html_e('Email Support', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Get direct support from our team for technical issues and questions.', 'ai-auto-post-image-generator'); ?></p>
                    <a href="mailto:contact@wallshoot.com" class="button button-primary">
                        <?php esc_html_e('Contact Support', 'ai-auto-post-image-generator'); ?>
                    </a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon dashicons dashicons-admin-tools"></div>
                    <h3><?php esc_html_e('Plugin Settings', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Configure API keys, default models, and advanced settings for optimal performance.', 'ai-auto-post-image-generator'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-settings')); ?>" class="button button-secondary">
                        <?php esc_html_e('Go to Settings', 'ai-auto-post-image-generator'); ?>
                    </a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon dashicons dashicons-list-view"></div>
                    <h3><?php esc_html_e('View Logs', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Check detailed logs to troubleshoot issues and monitor your automation performance.', 'ai-auto-post-image-generator'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-logs')); ?>" class="button button-secondary">
                        <?php esc_html_e('View Logs', 'ai-auto-post-image-generator'); ?>
                    </a>
                </div>
                
                <div class="support-card">
                    <div class="support-icon dashicons dashicons-calendar-alt"></div>
                    <h3><?php esc_html_e('Schedule Manager', 'ai-auto-post-image-generator'); ?></h3>
                    <p><?php esc_html_e('Create and manage your automated posting schedules with ease.', 'ai-auto-post-image-generator'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=aiapg-schedules')); ?>" class="button button-secondary">
                        <?php esc_html_e('Manage Schedules', 'ai-auto-post-image-generator'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


