<?php
/**
 * Image Generator Class
 *
 * Handles AI image generation using various providers.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Image_Generator Class
 *
 * @since 1.0.0
 */
class AIAPG_Image_Generator {

    /**
     * Store last generation statistics for logging
     */
    private $last_generation_stats = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks if needed
    }

    /**
     * Generate images for a post
     *
     * @param string $title
     * @param object $schedule
     * @return array
     */
    public function generate_images($title, $schedule) {
        $images = array();
        $count = $schedule->images_per_post;
        $image_errors = array();
        $successful_images = 0;

        if (get_option('aiapg_enable_debug_log', false)) {
            
            
            
            
        }

        for ($i = 0; $i < $count; $i++) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            
            $image_result = $this->generate_single_image($title, $schedule, $i);
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            
            if ($image_result['success']) {
                $images[] = $image_result['url'];
                $successful_images++;
            } else {
                $image_errors[] = array(
                    'image_index' => $i + 1,
                    'model' => $schedule->image_model,
                    'error' => $image_result['message'],
                    'title' => $title
                );
            }
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
            
        }

        // Store image generation details for logging
        $this->last_generation_stats = array(
            'requested_images' => $count,
            'successful_images' => $successful_images,
            'failed_images' => count($image_errors),
            'errors' => $image_errors,
            'model_used' => $schedule->image_model
        );

        return $images;
    }

    /**
     * Get last generation statistics for logging
     *
     * @return array
     */
    public function get_last_generation_stats() {
        if (empty($this->last_generation_stats)) {
            $this->last_generation_stats = array(
                'requested_images' => 0,
                'successful_images' => 0,
                'failed_images' => 0,
                'errors' => array(),
                'model_used' => ''
            );
        }
        return $this->last_generation_stats;
    }

    /**
     * Generate a single image
     *
     * @param string $title
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    private function generate_single_image($title, $schedule, $image_index = 0) {
        $result = array(
            'success' => false,
            'url' => '',
            'message' => ''
        );

        // Generate image prompt from title
        $prompt = $this->generate_image_prompt($title);

        return $this->generate_image_from_prompt($prompt, $schedule, $image_index);
    }

    /**
     * Generate a single image from a specific prompt
     *
     * @param string $prompt
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    public function generate_image_from_prompt($prompt, $schedule, $image_index = 0) {
        $result = array(
            'success' => false,
            'url' => '',
            'message' => ''
        );

        // Try the primary model first
        $primary_result = $this->try_image_model($schedule->image_model, $prompt, $schedule, $image_index);
        
        if ($primary_result['success']) {
            return $primary_result;
        }
        
        // If primary model fails, try user-configured fallback model
        if (get_option('aiapg_enable_debug_log', false)) {
            
        }
        
        // Get the user-configured fallback model for this schedule
        $fallback_model = $schedule->fallback_image_model ?? get_option('aiapg_default_fallback_image_model', 'pollinations');
        
        // Try the user-selected fallback model
        $fallback_result = $this->try_image_model($fallback_model, $prompt, $schedule, $image_index);
        if ($fallback_result['success']) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return $fallback_result;
        }
        
        // If all attempts fail, provide a comprehensive error message
        $error_messages = array();
        if (!empty($primary_result['message'])) {
            $error_messages[] = 'Primary (' . $schedule->image_model . '): ' . $primary_result['message'];
        }
        
        if (!empty($fallback_result['message'])) {
            $error_messages[] = 'Fallback (' . $fallback_model . '): ' . $fallback_result['message'];
        }
        
        $final_message = 'All image generation services failed. ';
        if (!empty($error_messages)) {
            $final_message .= 'Errors: ' . implode('; ', array_unique($error_messages));
        } else {
            $final_message .= 'Please check your API keys and account limits.';
        }
        
        if (get_option('aiapg_enable_debug_log', false)) {
            
        }
        
        return array(
            'success' => false,
            'url' => '',
            'message' => $final_message
        );
    }
    
    /**
     * Try a specific image model
     *
     * @param string $model
     * @param string $prompt
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    private function try_image_model($model, $prompt, $schedule, $image_index = 0) {
        if (!empty($model) && strpos($model, 'dall-e') === 0) {
            return $this->generate_with_dalle($prompt, $schedule, $image_index);
        } elseif (!empty($model) && strpos($model, 'gemini') === 0) {
            return $this->generate_with_gemini($prompt, $schedule, $image_index, $model);
        } elseif (!empty($model) && strpos($model, 'pollinations') === 0) {
            return $this->generate_with_pollinations($prompt, $schedule, $image_index);
        } elseif (!empty($model) && strpos($model, 'leonardo') === 0) {
            return $this->generate_with_leonardo($prompt, $schedule, $image_index);
        } else {
            return array(
                'success' => false,
                'url' => '',
                'message' => __('Unsupported image model.', 'ai-auto-post-image-generator')
            );
        }
    }

    /**
     * Generate image prompt from title
     *
     * @param string $title
     * @return string
     */
    private function generate_image_prompt($title) {
        // Clean the title for image generation
        $prompt = sanitize_text_field($title);
        
        // Add some context to make it more suitable for image generation
        $prompt = 'High quality, professional, ' . $prompt . ', digital art, clean background';
        
        return $prompt;
    }

    /**
     * Generate image with DALL-E
     *
     * @param string $prompt
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    private function generate_with_dalle($prompt, $schedule, $image_index = 0) {
        $api_key = get_option('aiapg_openai_api_key');
        if (empty($api_key)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => 'OpenAI API key not configured.'
            );
        }

        // Determine which DALL-E model to use based on schedule
        $model = 'dall-e-2'; // Default fallback
        if (strpos($schedule->image_model, 'dall-e-3') !== false) {
            $model = 'dall-e-3';
        } elseif (strpos($schedule->image_model, 'dall-e-2') !== false) {
            $model = 'dall-e-2';
        }
        
        // Map size to DALL-E format
        $size = $this->map_size_to_dalle($schedule->image_size, $model);

        // Use the improved request structure based on the reference implementation
        $body = array(
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'response_format' => 'b64_json' // Use base64 for better reliability
        );

        $response = AIAPG_Utils::make_post_request('https://api.openai.com/v1/images/generations', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($body)
        ), 'OpenAI Image Generation');

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code < 200 || $code >= 300) {
            // Parse error response for better user feedback
            $data = json_decode($body, true);
            $error_message = 'OpenAI HTTP error: ' . $body;
            
            if (isset($data['error']['message'])) {
                $error_message = $data['error']['message'];
                
                // Handle specific billing errors
                if (strpos($error_message, 'Billing hard limit has been reached') !== false || 
                    strpos($error_message, 'billing_hard_limit_reached') !== false) {
                    $error_message = 'OpenAI billing limit reached. Please add credits to your OpenAI account or use a different image model.';
                } elseif (strpos($error_message, 'quota') !== false || strpos($error_message, 'limit') !== false) {
                    $error_message = 'OpenAI quota exceeded. Please check your OpenAI account limits or use a different image model.';
                }
            }
            
            return array(
                'success' => false,
                'url' => '',
                'message' => $error_message
            );
        }

        $data = json_decode($body, true);

        if (empty($data['data']) || !is_array($data['data'])) {
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Unexpected response from OpenAI.'
            );
        }

        // Process the first image
        if (!empty($data['data'][0]['b64_json'])) {
            $b64_data = $data['data'][0]['b64_json'];
        if (!empty($b64_data) && base64_encode(base64_decode($b64_data, true)) === $b64_data) {
            $binary = base64_decode($b64_data);
        } else {
            throw new Exception('Invalid base64 data received from API');
        }
            if ($binary !== false) {
                // Save the image to media library and return the URL
                $attachment_id = $this->save_image_to_media($binary, 0, 'image/png');
                if ($attachment_id) {
                    $image_url = wp_get_attachment_url($attachment_id);
                    return array(
                        'success' => true,
                        'url' => $image_url,
                        'message' => ''
                    );
                }
            }
        }

        return array(
            'success' => false,
            'url' => '',
            'message' => isset($data['error']['message']) ? $data['error']['message'] : __('Unknown error occurred.', 'ai-auto-post-image-generator')
        );
    }



    /**
     * Generate image with Gemini native image models (Nano Banana).
     *
     * @param string $prompt
     * @param object $schedule
     * @param int    $image_index
     * @param string $model
     * @return array
     */
    private function generate_with_gemini($prompt, $schedule, $image_index = 0, $model = '') {
        $api_key = get_option('aiapg_gemini_api_key');

        if (empty($api_key)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => __('Gemini API key not configured.', 'ai-auto-post-image-generator'),
            );
        }

        $model = sanitize_text_field((string) $model);
        if ($model === '' || strpos($model, 'gemini') !== 0) {
            $model = 'gemini-3.1-flash-image';
        }

        $randomized_prompt = $this->add_randomization_to_prompt($prompt, $image_index);
        $aspect_ratio = $this->map_size_to_gemini_aspect_ratio(
            isset($schedule->image_size) ? $schedule->image_size : '1024x1024'
        );

        $body = array(
            'contents' => array(
                array(
                    'role' => 'user',
                    'parts' => array(
                        array('text' => $randomized_prompt),
                    ),
                ),
            ),
            'generationConfig' => array(
                'responseModalities' => array('TEXT', 'IMAGE'),
                'imageConfig' => array(
                    'aspectRatio' => $aspect_ratio,
                ),
            ),
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($api_key);

        $response = AIAPG_Utils::make_post_request(
            $url,
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body),
                'timeout' => 120,
            ),
            'Gemini Image Generation'
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => $response->get_error_message(),
            );
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
        $data = json_decode($raw_body, true);

        if ($code < 200 || $code >= 300) {
            $error_message = __('Gemini image generation failed.', 'ai-auto-post-image-generator');
            if (isset($data['error']['message']) && is_string($data['error']['message'])) {
                $error_message = $data['error']['message'];
            } elseif (is_string($raw_body) && $raw_body !== '') {
                $error_message = $raw_body;
            }

            if (stripos($error_message, 'quota') !== false || stripos($error_message, 'rate-limits') !== false || stripos($error_message, 'RESOURCE_EXHAUSTED') !== false) {
                $error_message = __('Gemini image quota exceeded (free-tier limit is often 0 for image models). Enable billing in Google AI Studio / Cloud, or use Pollinations/DALL-E/Leonardo instead.', 'ai-auto-post-image-generator')
                    . ' ' . $error_message;
            }

            return array(
                'success' => false,
                'url' => '',
                'message' => $error_message,
            );
        }

        $parts = array();
        if (!empty($data['candidates'][0]['content']['parts']) && is_array($data['candidates'][0]['content']['parts'])) {
            $parts = $data['candidates'][0]['content']['parts'];
        }

        foreach ($parts as $part) {
            $inline = null;
            if (!empty($part['inlineData']) && is_array($part['inlineData'])) {
                $inline = $part['inlineData'];
            } elseif (!empty($part['inline_data']) && is_array($part['inline_data'])) {
                $inline = $part['inline_data'];
            }

            if (empty($inline['data']) || !is_string($inline['data'])) {
                continue;
            }

            $binary = base64_decode($inline['data'], true);
            if ($binary === false || $binary === '') {
                continue;
            }

            $mime = 'image/png';
            if (!empty($inline['mimeType']) && is_string($inline['mimeType'])) {
                $mime = $inline['mimeType'];
            } elseif (!empty($inline['mime_type']) && is_string($inline['mime_type'])) {
                $mime = $inline['mime_type'];
            }

            $attachment_id = $this->save_image_to_media($binary, 0, $mime);
            if ($attachment_id) {
                $image_url = wp_get_attachment_url($attachment_id);
                if ($image_url) {
                    return array(
                        'success' => true,
                        'url' => $image_url,
                        'message' => '',
                    );
                }
            }
        }

        $block_reason = '';
        if (!empty($data['promptFeedback']['blockReason'])) {
            $block_reason = (string) $data['promptFeedback']['blockReason'];
        } elseif (!empty($data['candidates'][0]['finishReason']) && $data['candidates'][0]['finishReason'] !== 'STOP') {
            $block_reason = (string) $data['candidates'][0]['finishReason'];
        }

        return array(
            'success' => false,
            'url' => '',
            'message' => $block_reason !== ''
                ? sprintf(
                    /* translators: %s: Gemini finish/block reason */
                    __('Gemini did not return an image (%s). Try a different prompt or model.', 'ai-auto-post-image-generator'),
                    $block_reason
                )
                : __('Gemini did not return image data. Try a different prompt or model.', 'ai-auto-post-image-generator'),
        );
    }

    /**
     * Map plugin image size to a Gemini aspect ratio.
     *
     * @param string $size
     * @return string
     */
    private function map_size_to_gemini_aspect_ratio($size) {
        $size = preg_replace('/\s+/', '', strtolower((string) $size));
        $map = array(
            '256x256' => '1:1',
            '512x512' => '1:1',
            '1024x1024' => '1:1',
            '768x768' => '1:1',
            '1792x1024' => '16:9',
            '1024x1792' => '9:16',
            '1408x1024' => '4:3',
            '1024x1408' => '3:4',
        );

        if (isset($map[$size])) {
            return $map[$size];
        }

        if (preg_match('/^(\d+)x(\d+)$/', $size, $matches)) {
            $width = (int) $matches[1];
            $height = (int) $matches[2];
            if ($width > 0 && $height > 0) {
                if ($width === $height) {
                    return '1:1';
                }
                if ($width > $height) {
                    $ratio = $width / $height;
                    if ($ratio >= 1.7) {
                        return '16:9';
                    }
                    return '4:3';
                }
                $ratio = $height / $width;
                if ($ratio >= 1.7) {
                    return '9:16';
                }
                return '3:4';
            }
        }

        return '1:1';
    }



    /**
     * Generate image with Pollinations
     *
     * @param string $prompt
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    private function generate_with_pollinations($prompt, $schedule, $image_index = 0) {
        $api_key = AIAPG_Utils::get_pollinations_api_key();
        if ($api_key === '') {
            return array(
                'success' => false,
                'url' => '',
                'message' => __('Pollinations API key missing. Add your own key in Settings → API Keys, or restore the plugin default.', 'ai-auto-post-image-generator'),
            );
        }

        // Add randomization to generate different images for the same prompt
        $randomized_prompt = $this->add_randomization_to_prompt($prompt, $image_index);
        $encoded_prompt = rawurlencode($randomized_prompt);
        $width = 1024;
        $height = 1024;
        $this->parse_size(isset($schedule->image_size) ? $schedule->image_size : '1024x1024', $width, $height);

        // Pollinations API: https://gen.pollinations.ai/image/{prompt}?model=flux
        $url = add_query_arg(
            array(
                'model' => 'flux',
                'width' => $width,
                'height' => $height,
                'seed' => wp_rand(1, 999999),
                'key' => $api_key,
            ),
            'https://gen.pollinations.ai/image/' . $encoded_prompt
        );

        $request_args = array(
            'timeout' => 60,
            'redirection' => 5,
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
            ),
        );

        if (get_option('aiapg_enable_debug_log', false)) {
            // Debug placeholder
        }

        $response = wp_remote_get($url, $request_args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        if (is_array($content_type)) {
            $content_type = reset($content_type);
        }
        $content_type = strtolower((string) $content_type);
        
        // Debug logging
        if (get_option('aiapg_enable_debug_log', false)) {
            
            
            
        }
        
        if ($code < 200 || $code >= 300) {
            $error_message = 'Pollinations HTTP error: ' . $code . ' - ' . substr($body, 0, 200);
            if ((int) $code === 401 || (int) $code === 403) {
                $error_message = __('Pollinations authentication failed. Add your own API key in Settings (enter.pollinations.ai), or check the built-in key balance.', 'ai-auto-post-image-generator');
            } elseif ((int) $code === 402 || stripos($body, 'Insufficient balance') !== false || stripos($body, 'PAYMENT_REQUIRED') !== false) {
                $error_message = __('Pollinations has insufficient pollen balance on the current key. Add your own funded key in Settings → API Keys.', 'ai-auto-post-image-generator');
            }

            return array(
                'success' => false,
                'url' => '',
                'message' => $error_message
            );
        }

        // Check if the response is actually an image
        if (strpos($content_type, 'image/') !== 0) {
            $error_message = 'Pollinations returned non-image response (' . ($content_type ?: 'unknown content type') . '): ' . substr($body, 0, 200);
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return array(
                'success' => false,
                'url' => '',
                'message' => $error_message
            );
        }
        
        if (strlen($body) < 1000) {
            $error_message = 'Pollinations image response was too small: ' . substr($body, 0, 100);
            return array(
                'success' => false,
                'url' => '',
                'message' => $error_message
            );
        }

        $mime = (strpos($content_type, 'image/jpeg') === 0 || strpos($content_type, 'image/jpg') === 0)
            ? 'image/jpeg'
            : (strpos($content_type, 'image/webp') === 0 ? 'image/webp' : 'image/png');

        // Save image to media library
        if (get_option('aiapg_enable_debug_log', false)) {
            
            
        }
        
        $attachment_id = $this->save_image_to_media($body, 0, $mime);
        if ($attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return array(
                'success' => true,
                'url' => $image_url,
                'message' => ''
            );
        } else {
            if (get_option('aiapg_enable_debug_log', false)) {
                
                
            }
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Failed to save image to media library'
            );
        }
    }

    /**
     * Map size to DALL-E format
     *
     * @param string $size
     * @param string $model
     * @return string
     */
    private function map_size_to_dalle($size, $model = 'dall-e-2') {
        // DALL-E 2 supports: 256x256, 512x512, 1024x1024
        // DALL-E 3 supports: 1024x1024, 1792x1024, 1024x1792
        
        if ($model === 'dall-e-3') {
            switch ($size) {
                case '1024x1024':
                    return '1024x1024';
                case '1792x1024':
                    return '1792x1024';
                case '1024x1792':
                    return '1024x1792';
                default:
                    return '1024x1024'; // Default for DALL-E 3
            }
        } else {
            // DALL-E 2
            switch ($size) {
                case '256x256':
                    return '256x256';
                case '512x512':
                    return '512x512';
                case '1024x1024':
                default:
                    return '1024x1024';
            }
        }
    }

    /**
     * Parse size string to width and height
     *
     * @param string $size
     * @param int &$width
     * @param int &$height
     */
    private function parse_size($size, &$width, &$height) {
        $size = preg_replace('/\s+/', '', (string) $size);
        if (preg_match('/(\d+)x(\d+)/', $size, $matches)) {
            $width = intval($matches[1]);
            $height = intval($matches[2]);
        }
    }

    /**
     * Get available image models
     *
     * @return array
     */
    public function get_available_models() {
        return array(
            'dall-e-2' => array(
                'name' => 'DALL-E 2',
                'provider' => 'OpenAI',
                'sizes' => array('256x256', '512x512', '1024x1024'),
                'requires_key' => 'openai_api_key'
            ),
            'dall-e-3' => array(
                'name' => 'DALL-E 3',
                'provider' => 'OpenAI',
                'sizes' => array('1024x1024', '1792x1024', '1024x1792'),
                'requires_key' => 'openai_api_key'
            ),
            'gemini-3.1-flash-image' => array(
                'name' => 'Gemini 3.1 Flash Image',
                'provider' => 'Google',
                'sizes' => array('1024x1024', '1792x1024', '1024x1792'),
                'requires_key' => 'gemini_api_key'
            ),
            'gemini-3.1-flash-lite-image' => array(
                'name' => 'Gemini 3.1 Flash Lite Image',
                'provider' => 'Google',
                'sizes' => array('1024x1024', '1792x1024', '1024x1792'),
                'requires_key' => 'gemini_api_key'
            ),
            'gemini-3-pro-image' => array(
                'name' => 'Gemini 3 Pro Image',
                'provider' => 'Google',
                'sizes' => array('1024x1024', '1792x1024', '1024x1792'),
                'requires_key' => 'gemini_api_key'
            ),
            'gemini-2.5-flash-image' => array(
                'name' => 'Gemini 2.5 Flash Image (Legacy)',
                'provider' => 'Google',
                'sizes' => array('1024x1024', '1792x1024', '1024x1792'),
                'requires_key' => 'gemini_api_key'
            ),
            'pollinations' => array(
                'name' => 'Pollinations (Flux)',
                'provider' => 'Pollinations',
                'sizes' => array('1024x1024'),
                'requires_key' => null
            ),
             'leonardo' => array(
                 'name' => 'Leonardo.AI (Auto-Model Selection)',
                 'provider' => 'Leonardo.AI',
                 'sizes' => array('512x512', '768x768', '1024x1024', '1024x1408', '1408x1024'),
                 'requires_key' => 'leonardo_api_key'
             )
        );
    }

    /**
     * Check if model is available
     *
     * @param string $model
     * @return bool
     */
    public function is_model_available($model) {
        $models = $this->get_available_models();

        if (strpos((string) $model, 'gemini') === 0 && strpos((string) $model, 'image') !== false) {
            return !empty(get_option('aiapg_gemini_api_key'));
        }
        
        if (!isset($models[$model])) {
            return false;
        }

        $required_key = $models[$model]['requires_key'];
        if ($required_key === null) {
            return true;
        }
        $api_key = get_option('aiapg_' . $required_key);
        
        return !empty($api_key);
    }

    /**
     * Save a binary image to the Media Library
     *
     * @param string $binary
     * @param int $post_id
     * @param string $mime
     * @return int Attachment ID or 0
     */
    private function save_image_to_media($binary, $post_id = 0, $mime = 'image/png') {
        if (empty($binary)) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return 0;
        }

        $upload_dir = wp_upload_dir();
        if (!empty($upload_dir['error'])) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return 0;
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        wp_mkdir_p($upload_dir['path']);

        // Determine file extension based on MIME type
        if (strpos($mime, 'jpeg') !== false || strpos($mime, 'jpg') !== false) {
            $ext = 'jpg';
        } elseif (strpos($mime, 'webp') !== false) {
            $ext = 'webp';
        } else {
            $ext = 'png';
        }
        $filename = 'aiapg-' . time() . '-' . wp_generate_password(6, false) . '.' . $ext;
        $filepath = trailingslashit($upload_dir['path']) . $filename;

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        // Write file
        if (file_put_contents($filepath, $binary) === false) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return 0;
        }

        // Prepare attachment
        $filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $filetype['type'] ?: $mime,
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
        );

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        $attach_id = wp_insert_attachment($attachment, $filepath, $post_id);
        if (!$attach_id) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return 0;
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attach_id, $filepath);
        wp_update_attachment_metadata($attach_id, $attach_data);

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        return $attach_id;
    }

    /**
     * Get model info
     *
     * @param string $model
     * @return array|null
     */
    public function get_model_info($model) {
        $models = $this->get_available_models();
        return isset($models[$model]) ? $models[$model] : null;
    }

    /**
     * Add randomization to the prompt for Pollinations
     *
     * @param string $prompt
     * @param int $image_index
     * @return string
     */
    private function add_randomization_to_prompt($prompt, $image_index) {
        // Array of artistic styles and variations to add variety
        $style_variations = array(
            'artistic style', 'digital painting', 'photorealistic', 'watercolor', 'oil painting',
            'sketch', 'cartoon', 'anime', 'cinematic', 'studio lighting', 'natural lighting',
            'dramatic', 'soft', 'vibrant', 'muted', 'warm tones', 'cool tones', 'high contrast',
            'low contrast', 'detailed', 'minimalist', 'abstract', 'impressionist', 'surreal'
        );
        
        // Array of composition variations
        $composition_variations = array(
            'close-up', 'wide shot', 'medium shot', 'bird\'s eye view', 'worm\'s eye view',
            'rule of thirds', 'symmetrical', 'asymmetrical', 'centered', 'off-center',
            'leading lines', 'framed', 'shallow depth of field', 'deep depth of field'
        );
        
        // Generate a seed based on image index and current time for consistent randomization
        $seed = $image_index + (time() % 1000);
        
        // Select random variations using wp_rand for better security
        $random_style = $style_variations[wp_rand(0, count($style_variations) - 1)];
        $random_composition = $composition_variations[wp_rand(0, count($composition_variations) - 1)];
        
        // Add random number for additional variety
        $random_number = wp_rand(1000, 9999);
        
        // Combine original prompt with variations
        $randomized_prompt = $prompt . ', ' . $random_style . ', ' . $random_composition . ', variation ' . $random_number;
        
        return $randomized_prompt;
    }
    

    


    /**
     * Generate image with Leonardo.AI
     *
     * @param string $prompt
     * @param object $schedule
     * @param int $image_index
     * @return array
     */
    private function generate_with_leonardo($prompt, $schedule, $image_index = 0) {
        $api_key = get_option('aiapg_leonardo_api_key');
        if (empty($api_key)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Leonardo.AI API key not configured.'
            );
        }

        // Force debug logging for Leonardo.AI testing
        
        
        
        

        // Parse image size
        $size_parts = explode('x', $schedule->image_size);
        $width = isset($size_parts[0]) ? (int)$size_parts[0] : 1024;
        $height = isset($size_parts[1]) ? (int)$size_parts[1] : 1024;

        // Get the best model and settings for this prompt
        $model_selection = $this->get_leonardo_model_for_prompt($prompt);
        
        

        // Create the primary model configuration
        $primary_model = array(
            'name' => $model_selection['modelName'],
            'modelId' => $model_selection['modelId'],
            'styleUUID' => $model_selection['styleUUID'],
            'contrast' => $model_selection['contrast']
        );

        // Define fallback models in order of preference (excluding the primary model)
        $fallback_models = array(
            array(
                'name' => 'Phoenix',
                'modelId' => 'de7d3faf-762f-48e0-b3b7-9d0ac3a3fcf3',
                'styleUUID' => '111dc692-d470-4eec-b791-3475abac4c46', // Dynamic
                'contrast' => 3.5
            ),
            array(
                'name' => 'Flux Dev',
                'modelId' => 'b2614463-296c-462a-9586-aafdb8f00e36',
                'styleUUID' => '556c1ee5-ec38-42e8-955a-1e82dad0ffa1', // None
                'contrast' => 3.5
            ),
            array(
                'name' => 'Lucid Origin',
                'modelId' => '7b592283-e8a7-4c5a-9ba6-d18c31f258b9',
                'styleUUID' => '6fedbf1f-4a17-45ec-84fb-92fe524a29ef', // Creative
                'contrast' => 3.5
            ),
            array(
                'name' => 'Lucid Realism',
                'modelId' => '05ce0082-2d80-4a2d-8653-4d1c85e2418e',
                'styleUUID' => '0d914779-c822-430a-b976-30075033f1c4', // Neutral
                'contrast' => 3.5
            )
        );

        // Remove the primary model from fallbacks if it's already there
        foreach ($fallback_models as $key => $fallback) {
            if ($fallback['modelId'] === $primary_model['modelId']) {
                unset($fallback_models[$key]);
                break;
            }
        }

        // Try the smart-selected primary model first
        
        $result = $this->try_leonardo_model($prompt, $api_key, $width, $height, $primary_model);
        
        if ($result['success']) {
            
            return $result;
        } else {
            
            // If it's a 500 error, try fallback models
            if (strpos($result['message'], '500') !== false) {
                // Try fallback models
                foreach ($fallback_models as $model_index => $model_config) {
                    
                    
                    $fallback_result = $this->try_leonardo_model($prompt, $api_key, $width, $height, $model_config);
                    
                    if ($fallback_result['success']) {
                        
                        return $fallback_result;
                    } else {
                        
                        // If it's a 500 error, try the next model
                        if (strpos($fallback_result['message'], '500') !== false) {
                            continue;
                        } else {
                            // If it's not a 500 error, return the error (might be API key issue, etc.)
                            return $fallback_result;
                        }
                    }
                }
            } else {
                // If it's not a 500 error, return the error (might be API key issue, etc.)
                return $result;
            }
        }

        // If all models failed, return the last error
        return array(
            'success' => false,
            'url' => '',
            'message' => 'All Leonardo.AI models failed. Primary model (' . $primary_model['name'] . ') and all fallback models returned errors. Last error: ' . $result['message']
        );
    }

    /**
     * Try a specific Leonardo.AI model
     *
     * @param string $prompt
     * @param string $api_key
     * @param int $width
     * @param int $height
     * @param array $model_config
     * @return array
     */
    private function try_leonardo_model($prompt, $api_key, $width, $height, $model_config) {
        $payload = array(
            'height' => $height,
            'width' => $width,
            'modelId' => $model_config['modelId'],
            'prompt' => $prompt,
            'num_images' => 1,
            'ultra' => false,
        );

        // Only add alchemy parameter if the model supports it
        if (isset($model_config['alchemy_support']) && $model_config['alchemy_support']) {
            $payload['alchemy'] = true; // Enable alchemy for better quality
        }

        // Add style-specific parameters
        if (!empty($model_config['styleUUID'])) {
            $payload['styleUUID'] = $model_config['styleUUID'];
        }
        if (!empty($model_config['contrast'])) {
            $payload['contrast'] = $model_config['contrast'];
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        $api_url = 'https://cloud.leonardo.ai/api/rest/v1/generations';
        
        $response = AIAPG_Utils::make_post_request(
            $api_url,
            array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ),
                'body' => wp_json_encode($payload)
            ),
            'Leonardo.AI Image Generation'
        );

        if (is_wp_error($response)) {
            
            return array(
                'success' => false,
                'url' => '',
                'message' => $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);

        
        

        if ($code < 200 || $code >= 300) {
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Leonardo HTTP error: ' . $code . ' - ' . substr($raw, 0, 200)
            );
        }

        $json = json_decode($raw, true);
        
        
        
        // Check for generation ID in the response
        $gen_id = '';
        if (isset($json['sdGenerationJob']['generationId'])) {
            $gen_id = $json['sdGenerationJob']['generationId'];
        } elseif (isset($json['generationId'])) {
            $gen_id = $json['generationId'];
        } elseif (isset($json['id'])) {
            $gen_id = $json['id'];
        }

        if (empty($gen_id)) {
            
            return array(
                'success' => false,
                'url' => '',
                'message' => 'No generation ID returned from Leonardo. Response: ' . substr($raw, 0, 200)
            );
        }

        

        // Poll for the generated image URL
        $img_url = $this->poll_leonardo_generation($gen_id, $api_key, 120);
        if (is_wp_error($img_url)) {
            return array(
                'success' => false,
                'url' => '',
                'message' => $img_url->get_error_message()
            );
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        // Download the image from the URL
        $image_response = AIAPG_Utils::make_get_request($img_url, array(), 'Leonardo.AI Image Download');
        if (is_wp_error($image_response)) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Failed to download image from Leonardo: ' . $image_response->get_error_message()
            );
        }

        $image_body = wp_remote_retrieve_body($image_response);
        if (empty($image_body)) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Downloaded image is empty'
            );
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        // Save the image to WordPress media library
        $attachment_id = $this->save_image_to_media($image_body, 0, 'image/png');
        if ($attachment_id) {
            $wordpress_url = wp_get_attachment_url($attachment_id);
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return array(
                'success' => true,
                'url' => $wordpress_url,
                'message' => ''
            );
        } else {
            return array(
                'success' => false,
                'url' => '',
                'message' => 'Failed to save Leonardo image to media library'
            );
        }
    }

    /**
     * Poll Leonardo generation for image URL
     *
     * @param string $gen_id
     * @param string $api_key
     * @param int $timeout
     * @return string|WP_Error
     */
    private function poll_leonardo_generation($gen_id, $api_key, $timeout = 120) {
        $url = 'https://cloud.leonardo.ai/api/rest/v1/generations/' . $gen_id;
        $start = time();

        do {
            sleep(2);
            try {
                $resp = AIAPG_Utils::make_get_request($url, array(
                    'headers' => array('Authorization' => 'Bearer ' . $api_key)
                ), 'Leonardo.AI Polling');

                if (is_wp_error($resp)) {
                    return $resp;
                }
            } catch (Exception $e) {
                
                continue;
            } catch (Error $e) {
                
                continue;
            }

            $code = wp_remote_retrieve_response_code($resp);
            $raw = wp_remote_retrieve_body($resp);

            
            

            if ($code >= 200 && $code < 300) {
                $j = json_decode($raw, true);
                
                
                
                // Check for image URL in the response
                $image_url = '';
                if (!empty($j['generations_by_pk']['generated_images'][0]['url'])) {
                    $image_url = $j['generations_by_pk']['generated_images'][0]['url'];
                } elseif (!empty($j['generations'][0]['generated_images'][0]['url'])) {
                    $image_url = $j['generations'][0]['generated_images'][0]['url'];
                } elseif (!empty($j['generated_images'][0]['url'])) {
                    $image_url = $j['generated_images'][0]['url'];
                } elseif (!empty($j['url'])) {
                    $image_url = $j['url'];
                }
                
                if (!empty($image_url)) {
                    
                    return $image_url;
                }
                
                // Check if generation is still in progress
                $status = '';
                if (isset($j['generations_by_pk']['status'])) {
                    $status = $j['generations_by_pk']['status'];
                } elseif (isset($j['generations'][0]['status'])) {
                    $status = $j['generations'][0]['status'];
                } elseif (isset($j['status'])) {
                    $status = $j['status'];
                }
                
                
                
                if ($status === 'FAILED') {
                    $failure_reason = '';
                    if (isset($j['generations_by_pk']['failure_reason'])) {
                        $failure_reason = $j['generations_by_pk']['failure_reason'];
                    } elseif (isset($j['generations'][0]['failure_reason'])) {
                        $failure_reason = $j['generations'][0]['failure_reason'];
                    }
                    return new WP_Error('leonardo_failed', 'Leonardo generation failed: ' . $failure_reason);
                }
            }

        } while (time() - $start < $timeout);

                 return new WP_Error('leonardo_timeout', 'Timed out waiting for Leonardo generation to complete.');
     }

     /**
      * Get the best Leonardo.AI model and style based on the prompt
      *
      * @param string $prompt
      * @return array
      */
     private function get_leonardo_model_for_prompt($prompt) {
         // Leonardo.AI models with their configurations
         $leonardo_models = array(
             'flux-dev' => array(
                 'name' => 'Flux Dev',
                 'modelId' => 'b2614463-296c-462a-9586-aafdb8f00e36',
                 'description' => 'Best for ultra-high-quality realistic images',
                 'keywords' => array('professional', 'realistic', 'photorealistic', 'photo', 'real', 'natural', 'lifelike', 'high quality', 'detailed', 'ultra', 'sharp', 'crisp', 'studio', 'commercial', 'photography'),
                 'weight' => 2, // Higher priority for realistic content
                 'default_style' => 'None',
                 'default_contrast' => 3.5,
                 'alchemy_support' => false // Flux Dev doesn't support alchemy
             ),
             'lucid-realism' => array(
                 'name' => 'Lucid Realism',
                 'modelId' => '05ce0082-2d80-4a2d-8653-4d1c85e2418e',
                 'description' => 'Best for photorealistic portraits and people',
                 'keywords' => array('portrait', 'person', 'face', 'human', 'people', 'man', 'woman', 'child', 'skin', 'eyes', 'smile', 'expression', 'headshot', 'profile', 'selfie'),
                 'weight' => 3, // Highest priority for people/portraits
                 'default_style' => 'Pro color photography',
                 'default_contrast' => 3.5,
                 'alchemy_support' => true
             ),
             'lucid-origin' => array(
                 'name' => 'Lucid Origin',
                 'modelId' => '7b592283-e8a7-4c5a-9ba6-d18c31f258b9',
                 'description' => 'Best for artistic and creative illustrations',
                 'keywords' => array('artistic', 'creative', 'art', 'painting', 'drawing', 'illustration', 'digital art', 'concept art', 'fantasy', 'abstract', 'surreal', 'magical', 'stylized', 'design', 'graphic'),
                 'weight' => 2, // High priority for artistic content
                 'default_style' => 'Creative',
                 'default_contrast' => 3.5,
                 'alchemy_support' => true
             ),
             'leonardo-creative' => array(
                 'name' => 'Leonardo Creative',
                 'modelId' => '6bef9f1b-29cb-40c7-b9df-32b51c1f67d3',
                 'description' => 'Best for creative and imaginative scenes',
                 'keywords' => array('imaginative', 'creative', 'colorful', 'vibrant', 'dreamy', 'whimsical', 'cartoon', 'anime', 'stylized', 'artistic style', 'unique', 'original'),
                 'weight' => 1.5,
                 'default_style' => 'Creative',
                 'default_contrast' => 3.5,
                 'alchemy_support' => true
             ),
             'phoenix' => array(
                 'name' => 'Phoenix',
                 'modelId' => 'de7d3faf-762f-48e0-b3b7-9d0ac3a3fcf3',
                 'description' => 'Best for general purpose and versatile images',
                 'keywords' => array('general', 'object', 'thing', 'item', 'product', 'animal', 'pet', 'food', 'landscape', 'nature', 'outdoor', 'indoor', 'scene', 'environment', 'architecture', 'building'),
                 'weight' => 1, // Default weight for general content
                 'default_style' => 'Dynamic',
                 'default_contrast' => 3.5,
                 'alchemy_support' => true
             )
         );

         // Leonardo.AI style UUIDs (updated from official documentation)
         $leonardo_styles = array(
             '3D Render' => 'debdf72a-91a4-467b-bf61-cc02bdeb69c6',
             'Bokeh' => '9fdc5e8c-4d13-49b4-9ce6-5a74cbb19177',
             'Cinematic' => 'a5632c7c-ddbb-4e2f-ba34-8456ab3ac436',
             'Cinematic Concept' => '33abbb99-03b9-4dd7-9761-ee98650b2c88',
             'Creative' => '6fedbf1f-4a17-45ec-84fb-92fe524a29ef',
             'Dynamic' => '111dc692-d470-4eec-b791-3475abac4c46',
             'Fashion' => '594c4a08-a522-4e0e-b7ff-e4dac4b6b622',
             'Graphic Design Pop Art' => '2e74ec31-f3a4-4825-b08b-2894f6d13941',
             'Graphic Design Vector' => '1fbb6a68-9319-44d2-8d56-2957ca0ece6a',
             'HDR' => '97c20e5c-1af6-4d42-b227-54d03d8f0727',
             'Illustration' => '645e4195-f63d-4715-a3f2-3fb1e6eb8c70',
             'Macro' => '30c1d34f-e3a9-479a-b56f-c018bbc9c02a',
             'Minimalist' => 'cadc8cd6-7838-4c99-b645-df76be8ba8d8',
             'Moody' => '621e1c9a-6319-4bee-a12d-ae40659162fa',
             'None' => '556c1ee5-ec38-42e8-955a-1e82dad0ffa1',
             'Portrait' => '8e2bc543-6ee2-45f9-bcd9-594b6ce84dcd',
             'Pro B&W photography' => '22a9a7d2-2166-4d86-80ff-22e2643adbcf',
             'Pro color photography' => '7c3f932b-a572-47cb-9b9b-f20211e63b5b',
             'Pro film photography' => '581ba6d6-5aac-4492-bebe-54c424a0d46e',
             'Portrait Fashion' => '0d34f8e1-46d4-428f-8ddd-4b11811fa7c9',
             'Ray Traced' => 'b504f83c-3326-4947-82e1-7fe9e839ec0f',
             'Sketch (B&W)' => 'be8c6b58-739c-4d44-b9c1-b032ed308b61',
             'Sketch (Color)' => '093accc3-7633-4ffd-82da-d34000dfc0d6',
             'Stock Photo' => '5bdc3f2a-1be6-4d1c-8e77-992a30824a2c',
             'Vibrant' => 'dee282d3-891f-4f73-ba02-7f8131e5541b'
         );

         // Convert prompt to lowercase for matching
         $prompt_lower = strtolower($prompt);
         
         // Score each model based on keyword matches with weights
         $model_scores = array();
         foreach ($leonardo_models as $model_key => $model_config) {
             $score = 0;
             $weight = isset($model_config['weight']) ? $model_config['weight'] : 1;
             
             foreach ($model_config['keywords'] as $keyword) {
                 if (strpos($prompt_lower, $keyword) !== false) {
                     $score += $weight; // Apply model weight to each keyword match
                 }
             }
             $model_scores[$model_key] = $score;
             
             // Debug logging for model scoring
             if ($score > 0) {
                 
             }
         }

         // Select the model with the highest score, or default to Phoenix
         $selected_model_key = 'phoenix'; // Default
         $highest_score = 0;
         foreach ($model_scores as $model_key => $score) {
             if ($score > $highest_score) {
                 $highest_score = $score;
                 $selected_model_key = $model_key;
             }
         }

         $selected_model = $leonardo_models[$selected_model_key];

         // Determine contrast based on prompt content
         $contrast = $selected_model['default_contrast'];
         if (strpos($prompt_lower, 'dramatic') !== false || strpos($prompt_lower, 'moody') !== false || 
             strpos($prompt_lower, 'high contrast') !== false || strpos($prompt_lower, 'bold') !== false ||
             strpos($prompt_lower, 'dark') !== false || strpos($prompt_lower, 'shadow') !== false) {
             $contrast = 4.0; // High contrast
         } elseif (strpos($prompt_lower, 'soft') !== false || strpos($prompt_lower, 'gentle') !== false ||
                   strpos($prompt_lower, 'pastel') !== false || strpos($prompt_lower, 'light') !== false ||
                   strpos($prompt_lower, 'subtle') !== false || strpos($prompt_lower, 'delicate') !== false) {
             $contrast = 3.0; // Low contrast
         }

         // Override style based on specific prompt content
         $style_name = $selected_model['default_style'];
         if (strpos($prompt_lower, 'portrait') !== false || strpos($prompt_lower, 'person') !== false ||
             strpos($prompt_lower, 'face') !== false || strpos($prompt_lower, 'headshot') !== false) {
             $style_name = 'Pro color photography';
         } elseif (strpos($prompt_lower, 'fashion') !== false || strpos($prompt_lower, 'model') !== false ||
                   strpos($prompt_lower, 'style') !== false) {
             $style_name = 'Fashion';
         } elseif (strpos($prompt_lower, 'cinematic') !== false || strpos($prompt_lower, 'movie') !== false ||
                   strpos($prompt_lower, 'film') !== false) {
             $style_name = 'Cinematic';
         } elseif (strpos($prompt_lower, 'illustration') !== false || strpos($prompt_lower, 'drawing') !== false ||
                   strpos($prompt_lower, 'art') !== false) {
             $style_name = 'Illustration';
         } elseif (strpos($prompt_lower, 'minimalist') !== false || strpos($prompt_lower, 'simple') !== false ||
                   strpos($prompt_lower, 'clean') !== false) {
             $style_name = 'Minimalist';
         } elseif (strpos($prompt_lower, 'vibrant') !== false || strpos($prompt_lower, 'colorful') !== false ||
                   strpos($prompt_lower, 'bright') !== false) {
             $style_name = 'Vibrant';
         }

         // Get the final style UUID
         $style_uuid = isset($leonardo_styles[$style_name]) ? $leonardo_styles[$style_name] : '';

         // Log the model selection for debugging
         
         
         

         return array(
             'modelId' => $selected_model['modelId'],
             'styleUUID' => $style_uuid,
             'contrast' => $contrast,
             'modelName' => $selected_model['name'],
             'alchemy_support' => isset($selected_model['alchemy_support']) ? $selected_model['alchemy_support'] : true
         );
     }
 }
