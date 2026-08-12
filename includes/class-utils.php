<?php
/**
 * Utility Functions Class
 *
 * Contains shared utility functions used across the plugin.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Utils Class
 *
 * @since 1.0.0
 */
class AIAPG_Utils {

    /**
     * Curated OpenAI text models.
     *
     * @return array
     */
    public static function get_openai_text_models() {
        $models = array(
            array('value' => 'gpt-5.4-nano', 'label' => 'GPT-5.4 nano (OpenAI) — Cheapest modern'),
            array('value' => 'gpt-5.4-mini', 'label' => 'GPT-5.4 mini (OpenAI) — Low cost / good quality'),
            array('value' => 'gpt-5.4', 'label' => 'GPT-5.4 (OpenAI) — Recommended paid quality'),
            array('value' => 'gpt-4.1-mini', 'label' => 'GPT-4.1 mini (OpenAI) — Cheap & fast'),
            array('value' => 'gpt-4.1', 'label' => 'GPT-4.1 (OpenAI)'),
            array('value' => 'gpt-4o-mini', 'label' => 'GPT-4o mini (OpenAI) — Popular low-cost'),
            array('value' => 'gpt-4o', 'label' => 'GPT-4o (OpenAI)'),
            array('value' => 'gpt-4-turbo', 'label' => 'GPT-4 Turbo (OpenAI) — Legacy'),
            array('value' => 'gpt-3.5-turbo', 'label' => 'GPT-3.5 Turbo (OpenAI) — Legacy'),
        );

        /**
         * Filter curated OpenAI text models shown in the admin UI.
         *
         * @param array $models
         */
        return apply_filters('aiapg_openai_text_models', $models);
    }

    /**
     * Whether a model ID should be routed to OpenAI.
     *
     * @param string $model
     * @return bool
     */
    public static function is_openai_text_model($model) {
        $model = strtolower(trim((string) $model));
        if ($model === '' || $model === '__custom__') {
            return false;
        }

        return (bool) preg_match('/^(gpt-|o[0-9]|chatgpt-|codex-)/', $model);
    }

    /**
     * Allowed WordPress post statuses for generated posts.
     *
     * @return array<string, string> status => label
     */
    public static function get_post_status_choices() {
        $choices = array(
            'draft'   => __('Draft', 'ai-auto-post-image-generator'),
            'pending' => __('Pending Review', 'ai-auto-post-image-generator'),
            'private' => __('Private', 'ai-auto-post-image-generator'),
            'publish' => __('Published', 'ai-auto-post-image-generator'),
        );

        /**
         * Filter allowed post statuses for AI-generated posts.
         *
         * @param array<string, string> $choices
         */
        return apply_filters('aiapg_post_status_choices', $choices);
    }

    /**
     * Normalize a post status to an allowed value.
     *
     * @param string $status
     * @param string $fallback
     * @return string
     */
    public static function normalize_post_status($status, $fallback = 'publish') {
        $status   = sanitize_key((string) $status);
        $fallback = sanitize_key((string) $fallback);
        $allowed  = array_keys(self::get_post_status_choices());

        if (!in_array($fallback, $allowed, true)) {
            $fallback = 'publish';
        }

        if ($status === '' || !in_array($status, $allowed, true)) {
            return $fallback;
        }

        return $status;
    }

    /**
     * Global default post status for generated posts.
     *
     * @return string
     */
    public static function get_default_post_status() {
        return self::normalize_post_status(
            get_option('aiapg_post_status', 'publish'),
            'publish'
        );
    }

    /**
     * Curated Gemini text models (stable/common IDs).
     * Google retires models often — users can also pick a custom model ID.
     *
     * @return array
     */
    public static function get_gemini_text_models() {
        $models = array(
            array('value' => 'gemini-3.1-flash-lite', 'label' => 'Gemini 3.1 Flash-Lite (Google) — Best free-tier quota'),
            array('value' => 'gemini-3.5-flash', 'label' => 'Gemini 3.5 Flash (Google) — Higher quality, lower free quota'),
            array('value' => 'gemini-flash-latest', 'label' => 'Gemini Flash Latest (Google alias) — Follows current Flash model'),
            array('value' => 'gemini-3.1-pro-preview', 'label' => 'Gemini 3.1 Pro Preview (Google) — Paid / limited free'),
            array('value' => 'gemini-2.5-flash', 'label' => 'Gemini 2.5 Flash (Legacy — blocked for new API keys)'),
            array('value' => 'gemini-2.5-pro', 'label' => 'Gemini 2.5 Pro (Legacy — blocked for new API keys)'),
            array('value' => 'gemini-2.5-flash-lite', 'label' => 'Gemini 2.5 Flash-Lite (Legacy — blocked for new API keys)'),
        );

        /**
         * Filter curated Gemini text models shown in the admin UI.
         *
         * @param array $models
         */
        return apply_filters('aiapg_gemini_text_models', $models);
    }

    /**
     * Gemini text models that Google often blocks for new API keys.
     *
     * @return string[]
     */
    public static function get_retired_gemini_text_models() {
        return array(
            'gemini-2.5-flash',
            'gemini-2.5-pro',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-pro',
        );
    }

    /**
     * Map a Gemini text model to one available for current/new API keys.
     *
     * @param string $model
     * @return string
     */
    public static function normalize_gemini_text_model($model) {
        $model = sanitize_text_field((string) $model);
        if ($model === '' || $model === '__custom__') {
            return self::get_default_gemini_model();
        }

        if (in_array($model, self::get_retired_gemini_text_models(), true)) {
            return 'gemini-3.1-flash-lite';
        }

        return $model;
    }

    /**
     * Curated Gemini image models (Nano Banana / native image generation).
     *
     * @return array
     */
    public static function get_gemini_image_models() {
        $models = array(
            array('value' => 'gemini-3.1-flash-image', 'label' => 'Gemini 3.1 Flash Image (Google) — Paid API (no free image quota)'),
            array('value' => 'gemini-3.1-flash-lite-image', 'label' => 'Gemini 3.1 Flash Lite Image (Google) — Paid API (cheapest Gemini image)'),
            array('value' => 'gemini-3-pro-image', 'label' => 'Gemini 3 Pro Image (Google) — Paid API (highest quality)'),
            array('value' => 'gemini-2.5-flash-image', 'label' => 'Gemini 2.5 Flash Image (Legacy — paid / prefer 3.1 Flash Lite Image)'),
        );

        /**
         * Filter curated Gemini image models shown in the admin UI.
         *
         * @param array $models
         */
        return apply_filters('aiapg_gemini_image_models', $models);
    }

    /**
     * Resolve Pollinations API key: user override first, then built-in default.
     *
     * @return string
     */
    public static function get_pollinations_api_key() {
        $user_key = trim((string) get_option('aiapg_pollinations_api_key', ''));
        if ($user_key !== '') {
            return $user_key;
        }

        $default = defined('AIAPG_DEFAULT_POLLINATIONS_API_KEY') ? (string) AIAPG_DEFAULT_POLLINATIONS_API_KEY : '';

        /**
         * Filter the effective Pollinations API key (after user override check).
         *
         * @param string $default Built-in default when no user key is set.
         */
        return (string) apply_filters('aiapg_pollinations_api_key', $default);
    }

    /**
     * Whether the site is using the built-in Pollinations key (no custom override).
     *
     * @return bool
     */
    public static function is_using_default_pollinations_key() {
        return trim((string) get_option('aiapg_pollinations_api_key', '')) === '';
    }

    /**
     * Default Gemini model used for API tests and new installs.
     *
     * @return string
     */
    public static function get_default_gemini_model() {
        $default = 'gemini-3.1-flash-lite';
        $saved   = get_option('aiapg_default_text_model', '');

        if (is_string($saved) && strpos($saved, 'gemini') === 0 && $saved !== '__custom__') {
            return self::normalize_gemini_text_model($saved);
        }

        return $default;
    }

    /**
     * Fetch generateContent-capable Gemini models from Google's API.
     *
     * @param string $api_key Optional API key override.
     * @param bool   $force_refresh Bypass transient cache.
     * @return array{success:bool,models?:array,message?:string}
     */
    public static function fetch_gemini_models_from_api($api_key = '', $force_refresh = false) {
        $api_key = $api_key !== '' ? $api_key : get_option('aiapg_gemini_api_key', '');

        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => __('Gemini API key not configured.', 'ai-auto-post-image-generator'),
            );
        }

        $cache_key = 'aiapg_gemini_models_list';
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached) && !empty($cached)) {
                return array(
                    'success' => true,
                    'models'  => $cached,
                );
            }
        }

        $response = self::make_get_request(
            'https://generativelanguage.googleapis.com/v1beta/models?key=' . rawurlencode($api_key) . '&pageSize=100',
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'timeout' => 30,
            ),
            'Gemini List Models'
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($body) || empty($body['models']) || !is_array($body['models'])) {
            $error_message = isset($body['error']['message'])
                ? $body['error']['message']
                : __('Could not retrieve Gemini models.', 'ai-auto-post-image-generator');

            return array(
                'success' => false,
                'message' => $error_message,
            );
        }

        $models = array();
        foreach ($body['models'] as $model) {
            if (empty($model['name']) || !is_string($model['name'])) {
                continue;
            }

            $supported = isset($model['supportedGenerationMethods']) && is_array($model['supportedGenerationMethods'])
                ? $model['supportedGenerationMethods']
                : array();

            if (!in_array('generateContent', $supported, true)) {
                continue;
            }

            $model_id = preg_replace('#^models/#', '', $model['name']);
            if ($model_id === '' || strpos($model_id, 'gemini') !== 0) {
                continue;
            }

            // Skip image/audio-only style model IDs for text generation dropdowns.
            if (preg_match('/(image|tts|audio|live|embedding|aqa)/i', $model_id)) {
                continue;
            }

            $label = !empty($model['displayName']) ? $model['displayName'] : $model_id;
            $models[] = array(
                'value' => $model_id,
                'label' => $label . ' (Google)',
            );
        }

        usort(
            $models,
            function ($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            }
        );

        set_transient($cache_key, $models, 12 * HOUR_IN_SECONDS);

        return array(
            'success' => true,
            'models'  => $models,
        );
    }

    /**
     * Get available models based on API key configuration
     *
     * @return array
     */
    public static function get_available_models() {
        $models = array(
            'text_models' => array(),
            'image_models' => array(),
            'text_models_require_keys' => array(),
            'image_models_require_keys' => array()
        );
        
        // Text Models
        $openai_key = get_option('aiapg_openai_api_key');
        $gemini_key = get_option('aiapg_gemini_api_key');

        $openai_models = self::get_openai_text_models();
        if (!empty($openai_key)) {
            foreach ($openai_models as $openai_model) {
                $models['text_models'][] = $openai_model;
            }
        } else {
            foreach ($openai_models as $openai_model) {
                $openai_model['provider'] = 'OpenAI';
                $models['text_models_require_keys'][] = $openai_model;
            }
        }
        
        $gemini_models = self::get_gemini_text_models();
        if (!empty($gemini_key)) {
            foreach ($gemini_models as $gemini_model) {
                if (($gemini_model['value'] ?? '') === '__custom__') {
                    continue;
                }
                $models['text_models'][] = $gemini_model;
            }

            // Merge any previously fetched live models (excluding duplicates / custom sentinel).
            $live = get_transient('aiapg_gemini_models_list');
            if (is_array($live)) {
                $existing = array_column($models['text_models'], 'value');
                foreach ($live as $live_model) {
                    if (empty($live_model['value']) || in_array($live_model['value'], $existing, true)) {
                        continue;
                    }
                    $models['text_models'][] = $live_model;
                    $existing[] = $live_model['value'];
                }
            }
        } else {
            foreach ($gemini_models as $gemini_model) {
                if (($gemini_model['value'] ?? '') === '__custom__') {
                    continue;
                }
                $gemini_model['provider'] = 'Google';
                $models['text_models_require_keys'][] = $gemini_model;
            }
        }

        // Shared custom model ID option when at least one text provider key is set.
        if (!empty($openai_key) || !empty($gemini_key)) {
            $models['text_models'][] = array(
                'value' => '__custom__',
                'label' => __('Custom model ID…', 'ai-auto-post-image-generator'),
            );
        }
        
        // Image Models
        $leonardo_key = get_option('aiapg_leonardo_api_key');

        // Pollinations is always available (built-in default key; optional user override)
        $models['image_models'][] = array(
            'value' => 'pollinations',
            'label' => self::is_using_default_pollinations_key()
                ? 'Pollinations (Flux — Built-in Key)'
                : 'Pollinations (Flux — Your Key)',
        );

        $gemini_image_models = self::get_gemini_image_models();
        if (!empty($gemini_key)) {
            foreach ($gemini_image_models as $gemini_image_model) {
                $models['image_models'][] = $gemini_image_model;
            }
        } else {
            foreach ($gemini_image_models as $gemini_image_model) {
                $gemini_image_model['provider'] = 'Google';
                $models['image_models_require_keys'][] = $gemini_image_model;
            }
        }
        
        if (!empty($openai_key)) {
            $models['image_models'][] = array('value' => 'dall-e-2', 'label' => 'DALL-E 2');
            $models['image_models'][] = array('value' => 'dall-e-3', 'label' => 'DALL-E 3');
        } else {
            $models['image_models_require_keys'][] = array('value' => 'dall-e-2', 'label' => 'DALL-E 2', 'provider' => 'OpenAI');
            $models['image_models_require_keys'][] = array('value' => 'dall-e-3', 'label' => 'DALL-E 3', 'provider' => 'OpenAI');
        }
        
        if (!empty($leonardo_key)) {
            $models['image_models'][] = array('value' => 'leonardo', 'label' => 'Leonardo.AI');
        } else {
            $models['image_models_require_keys'][] = array('value' => 'leonardo', 'label' => 'Leonardo.AI', 'provider' => 'Leonardo.AI');
        }
        
        return $models;
    }

    /**
     * Pull visible answer text from a Gemini generateContent payload.
     * Thinking models often put thoughts / empty signature parts first.
     *
     * @param array|null $data
     * @return string
     */
    public static function extract_gemini_text_from_response($data) {
        if (!is_array($data) || empty($data['candidates']) || !is_array($data['candidates'])) {
            return '';
        }

        $parts = array();
        if (!empty($data['candidates'][0]['content']['parts']) && is_array($data['candidates'][0]['content']['parts'])) {
            $parts = $data['candidates'][0]['content']['parts'];
        }

        $chunks = array();
        foreach ($parts as $part) {
            if (!is_array($part)) {
                continue;
            }

            // Skip internal thought summaries when present.
            if (!empty($part['thought'])) {
                continue;
            }

            if (!isset($part['text']) || !is_string($part['text'])) {
                continue;
            }

            $text = trim($part['text']);
            if ($text === '') {
                continue;
            }

            $chunks[] = $text;
        }

        if (empty($chunks)) {
            return '';
        }

        return trim(implode("\n\n", $chunks));
    }

    /**
     * Normalize a site-local datetime string to MySQL format (Y-m-d H:i:s).
     * Keeps wall-clock time as entered (does not convert timezones).
     *
     * @param string $datetime
     * @return string Empty string if invalid.
     */
    public static function normalize_datetime($datetime) {
        $datetime = trim((string) $datetime);
        if ($datetime === '') {
            return '';
        }

        // Support HTML datetime-local values (Y-m-d\TH:i).
        $datetime = str_replace('T', ' ', $datetime);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $datetime)) {
            $datetime .= ':00';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $datetime)) {
            return '';
        }

        $parts = date_parse($datetime);
        if (
            empty($parts) ||
            !empty($parts['error_count']) ||
            !checkdate((int) $parts['month'], (int) $parts['day'], (int) $parts['year'])
        ) {
            return '';
        }

        return sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d',
            (int) $parts['year'],
            (int) $parts['month'],
            (int) $parts['day'],
            (int) $parts['hour'],
            (int) $parts['minute'],
            (int) $parts['second']
        );
    }

    /**
     * Convert a site-local MySQL datetime to a Unix timestamp suitable for wp_schedule_*.
     *
     * @param string $local_mysql_datetime
     * @return int|false
     */
    public static function local_datetime_to_gmt_timestamp($local_mysql_datetime) {
        $normalized = self::normalize_datetime($local_mysql_datetime);
        if ($normalized === '') {
            return false;
        }

        $gmt = get_gmt_from_date($normalized);
        if (empty($gmt)) {
            return false;
        }

        return strtotime($gmt . ' UTC');
    }

    /**
     * Make API request with retry logic and dynamic timeout
     *
     * @param string $url
     * @param array $args
     * @param string $context Optional context for logging
     * @return WP_Error|array
     */
    public static function make_api_request($url, $args = array(), $context = '') {
        $max_retries = isset($args['aiapg_max_retries'])
            ? max(0, (int) $args['aiapg_max_retries'])
            : max(0, (int) get_option('aiapg_max_retries', 3));
        unset($args['aiapg_max_retries']);

        $timeout = get_option('aiapg_timeout_seconds', 60);
        $debug_log = get_option('aiapg_enable_debug_log', false);
        
        // Ensure timeout is set in args
        if (!isset($args['timeout'])) {
            $args['timeout'] = $timeout;
        }
        
        if ($debug_log) {
            
        }
        
        $attempt = 0;
        $last_error = null;
        
        while ($attempt <= $max_retries) {
            $attempt++;
            
            if ($debug_log && $attempt > 1) {
                
            }
            
            $response = wp_remote_request($url, $args);
            
            // If successful, return immediately
            if (!is_wp_error($response)) {
                $response_code = wp_remote_retrieve_response_code($response);
                
                // Consider 2xx and 3xx responses as successful
                if ($response_code >= 200 && $response_code < 400) {
                    if ($debug_log) {
                        
                    }
                    return $response;
                }
                
                // For 4xx errors (client errors), don't retry
                if ($response_code >= 400 && $response_code < 500) {
                    if ($debug_log) {
                        
                    }
                    return $response;
                }
                
                // For 5xx errors (server errors), retry
                if ($response_code >= 500) {
                    $last_error = new WP_Error(
                        'http_error',
                        sprintf('HTTP %d error on attempt %d', $response_code, $attempt)
                    );
                }
            } else {
                $last_error = $response;
                
                // Check if it's a timeout error
                $error_code = $response->get_error_code();
                if (strpos($error_code, 'timeout') !== false || strpos($error_code, 'connect') !== false) {
                    if ($debug_log) {
                        
                    }
                } else {
                    // For other errors, don't retry
                    if ($debug_log) {
                        
                    }
                    return $response;
                }
            }
            
            // If we've reached max retries, break
            if ($attempt > $max_retries) {
                break;
            }
            
            // Calculate exponential backoff delay (1s, 2s, 4s, 8s...)
            $delay = pow(2, $attempt - 1);
            
            if ($debug_log) {
                
            }
            
            sleep($delay);
        }
        
        // All retries exhausted
        if ($debug_log) {
            
        }
        
        return $last_error ?: new WP_Error('max_retries_exceeded', "All {$max_retries} retry attempts failed for {$context}");
    }

    /**
     * Make POST request with retry logic
     *
     * @param string $url
     * @param array $args
     * @param string $context Optional context for logging
     * @return WP_Error|array
     */
    public static function make_post_request($url, $args = array(), $context = '') {
        $args['method'] = 'POST';
        return self::make_api_request($url, $args, $context);
    }

    /**
     * Make GET request with retry logic
     *
     * @param string $url
     * @param array $args
     * @param string $context Optional context for logging
     * @return WP_Error|array
     */
    public static function make_get_request($url, $args = array(), $context = '') {
        $args['method'] = 'GET';
        return self::make_api_request($url, $args, $context);
    }
}

/**
 * Global function wrapper for backward compatibility
 */
if (!function_exists('aiapg_get_available_models')) {
    function aiapg_get_available_models() {
        return AIAPG_Utils::get_available_models();
    }
}
