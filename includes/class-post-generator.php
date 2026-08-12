<?php
/**
 * Post Generator Class
 *
 * Handles AI text generation and post creation.
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIAPG_Post_Generator Class
 *
 * @since 1.0.0
 */
class AIAPG_Post_Generator {

    /**
     * Tracks shortcode image generation stats during a run
     */
    private $shortcode_image_stats = array();
    
    /**
     * Stores original prompt data for category matching
     */
    private $original_prompt_data = array();

    /**
     * Per-topic metadata (categories, publish_at) keyed by topic index during a run
     *
     * @var array
     */
    private $topic_meta = array();

    /**
     * Metadata for the topic currently being generated
     *
     * @var array
     */
    private $current_topic_meta = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks if needed
    }

    /**
     * Generate posts for a schedule
     *
     * @param object $schedule
     * @return array
     */
    public function generate_posts_for_schedule($schedule) {
        $result = array(
            'success' => false,
            'posts_created' => 0,
            'message' => '',
            'errors' => array()
        );

        try {
            // Debug logging at start
            
            
            
            // Validate schedule object
            if (!$schedule || !is_object($schedule)) {
                
                $result['message'] = 'Invalid schedule object';
                return $result;
            }

            // Normalize posts per run (UI allows 1–10; DB may hold 0 or out-of-range from older saves)
            $schedule->posts_per_run = max(1, min(10, absint($schedule->posts_per_run ?? 1)));
            
            // Debug logging
            if (get_option('aiapg_enable_debug_log', false)) {
                
                
            }

            // Get topics to generate posts for
            
            $topics = $this->get_topics_for_schedule($schedule);
            
            
            if (empty($topics)) {
                $result['message'] = __('No topics found for this schedule.', 'ai-auto-post-image-generator');
                
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                return $result;
            }

            if (get_option('aiapg_enable_debug_log', false)) {
                
            }

            $posts_created = 0;
            $errors = array();
            $this->topic_meta = array();

            // Generate posts for each topic
            foreach ($topics as $topic_index => $topic) {
                $topic_text = $topic;
                $this->current_topic_meta = array();

                if (is_array($topic)) {
                    $topic_text = isset($topic['topic']) ? $topic['topic'] : '';
                    $this->current_topic_meta = $topic;
                    $this->topic_meta[$topic_index] = $topic;
                }

                if ($topic_text === '' || $topic_text === null) {
                    continue;
                }
                
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                
                $post_result = $this->generate_single_post($schedule, $topic_text);
                
                
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                
                if ($post_result['success']) {
                    $posts_created++;
                    
                } else {
                    $errors[] = $post_result['message'];
                    
                }

                // Stop if we've reached the limit
                if ($posts_created >= $schedule->posts_per_run) {
                    
                    break;
                }
            }

            $this->current_topic_meta = array();
            $this->topic_meta = array();

            $result['posts_created'] = $posts_created;
            $result['errors'] = $errors;

            if (!empty($errors)) {
                $result['success'] = $posts_created > 0;
                $first_error = is_string($errors[0] ?? '') ? $errors[0] : __('Unknown error occurred.', 'ai-auto-post-image-generator');
                if ($posts_created === 0) {
                    $result['message'] = sprintf(
                        /* translators: 1: Number of errors, 2: First error message */
                        __('Generated 0 posts with %1$d errors. First error: %2$s', 'ai-auto-post-image-generator'),
                        count($errors),
                        $first_error
                    );
                } else {
                    $result['message'] = sprintf(
                        /* translators: 1: Number of posts created, 2: Number of errors, 3: First error message */
                        __('Generated %1$d posts with %2$d errors. First error: %3$s', 'ai-auto-post-image-generator'),
                        $posts_created,
                        count($errors),
                        $first_error
                    );
                }
            } else {
                $result['success'] = true;
                $result['message'] = sprintf(
                    /* translators: %d: Number of posts created */
                    __('Successfully generated %d posts.', 'ai-auto-post-image-generator'),
                    $posts_created
                );
            }
            
            

        } catch (Exception $e) {
            
            
            $result['message'] = $e->getMessage();
        } catch (Error $e) {
            
            
            $result['message'] = 'Fatal error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Get topics for a schedule
     *
     * @param object $schedule
     * @return array
     */
    private function get_topics_for_schedule($schedule) {
        $topics = array();

        // Check if using categories or custom prompts
        $categories = !empty($schedule->categories) ? maybe_unserialize($schedule->categories) : array();
        $custom_prompts = !empty($schedule->custom_prompts) ? maybe_unserialize($schedule->custom_prompts) : array();

        // Ensure both are arrays
        if (!is_array($categories)) {
            $categories = array();
        }
        if (!is_array($custom_prompts)) {
            $custom_prompts = array();
        }

        // Debug logging
        if (get_option('aiapg_enable_debug_log', false)) {
            
            
            
        }

        if (!empty($categories)) {
            // Generate topics based on categories
            $topics = $this->generate_topics_from_categories($categories, $schedule->posts_per_run, $schedule->text_model);
        } elseif (!empty($custom_prompts)) {
            $this->original_prompt_data = $custom_prompts;
            $topics = $this->build_prompt_topics($custom_prompts, $schedule->posts_per_run, $schedule->text_model);
        } else {
            // Fallback: Generate generic topics if no categories or prompts are configured
            $topics = $this->generate_fallback_topics($schedule->posts_per_run);
        }

        // Debug logging
        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        return $topics;
    }

    /**
     * Generate topics from categories
     *
     * @param array $categories
     * @param int $count
     * @return array
     */
    private function generate_topics_from_categories($categories, $count, $model = 'gpt-3.5-turbo') {
        $topics = array();

        // Get category names
        $category_names = array();
        foreach ($categories as $category_id) {
            if (!empty($category_id)) {
                $category = get_term($category_id, 'category');
                if ($category && !is_wp_error($category)) {
                    $category_names[] = $category->name;
                }
            }
        }

        // If we have category names, generate topics based on them
        if (!empty($category_names)) {
            // Generate the exact number of topics requested
            $topics = $this->generate_topics_from_category_names($category_names, $count, $model);
        }

        // Ensure we return exactly the requested number of topics
        return array_slice($topics, 0, $count);
    }

    /**
     * Generate topics from category names
     *
     * @param array $category_names
     * @param int $count
     * @return array
     */
    private function generate_topics_from_category_names($category_names, $count, $model = 'gpt-3.5-turbo') {
        if (empty($category_names)) {
            return array();
        }


        $prompt = sprintf(
            'Generate %d unique and engaging blog post topics related to these categories: %s. ' .
            'Each topic should be specific, useful, and sound like a real editorial idea a human editor would approve. ' .
            'Do NOT use clickbait, fake urgency, scams, or spam phrasing (no "urgent", "act now", "claim your", "limited time", tax refund bait, etc.). ' .
            'Return only the topics, one per line, without numbering.',
            $count,
            implode(', ', $category_names)
        );

        $response = $this->generate_text_with_ai($prompt, $model);
        
        if ($response['success'] && isset($response['content'])) {
            $topics = array_filter(array_map('trim', explode("\n", $response['content'])));
            return array_slice($topics, 0, $count);
        }

        // Fallback: create simple topics based on category names
        $topics = array();
        foreach ($category_names as $category_name) {
            $topics[] = 'Latest Trends in ' . $category_name;
            if (count($topics) >= $count) break;
        }

        return array_slice($topics, 0, $count);
    }

    /**
     * Generate fallback topics when no categories or prompts are configured
     *
     * @param int $count
     * @return array
     */
    private function generate_fallback_topics($count) {
        // Default topics that can be used as fallback
        $default_topics = array(
            'Technology Trends and Innovations',
            'Digital Marketing Strategies',
            'Business Growth and Development',
            'Health and Wellness Tips',
            'Personal Finance Management',
            'Travel and Adventure',
            'Food and Cooking',
            'Fitness and Exercise',
            'Education and Learning',
            'Environmental Sustainability',
            'Creative Arts and Design',
            'Science and Discovery',
            'Social Media Marketing',
            'Entrepreneurship and Startups',
            'Productivity and Time Management'
        );

        // Shuffle the array to get random topics
        shuffle($default_topics);
        
        // Return the requested number of topics
        return array_slice($default_topics, 0, $count);
    }

    /**
     * Generate a single post
     *
     * @param object $schedule
     * @param string $topic
     * @return array
     */
    private function generate_single_post($schedule, $topic) {
        $result = array(
            'success' => false,
            'post_id' => 0,
            'message' => ''
        );

        try {
            
            
            // Validate inputs
            if (!$schedule || !is_object($schedule)) {
                
                $result['message'] = 'Invalid schedule object';
                return $result;
            }
            
            if (empty($topic)) {
                
                $result['message'] = 'Empty topic';
                return $result;
            }
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }

            // Extract a meaningful focus keyword from the topic
            $keyword_data = $this->extract_meaningful_focus_keyword($topic);
            $focus_keyword = $keyword_data['focus_keyword'];
            $original_prompt = $keyword_data['original_prompt'];
            
            // Generate title from the full topic (not a truncated keyword alone)
            $title_result = $this->generate_title($original_prompt, $schedule->text_model, $focus_keyword);
            
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            
            if (!$title_result['success']) {
                $result['message'] = $title_result['message'];
                
                return $result;
            }

            $title = isset($title_result['content']) ? $title_result['content'] : '';
            $title = $this->sanitize_generated_title($title);

            // Validate title
            if (empty($title)) {
                $result['message'] = __('Failed to generate title.', 'ai-auto-post-image-generator');
                
                return $result;
            }

            if (get_option('aiapg_enable_debug_log', false)) {
                
            }

            // Always write about the original topic; focus keyword is for light SEO only
            $content_result = $this->generate_content($original_prompt, $title, $schedule, $focus_keyword);
            
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            
            if (!$content_result['success']) {
                $result['message'] = $content_result['message'];
                
                return $result;
            }

            $content = isset($content_result['content']) ? $content_result['content'] : '';

            // Validate content
            if (empty($content)) {
                $result['message'] = __('Failed to generate content.', 'ai-auto-post-image-generator');
                
                return $result;
            }

            // Generate meta description
            $meta_description = $this->generate_meta_description($focus_keyword, $title, $schedule->text_model);
            
            // Generate SEO-friendly slug
            $seo_slug = $this->generate_seo_slug($title, $topic);

            // Create the post (schedule override, else Settings default — draft by default).
            $default_status = AIAPG_Utils::get_default_post_status();
            if (!empty($schedule->post_status)) {
                $default_status = AIAPG_Utils::normalize_post_status($schedule->post_status, $default_status);
            }

            $post_data = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $default_status,
                'post_author' => get_option('aiapg_post_author', get_current_user_id()),
                'post_type' => 'post',
                'post_name' => $seo_slug
            );

            // Prompt-level publish datetime (WordPress scheduled / future post).
            $publish_at = '';
            if (!empty($this->current_topic_meta['publish_at'])) {
                $publish_at = AIAPG_Utils::normalize_datetime($this->current_topic_meta['publish_at']);
            }

            if ($publish_at !== '') {
                $publish_gmt_ts = AIAPG_Utils::local_datetime_to_gmt_timestamp($publish_at);
                $post_data['post_date'] = $publish_at;
                $post_data['post_date_gmt'] = get_gmt_from_date($publish_at);

                if ($publish_gmt_ts && $publish_gmt_ts > time()) {
                    // Future date forces WordPress scheduled status regardless of draft/publish setting.
                    $post_data['post_status'] = 'future';
                }
                // Past/now: keep the configured status (draft / pending / private / publish).
            }

            // Add categories based on content source
            $post_categories = array();

            if (!empty($this->current_topic_meta['categories']) && is_array($this->current_topic_meta['categories'])) {
                $post_categories = array_map('intval', $this->current_topic_meta['categories']);
                $post_categories = array_filter($post_categories);
            }
            
            // Check if using category-based generation
            $categories = !empty($schedule->categories) ? maybe_unserialize($schedule->categories) : array();
            if (!is_array($categories)) {
                $categories = array();
            }
            
            // Check if using custom prompts with categories
            $custom_prompts = !empty($schedule->custom_prompts) ? maybe_unserialize($schedule->custom_prompts) : array();
            if (!is_array($custom_prompts)) {
                $custom_prompts = array();
            }
            
            // First try to extract categories from custom prompts (highest priority)
            if (empty($post_categories) && (!empty($custom_prompts) || !empty($this->original_prompt_data))) {
                $prompt_categories_found = false;
                $prompts_to_check = !empty($this->original_prompt_data) ? $this->original_prompt_data : $custom_prompts;
                
                // For custom prompts with generated topics, use the first prompt's categories
                // This is a simple approach that works for most use cases
                if (!empty($this->original_prompt_data)) {
                    // We're using generated topics, so use the first prompt's categories
                    $first_prompt = $prompts_to_check[0];
                    if (is_array($first_prompt) && isset($first_prompt['categories'])) {
                        $prompt_categories = $first_prompt['categories'];
                        if (is_array($prompt_categories) && !empty($prompt_categories)) {
                            $post_categories = $prompt_categories;
                            $prompt_categories_found = true;
                        }
                    }
                } else {
                    // Direct prompt matching (for non-generated topics)
                    foreach ($prompts_to_check as $prompt_data) {
                        $prompt_text = '';
                        if (is_string($prompt_data)) {
                            $prompt_text = $prompt_data;
                        } elseif (is_array($prompt_data) && isset($prompt_data['text'])) {
                            $prompt_text = $prompt_data['text'];
                        }
                        
                        // Direct prompt matching
                        if ($prompt_text === $topic && is_array($prompt_data) && isset($prompt_data['categories'])) {
                            $prompt_categories = $prompt_data['categories'];
                            if (is_array($prompt_categories) && !empty($prompt_categories)) {
                                $post_categories = $prompt_categories;
                                $prompt_categories_found = true;
                                break;
                            }
                        }
                    }
                }
                
                // If no categories found in prompts, use schedule categories as fallback
                if (!$prompt_categories_found && !empty($categories)) {
                    $post_categories = $categories;
                }
            } elseif (empty($post_categories) && !empty($categories)) {
                // Use categories from "By Categories" mode (when no custom prompts)
                $post_categories = $categories;
            }
            
            // Assign categories to the post
            if (!empty($post_categories)) {
                $post_data['post_category'] = $post_categories;
            }

            
            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                $result['message'] = $post_id->get_error_message();
                
                return $result;
            }

            // Set meta description for SEO
            if (!empty($meta_description)) {
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_description);
                update_post_meta($post_id, '_rank_math_description', $meta_description);
                update_post_meta($post_id, '_aioseo_description', $meta_description);
            }

            // Set focus keyword for SEO plugins (check if installed first)
            $this->set_seo_plugin_keywords($post_id, $focus_keyword);

            

            // Generate and attach images if enabled
            if (get_option('aiapg_enable_debug_log', false)) {
                
                
            }
            
            if ($schedule->enable_images == 1) {
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                
                $this->attach_images_to_post($post_id, $schedule, $title, $focus_keyword);
                
            } else {
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                
            }

            $result['success'] = true;
            $result['post_id'] = $post_id;
            $result['message'] = __('Post created successfully.', 'ai-auto-post-image-generator');
            
            

        } catch (Exception $e) {
            
            
            $result['message'] = $e->getMessage();
        } catch (Error $e) {
            
            
            $result['message'] = 'Fatal error: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Generate title using AI
     *
     * @param string $topic
     * @param string $model
     * @param string $focus_keyword
     * @return array
     */
    private function generate_title($topic, $model, $focus_keyword = '') {
        $focus_keyword = trim((string) $focus_keyword);
        if ($focus_keyword === '') {
            $focus_keyword = $this->extract_focus_keyword_from_text($topic, $this->is_detailed_instruction_prompt($topic));
        }

        $prompt = sprintf(
            'Write one clear, natural blog post title about this topic:
"%s"

Preferred SEO phrase (include only if it fits naturally): "%s"

Rules:
- Keep the title under 60 characters when possible.
- Match the topic exactly. Do not invent unrelated subjects (no tax refunds, scams, or off-topic claims).
- Sound like a real human editor headline — informative and specific.
- Forbidden spam patterns: urgent, act now, claim your, limited time, last chance, free money, click here, you won\'t believe, shocking, must read now.
- Do not awkwardly stack words. No grammar errors.
- Add a number only if the topic is naturally a list/tips guide.
- Return only the title. No quotes, no explanation.',
            $topic,
            $focus_keyword
        );

        return $this->generate_text_with_ai($prompt, $model);
    }

    /**
     * Clean AI title output (strip quotes / leftover labels).
     *
     * @param string $title
     * @return string
     */
    private function sanitize_generated_title($title) {
        $title = trim((string) $title);
        $title = preg_replace('/^["\'“”‘’]+|["\'“”‘’]+$/u', '', $title);
        $title = preg_replace('/^(title|headline)\s*:\s*/i', '', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        return trim($title);
    }

    /**
     * Generate content using AI
     *
     * @param string $topic
     * @param string $title
     * @param object $schedule
     * @param string $focus_keyword Optional precomputed focus keyword.
     * @return array
     */
    private function generate_content($topic, $title, $schedule, $focus_keyword = '') {
        // Check if we need to include image shortcodes
        $needs_image_shortcodes = false;
        if ($schedule->enable_images == 1) {
            // Always include shortcodes if images_per_post > 1, regardless of placement
            // This allows for both featured images and inline images
            $needs_image_shortcodes = (
                $schedule->images_per_post > 1 || 
                $schedule->image_placement === 'inline' || 
                $schedule->image_placement === 'both'
            );
        }

        // Check if this is a detailed prompt that should be followed exactly
        $is_detailed_prompt = $this->is_detailed_instruction_prompt($topic);
        
        if ($focus_keyword === '') {
            $focus_keyword = $this->extract_focus_keyword_from_text($topic, $is_detailed_prompt);
        }
        $length = self::get_content_length_config($schedule);
        $keyword_max = max(2, min(4, (int) $length['keyword_max']));
        
        if ($is_detailed_prompt) {
            // For detailed prompts, follow the prompt exactly with light natural SEO
            $prompt = sprintf(
                'Follow this EXACT prompt and create a comprehensive blog post:

        PROMPT: "%s"

        Title: %s

        Writing requirements:
        - Follow the prompt EXACTLY — stay on that subject only
        - Write about %d-%d words
        - Prefer plain ASCII punctuation (straight quotes, regular hyphens). Avoid em dashes, curly quotes, and fancy Unicode symbols.
        - Do NOT use clickbait or spam phrasing (no "urgent", "act now", "claim your", fake scarcity)

        Natural SEO (do not keyword-stuff):
        - Preferred phrase: "%s"
        - Mention it naturally about %d times total (intro once is enough; never force it into every heading)
        - Headings must be readable sentences about the real section topic — never glue the SEO phrase awkwardly into H2/H3 text

        Content Structure:
        - Use proper heading hierarchy: H2 for main sections, H3 for subsections
        - NEVER use H1 tags (WordPress uses the post title as H1)
        - Short paragraphs (2-3 sentences)
        - Conversational, informative tone with a clear conclusion
        - HTML only with <p>, <h2>, <h3> (NO H1)

        Make the post valuable for readers. Quality and clarity beat keyword density.',
                $topic,
                $title,
                $length['min'],
                $length['max'],
                $focus_keyword,
                $keyword_max
            );
        } else {
            // For simple topics, write about the topic with light natural SEO
            $prompt = sprintf(
                'Write a comprehensive blog post about this topic: "%s"

        Title: %s

        Writing requirements:
        - Stay strictly on the topic above
        - Write about %d-%d words
        - Prefer plain ASCII punctuation (straight quotes, regular hyphens). Avoid em dashes, curly quotes, and fancy Unicode symbols.
        - Do NOT use clickbait or spam phrasing (no "urgent", "act now", "claim your", fake scarcity)

        Natural SEO (do not keyword-stuff):
        - Preferred phrase: "%s"
        - Mention it naturally about %d times total
        - Headings must describe the section clearly — never force the SEO phrase into every H2/H3

        Content Structure:
        - H2 for main sections, H3 for subsections
        - NEVER use H1 tags
        - Short paragraphs (2-3 sentences)
        - Conversational, informative tone with a clear conclusion
        - HTML only with <p>, <h2>, <h3> (NO H1)

        Make the post valuable for readers. Quality and clarity beat keyword density.',
                $topic,
                $title,
                $length['min'],
                $length['max'],
                $focus_keyword,
                $keyword_max
            );
        }

        // Add image shortcode instructions if needed
        if ($needs_image_shortcodes) {
            $image_count = max(1, intval($schedule->images_per_post));
            
            // Add placement-specific instructions
            $placement_instructions = '';
            if ($schedule->image_placement === 'both') {
                $placement_instructions = '
- NOTE: This post will use "Featured + Inline" image placement. The first shortcode will become the featured image, and the remaining shortcodes will be placed inline within the content.';
            } elseif ($schedule->image_placement === 'featured') {
                $placement_instructions = '
- NOTE: This post will use "Featured Image Only" placement. All shortcodes will be used for the featured image.';
            } elseif ($schedule->image_placement === 'inline') {
                $placement_instructions = '
- NOTE: This post will use "Inline Images Only" placement. All shortcodes will be placed inline within the content.';
            }
            
            $prompt .= sprintf(
                '
- CRITICAL: You MUST insert exactly %d image placeholder(s) throughout the content using this shortcode format EXACTLY:
  [aiapg_image prompt="descriptive image prompt"]

- Use only straight ASCII double quotes (") around the prompt. Never use curly quotes.
- Always close the shortcode with ]. Never leave it unfinished.
- Keep the prompt on one line when possible.

- Place each image placeholder naturally **within or after specific paragraphs** where an illustration would enhance the reader\'s understanding.

- Each image prompt must:
  • Be **highly descriptive** and relevant to the surrounding paragraph.  
  • Capture **visual details** (environment, style, colors, objects, mood).  
  • Match the **tone** of the article (e.g., educational, inspirational, technical).  
  • Remain concise and usable as a standalone image description.

- Example usage inside content:
  > The new workspace setup encourages focus and productivity.  
  > [aiapg_image prompt="modern office with glass walls and people working on laptops, cozy Italian café terrace with coffee cups and notebooks, peaceful city park with green trees and stone pathways, minimalist living room interior with sunlight through large windows, futuristic AI holographic brain with neon lights, university library with tall bookshelves and students studying, warm natural sunlight, cinematic atmosphere, ultra realistic, highly detailed, 8k quality, photorealistic"]

- Ensure the image prompts vary across the article (not repetitive).  
- Do not cluster all placeholders together — **distribute them naturally** across the content.  
- The total number of placeholders MUST match exactly **%d** - this is a strict requirement.%s

- Return the final response as fully written article content, with image shortcodes embedded.',
                $image_count,
                $image_count,
                $placement_instructions
            );
        } else {
            
        }

        $prompt .= '

Write the complete (SEO-optimized title) blog post:';

        $result = $this->generate_text_with_ai($prompt, $schedule->text_model, $length['max_tokens']);
        
        // Validate shortcode count if needed
        if ($needs_image_shortcodes && $result['success']) {
            $image_count = max(1, intval($schedule->images_per_post));
            $shortcode_count = count($this->extract_aiapg_image_shortcodes($result['content']));

            if ($shortcode_count !== $image_count) {
                
            }
        }
        
        return $result;
    }

    /**
     * Generate text using AI
     *
     * @param string $prompt
     * @param string $model
     * @param int    $max_tokens Optional max output tokens override.
     * @return array
     */
    private function generate_text_with_ai($prompt, $model, $max_tokens = 0) {
        $result = array(
            'success' => false,
            'content' => '',
            'message' => ''
        );

        $max_tokens = absint($max_tokens);
        if ($max_tokens < 500) {
            $max_tokens = 4000;
        }

        // Determine which AI provider to use
        if (!empty($model) && AIAPG_Utils::is_openai_text_model($model)) {
            return $this->generate_with_openai($prompt, $model, $max_tokens);
        }

        if (!empty($model) && strpos($model, 'gemini') === 0) {
            return $this->generate_with_gemini($prompt, $model, $max_tokens);
        }

        // Custom IDs: prefer OpenAI when only that key is set, else Gemini when only that key is set.
        $openai_key = get_option('aiapg_openai_api_key');
        $gemini_key = get_option('aiapg_gemini_api_key');
        if (!empty($model) && !empty($openai_key) && empty($gemini_key)) {
            return $this->generate_with_openai($prompt, $model, $max_tokens);
        }
        if (!empty($model) && !empty($gemini_key) && empty($openai_key)) {
            return $this->generate_with_gemini($prompt, $model, $max_tokens);
        }

        $result['message'] = __('Unsupported AI model. Use an OpenAI (gpt-*) or Gemini (gemini-*) model ID, or set only one provider API key for custom IDs.', 'ai-auto-post-image-generator');
        return $result;
    }

    /**
     * Generate text with OpenAI
     *
     * @param string $prompt
     * @param string $model
     * @param int    $max_tokens
     * @return array
     */
    private function generate_with_openai($prompt, $model, $max_tokens = 4000) {
        $api_key = get_option('aiapg_openai_api_key');
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'content' => '',
                'message' => __('OpenAI API key not configured.', 'ai-auto-post-image-generator')
            );
        }

        $token_limit = max(500, absint($max_tokens));
        $payload = array(
            'model' => $model,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
        );

        // GPT-5 / o-series prefer max_completion_tokens and often reject custom temperature.
        if (preg_match('/^(gpt-5|o[0-9]|codex-)/i', $model)) {
            $payload['max_completion_tokens'] = $token_limit;
        } else {
            $payload['max_tokens'] = $token_limit;
            $payload['temperature'] = 0.7;
        }

        $response = AIAPG_Utils::make_post_request('https://api.openai.com/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'body' => wp_json_encode($payload)
        ), 'OpenAI Text Generation');

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'content' => '',
                'message' => $response->get_error_message()
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['choices'][0]['message']['content'])) {
            $content = trim($data['choices'][0]['message']['content']);
            
            // Clean up the content - remove markdown formatting and extra characters
            $content = $this->clean_ai_content($content);
            
            return array(
                'success' => true,
                'content' => $content,
                'message' => ''
            );
        } else {
            return array(
                'success' => false,
                'content' => '',
                'message' => isset($data['error']['message']) ? $data['error']['message'] : __('Unknown error occurred.', 'ai-auto-post-image-generator')
            );
        }
    }

    /**
     * Generate text with Gemini
     *
     * @param string $prompt
     * @param string $model
     * @param int    $max_tokens
     * @return array
     */
    private function generate_with_gemini($prompt, $model, $max_tokens = 8000) {
        $api_key = get_option('aiapg_gemini_api_key');
        
        if (get_option('aiapg_enable_debug_log', false)) {
            
            
            
        }
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'content' => '',
                'message' => __('Gemini API key not configured.', 'ai-auto-post-image-generator')
            );
        }

        $model = AIAPG_Utils::normalize_gemini_text_model($model);

        $response = AIAPG_Utils::make_post_request(
            'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . rawurlencode($api_key),
            array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                // Text generation is synchronous. Avoid minutes of hidden retries.
                'timeout' => 30,
                'aiapg_max_retries' => 0,
                'body' => wp_json_encode(
                    array(
                        'contents' => array(
                            array(
                                'parts' => array(
                                    array(
                                        'text' => $prompt,
                                    ),
                                ),
                            ),
                        ),
                        'generationConfig' => array(
                            'maxOutputTokens' => max(500, absint($max_tokens)),
                            'temperature' => 0.7,
                        ),
                    )
                ),
            ),
            'Gemini Text Generation'
        );

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'content' => '',
                'message' => $response->get_error_message(),
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (get_option('aiapg_enable_debug_log', false)) {
            
        }

        $extracted = AIAPG_Utils::extract_gemini_text_from_response($data);
        if ($extracted !== '') {
            $content = $this->clean_ai_content($extracted);
            if ($content !== '') {
                return array(
                    'success' => true,
                    'content' => $content,
                    'message' => '',
                );
            }
        }

        if (isset($data['error']['message']) && is_string($data['error']['message'])) {
            $error_message = $data['error']['message'];
        } elseif (isset($data['promptFeedback']['blockReason'])) {
            $error_message = sprintf(
                /* translators: %s: Gemini block reason */
                __('Gemini blocked the prompt: %s', 'ai-auto-post-image-generator'),
                $data['promptFeedback']['blockReason']
            );
        } elseif (!empty($data['candidates'][0]['finishReason']) && $data['candidates'][0]['finishReason'] !== 'STOP') {
            $error_message = sprintf(
                /* translators: 1: HTTP status, 2: Gemini finish reason, 3: model id */
                __('Gemini returned no text (HTTP %1$d, finishReason=%2$s, model=%3$s).', 'ai-auto-post-image-generator'),
                $status_code,
                $data['candidates'][0]['finishReason'],
                $model
            );
        } elseif ($status_code >= 400) {
            $error_message = sprintf(
                /* translators: 1: HTTP status, 2: model id */
                __('Gemini HTTP %1$d for model %2$s.', 'ai-auto-post-image-generator'),
                $status_code,
                $model
            );
        } else {
            $error_message = sprintf(
                /* translators: %s: Gemini model id */
                __('Gemini returned an empty response for model %s.', 'ai-auto-post-image-generator'),
                $model
            );
        }

        return array(
            'success' => false,
            'content' => '',
            'message' => $error_message,
        );
    }

    /**
     * Attach images to post
     *
     * @param int $post_id
     * @param object $schedule
     * @param string $title
     */
    private function attach_images_to_post($post_id, $schedule, $title, $focus_keyword = '') {
        if ($schedule->enable_images != 1) {
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            return;
        }

        if (get_option('aiapg_enable_debug_log', false)) {
            
            
            
        }

        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        // Check if content has shortcodes that need processing
        $content = $this->normalize_aiapg_image_shortcodes($post->post_content);
        if ($content !== $post->post_content) {
            wp_update_post(
                array(
                    'ID' => $post_id,
                    'post_content' => $content,
                )
            );
            $post = get_post($post_id);
            $content = $post ? $post->post_content : $content;
        }

        $shortcodes = $this->extract_aiapg_image_shortcodes($content);
        $has_shortcodes = !empty($shortcodes);

        if ($has_shortcodes) {
            // Process shortcodes with specific prompts
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }
            $this->process_image_shortcodes($post_id, $schedule);
        } else {
            // Fallback to original method
            $images = aiapg()->image_generator->generate_images($title, $schedule);
            
            if (get_option('aiapg_enable_debug_log', false)) {
                
            }

            if (!empty($images) && is_array($images)) {
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
                
                // Set featured image
                if ($schedule->image_placement === 'featured' || $schedule->image_placement === 'both') {
                    if (get_option('aiapg_enable_debug_log', false)) {
                        
                    }
                    // Use the focus keyword as alt text for featured image
                    $alt_text = !empty($focus_keyword) ? $focus_keyword : get_the_title($post_id);
                    $this->set_featured_image_with_alt($post_id, $images[0], $alt_text);
                }

                // Add inline images
                if ($schedule->image_placement === 'inline' || $schedule->image_placement === 'both') {
                    if (get_option('aiapg_enable_debug_log', false)) {
                        
                    }
                    $this->add_inline_images($post_id, $images, $focus_keyword);
                }
            } else {
                if (get_option('aiapg_enable_debug_log', false)) {
                    
                }
            }
        }

        // Never leave raw / broken shortcodes in published content.
        $final_post = get_post($post_id);
        if ($final_post) {
            $cleaned = $this->strip_aiapg_image_shortcodes($final_post->post_content);
            if ($cleaned !== $final_post->post_content) {
                wp_update_post(
                    array(
                        'ID' => $post_id,
                        'post_content' => $cleaned,
                    )
                );
            }
        }
    }

    /**
     * Set featured image with specific alt text from shortcode
     *
     * @param int $post_id
     * @param string $image_url
     * @param string $alt_text
     */
    private function set_featured_image_with_alt($post_id, $image_url, $alt_text) {
        if (get_option('aiapg_enable_debug_log', false)) {
            // Debug logging disabled for production compatibility
        }
        
        // Check if the URL is already a WordPress media URL
        if (strpos($image_url, get_site_url()) !== false) {
            // Extract attachment ID from URL
            $attachment_id = attachment_url_to_postid($image_url);
            if ($attachment_id) {
                set_post_thumbnail($post_id, $attachment_id);
                
                // Update alt text for existing attachment
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
                update_post_meta($attachment_id, '_wp_attachment_image_alt_text', $alt_text);
                
                if (get_option('aiapg_enable_debug_log', false)) {
                    // Debug logging disabled for production compatibility
                }
                return;
            }
        }
        
        // Otherwise, sideload the image
        $upload = media_sideload_image($image_url, $post_id, $alt_text, 'id');
        
        if (!is_wp_error($upload)) {
            set_post_thumbnail($post_id, $upload);
            
            // Set the alt text we want
            update_post_meta($upload, '_wp_attachment_image_alt', $alt_text);
            update_post_meta($upload, '_wp_attachment_image_alt_text', $alt_text);
            
            // Update the attachment post title and content
            wp_update_post(array(
                'ID' => $upload,
                'post_title' => $alt_text,
                'post_content' => $alt_text
            ));
            
            // Force update the attachment metadata
            $attachment_meta = wp_get_attachment_metadata($upload);
            if ($attachment_meta) {
                $attachment_meta['image_meta']['caption'] = $alt_text;
                wp_update_attachment_metadata($upload, $attachment_meta);
            }
            
            if (get_option('aiapg_enable_debug_log', false)) {
                // Debug logging disabled for production compatibility
            }
        } else {
            if (get_option('aiapg_enable_debug_log', false)) {
                // Debug logging disabled for production compatibility
            }
        }
    }

    /**
     * Set featured image
     *
     * @param int $post_id
     * @param string $image_url
     */

    /**
     * Add inline images to post content
     *
     * @param int $post_id
     * @param array $images
     */
    private function add_inline_images($post_id, $images, $focus_keyword = '') {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        $content = $post->post_content;

        // Use focus keyword as alt text, fallback to post title
        $alt_text = !empty($focus_keyword) ? $focus_keyword : get_the_title($post_id);

        // Add images at the end of the content
        $image_html = '<div class="ai-generated-images">';
        foreach ($images as $image_url) {
            $image_html .= sprintf(
                '<img src="%s" alt="%s" class="ai-generated-image" style="max-width: 100%%; height: auto; margin: 20px 0;">',
                esc_url($image_url),
                esc_attr($alt_text)
            );
        }
        $image_html .= '</div>';

        $content .= $image_html;

        wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content
        ));
    }

     /**
      * Process image shortcodes in post content
      *
      * @param int $post_id
      * @param object $schedule
      */
     private function process_image_shortcodes($post_id, $schedule) {
         $post = get_post($post_id);
         if (!$post) {
             return;
         }

         $content = $this->normalize_aiapg_image_shortcodes($post->post_content);
         $featured_image_set = false;

         if (get_option('aiapg_enable_debug_log', false)) {
             
         }

         $matches = $this->extract_aiapg_image_shortcodes($content);

         if (empty($matches)) {
             if (get_option('aiapg_enable_debug_log', false)) {
                 
             }
             $content = $this->strip_aiapg_image_shortcodes($content);
             if ($content !== $post->post_content) {
                 wp_update_post(
                     array(
                         'ID' => $post_id,
                         'post_content' => $content,
                     )
                 );
             }
             return;
         }

         if (get_option('aiapg_enable_debug_log', false)) {
             
         }

         // Process each shortcode
         $shortcode_index = 0;
         foreach ($matches as $match) {
             $shortcode = $match['shortcode'];
             $prompt = html_entity_decode($match['prompt'], ENT_QUOTES, get_bloginfo('charset'));
             $shortcode_index++;

             if (get_option('aiapg_enable_debug_log', false)) {
                 
             }

             // Generate image for this specific prompt
             $image_result = aiapg()->image_generator->generate_image_from_prompt($prompt, $schedule, 0);

             // Track shortcode generation results
             if (empty($this->shortcode_image_stats)) {
                 $this->shortcode_image_stats = array(
                     'requested' => 0,
                     'successful' => 0,
                     'failed' => 0,
                     'errors' => array()
                 );
             }
             
             $this->shortcode_image_stats['requested']++;

             if ($image_result['success'] && !empty($image_result['url'])) {
                 $this->shortcode_image_stats['successful']++;
                 $image_url = $image_result['url'];

                 if (get_option('aiapg_enable_debug_log', false)) {
                     
                 }

                 // Handle featured + inline placement logic
                 if ($schedule->image_placement === 'both') {
                     // For 'both' placement: first shortcode becomes featured image, rest become inline
                     if ($shortcode_index === 1) {
                         // First shortcode: set as featured image and remove from content
                         $this->set_featured_image_with_alt($post_id, $image_url, $prompt);
                         $featured_image_set = true;
                         if (get_option('aiapg_enable_debug_log', false)) {
                             
                         }
                         $content = str_replace($shortcode, '', $content);
                     } else {
                         // Subsequent shortcodes: add as inline images
                         $seo_alt_text = $this->generate_seo_alt_text($post->post_title, $prompt);
                         $image_html = sprintf(
                             '<img src="%s" alt="%s" class="ai-generated-image" style="max-width: 100%%; height: auto; margin: 20px 0; display: block;">',
                             esc_url($image_url),
                             esc_attr($seo_alt_text)
                         );
                         if (get_option('aiapg_enable_debug_log', false)) {
                             
                         }
                         $content = str_replace($shortcode, $image_html, $content);
                     }
                 } else {
                     // Handle other placement types
                     // Set featured image if needed and not already set
                     if (!$featured_image_set && ($schedule->image_placement === 'featured')) {
                         $this->set_featured_image_with_alt($post_id, $image_url, $prompt);
                         $featured_image_set = true;
                         if (get_option('aiapg_enable_debug_log', false)) {
                             
                         }
                         $content = str_replace($shortcode, '', $content);
                     } else {
                         // Replace shortcode with image HTML if inline placement
                         if ($schedule->image_placement === 'inline') {
                             $seo_alt_text = $this->generate_seo_alt_text($post->post_title, $prompt);
                             $image_html = sprintf(
                                 '<img src="%s" alt="%s" class="ai-generated-image" style="max-width: 100%%; height: auto; margin: 20px 0; display: block;">',
                                 esc_url($image_url),
                                 esc_attr($seo_alt_text)
                             );
                             if (get_option('aiapg_enable_debug_log', false)) {
                                 
                             }
                             $content = str_replace($shortcode, $image_html, $content);
                         } else {
                             // Remove shortcode if not inline placement
                             $content = str_replace($shortcode, '', $content);
                         }
                     }
                 }
             } else {
                 $this->shortcode_image_stats['failed']++;
                 $this->shortcode_image_stats['errors'][] = array(
                     'prompt' => $prompt,
                     'error' => isset($image_result['message']) ? $image_result['message'] : 'Unknown error',
                     'model' => $schedule->image_model
                 );
                 
                 // Remove failed shortcode
                 if (get_option('aiapg_enable_debug_log', false)) {
                     
                 }
                 $content = str_replace($shortcode, '', $content);
             }
         }

         // Update post content
         $content = $this->strip_aiapg_image_shortcodes($content);
         wp_update_post(array(
             'ID' => $post_id,
             'post_content' => $content
         ));

         if (get_option('aiapg_enable_debug_log', false)) {
             
         }
     }

    /**
     * Normalize curly quotes / spacing in [aiapg_image] shortcodes.
     *
     * @param string $content
     * @return string
     */
    private function normalize_aiapg_image_shortcodes($content) {
        if (!is_string($content) || $content === '' || stripos($content, '[aiapg_image') === false) {
            return $content;
        }

        // Convert smart quotes to ASCII first (shortcode attribute parsers need them).
        $content = str_replace(
            array("\u{201C}", "\u{201D}", "\u{201E}", "\u{201F}", "\u{00AB}", "\u{00BB}", "\u{2018}", "\u{2019}", "\u{201A}", "\u{201B}"),
            array('"', '"', '"', '"', '"', '"', "'", "'", "'", "'"),
            $content
        );

        // Normalize attribute spacing: [aiapg_image prompt = "..."] → [aiapg_image prompt="..."]
        $content = preg_replace(
            '/\[aiapg_image\s+prompt\s*=\s*(["\'])(.*?)\1\s*\]/isu',
            '[aiapg_image prompt="$2"]',
            $content
        );

        // Drop truncated shortcodes that never close (common when the model cuts off mid-tag).
        $content = preg_replace('/\[aiapg_image(?![^\]]*\]).*$/isu', '', $content);

        return $content;
    }

    /**
     * Extract complete [aiapg_image prompt="..."] shortcodes.
     *
     * @param string $content
     * @return array<int, array{shortcode:string,prompt:string}>
     */
    private function extract_aiapg_image_shortcodes($content) {
        $matches = array();
        if (!is_string($content) || $content === '') {
            return $matches;
        }

        if (preg_match_all('/\[aiapg_image\s+prompt=(["\'])(.*?)\1\s*\]/isu', $content, $found, PREG_SET_ORDER)) {
            foreach ($found as $item) {
                $matches[] = array(
                    'shortcode' => $item[0],
                    'prompt' => trim($item[2]),
                );
            }
        }

        return $matches;
    }

    /**
     * Remove any remaining [aiapg_image ...] tags (complete or broken).
     *
     * @param string $content
     * @return string
     */
    private function strip_aiapg_image_shortcodes($content) {
        if (!is_string($content) || $content === '' || stripos($content, '[aiapg_image') === false) {
            return $content;
        }

        $content = preg_replace('/\[aiapg_image\s+[^\]]*\]/iu', '', $content);
        $content = preg_replace('/\[aiapg_image(?![^\]]*\]).*$/isu', '', $content);
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        return trim($content);
    }

    /**
     * Get shortcode image generation statistics
     *
     * @return array
     */
    public function get_shortcode_image_stats() {
        if (empty($this->shortcode_image_stats)) {
            $this->shortcode_image_stats = array(
                'requested' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => array()
            );
        }
        return $this->shortcode_image_stats;
    }

    /**
     * Generate meta description using AI
     *
     * @param string $topic
     * @param string $title
     * @param string $model
     * @return string
     */
    private function generate_meta_description($topic, $title, $model) {
        $prompt = sprintf(
            'Write a natural SEO meta description for this post.

Topic/keyword: "%s"
Title: %s

Rules:
- About 140-160 characters
- Mention "%s" once only if it fits naturally
- Summarize the real value of the article
- No spam phrasing (urgent, act now, claim your, clickbait)
- Return only the meta description',
            $topic,
            $title,
            $topic
        );

        $result = $this->generate_text_with_ai($prompt, $model);
        
        if ($result['success'] && !empty($result['content'])) {
            return trim($result['content']);
        }
        
        // Fallback meta description with exact focus keyword
        return sprintf('%s: Essential guide with tips and strategies. Learn everything you need to know about %s for maximum success.', $topic, $topic);
    }

    /**
     * Generate SEO-friendly URL slug
     *
     * @param string $title
     * @param string $topic
     * @return string
     */
    private function generate_seo_slug($title, $topic) {
        // Use the exact focus keyword from topic
        $focus_keyword = strtolower(trim($topic));
        
        // Create slug from focus keyword only (no title words)
        $slug = sanitize_title($focus_keyword);
        
        // Limit slug length for SEO (max 50 characters for shorter URLs)
        if (strlen($slug) > 50) {
            $slug = substr($slug, 0, 50);
            $slug = rtrim($slug, '-');
        }
        
        // If slug is still too long, truncate more aggressively
        if (strlen($slug) > 40) {
            $words = explode('-', $slug);
            $slug = '';
            foreach ($words as $word) {
                if (strlen($slug . $word) <= 40) {
                    $slug .= ($slug ? '-' : '') . $word;
                } else {
                    break;
                }
            }
        }
        
        // Ensure slug is unique
        $original_slug = $slug;
        $counter = 1;
        while (get_page_by_path($slug, OBJECT, 'post')) {
            $slug = $original_slug . '-' . $counter;
            // If adding counter makes it too long, truncate the original
            if (strlen($slug) > 50) {
                $original_slug = substr($original_slug, 0, 45);
                $slug = $original_slug . '-' . $counter;
            }
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Generate SEO-optimized alt text for images (unified function)
     *
     * @param string $post_title
     * @param string $image_prompt Optional image prompt for context
     * @return string
     */
    private function generate_seo_alt_text($post_title, $image_prompt = '') {
        // Extract focus keyword from post title
        $focus_keyword = $this->extract_focus_keyword_from_title($post_title);
        
        // Use image prompt if provided, otherwise use post title
        $alt_text = !empty($image_prompt) ? $image_prompt : $post_title;
        
        // If the alt text doesn't contain the focus keyword, add it
        if (!empty($focus_keyword) && strpos(strtolower($alt_text), strtolower($focus_keyword)) === false) {
            $alt_text = $focus_keyword . ' - ' . $alt_text;
        }
        
        // Limit alt text length (max 125 characters for SEO)
        if (strlen($alt_text) > 125) {
            $alt_text = substr($alt_text, 0, 125);
            $alt_text = rtrim($alt_text, ' -');
        }
        
        return $alt_text;
    }

    /**
     * Extract a meaningful focus keyword from the topic/prompt
     * Smart detection: if prompt is detailed/instructional, use it as-is for content generation
     * If prompt is short/topic-like, extract focus keyword for SEO
     *
     * @param string $topic
     * @return array ['focus_keyword' => string, 'is_detailed_prompt' => bool]
     */
    private function extract_meaningful_focus_keyword($topic) {
        $topic = trim($topic);
        
        // Check if this is a detailed prompt that should be followed exactly
        $is_detailed_prompt = $this->is_detailed_instruction_prompt($topic);
        
        // Extract focus keyword using unified function
        $focus_keyword = $this->extract_focus_keyword_from_text($topic, $is_detailed_prompt);
        
        return array(
            'focus_keyword' => $focus_keyword,
            'is_detailed_prompt' => $is_detailed_prompt,
            'original_prompt' => $topic
        );
    }
    
    /**
     * Check if the prompt is a detailed instruction that should be followed exactly
     *
     * @param string $prompt
     * @return bool
     */
    private function is_detailed_instruction_prompt($prompt) {
        // Indicators of detailed prompts
        $detailed_indicators = array(
            'write', 'create', 'generate', 'make', 'produce', 'develop',
            'about', 'regarding', 'concerning', 'on the topic of',
            'explain', 'describe', 'discuss', 'analyze', 'review',
            'step by step', 'how to', 'guide to', 'tutorial on',
            'comprehensive', 'detailed', 'in-depth', 'complete guide',
            'tips for', 'best practices', 'everything you need to know',
            'ultimate guide', 'complete overview'
        );
        
        $prompt_lower = strtolower($prompt);
        
        // Check for detailed indicators
        foreach ($detailed_indicators as $indicator) {
            if (strpos($prompt_lower, $indicator) !== false) {
                return true;
            }
        }
        
        // Check length and complexity
        $word_count = str_word_count($prompt);
        $has_punctuation = preg_match('/[.!?]/', $prompt);
        $has_commas = substr_count($prompt, ',') > 1;
        
        // If it's long, has punctuation, or multiple clauses, likely a detailed prompt
        if ($word_count > 8 || $has_punctuation || $has_commas) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract focus keyword from text (unified function for both detailed prompts and simple topics)
     *
     * @param string $text
     * @param bool $is_detailed_prompt
     * @return string
     */
    private function extract_focus_keyword_from_text($text, $is_detailed_prompt = false) {
        // Common words to remove (expanded list for detailed prompts)
        $common_words = array(
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'how', 'what', 'when', 'where', 'why', 'guide', 'tips', 'best', 'top', 'ultimate', 'complete',
            'discover', 'find', 'get', 'learn', 'about', 'from', 'on', 'high', 'demand', 'amazon', 'deals',
            // Spam / fake-urgency fillers that produce bad Rank Math phrases
            'urgent', 'claim', 'your', 'now', 'act', 'free', 'limited', 'exclusive', 'amazing', 'shocking',
            'secret', 'secrets', 'instantly', 'guaranteed', 'click', 'here', 'today', 'immediately'
        );
        
        // Add more words for detailed prompts
        if ($is_detailed_prompt) {
            $common_words = array_merge($common_words, array(
                'write', 'create', 'generate', 'make', 'produce', 'develop', 'explain', 'describe', 'discuss',
                'analyze', 'review', 'step', 'tutorial', 'comprehensive', 'detailed', 'in-depth', 'everything',
                'you', 'need', 'know', 'overview', 'practices'
            ));
        }
        
        // Convert to lowercase and split into words
        $words = explode(' ', strtolower(trim($text)));
        $meaningful_words = array();
        
        foreach ($words as $word) {
            // Clean the word (remove punctuation)
            $clean_word = preg_replace('/[^a-z0-9]/', '', $word);
            
            // Skip if too short, common word, or empty
            if (strlen($clean_word) > 2 && !in_array($clean_word, $common_words, true)) {
                $meaningful_words[] = $clean_word;
            }
        }
        
        // Take first 2-3 meaningful words for focus keyword
        if (!empty($meaningful_words)) {
            $focus_keyword = implode(' ', array_slice($meaningful_words, 0, 3));
            
            // Limit length
            if (strlen($focus_keyword) > 30) {
                $focus_keyword = implode(' ', array_slice($meaningful_words, 0, 2));
            }
            
            if (strlen($focus_keyword) > 20) {
                $focus_keyword = $meaningful_words[0];
            }
            
            return ucwords($focus_keyword);
        }
        
        // Fallback
        $fallback_words = array_slice($words, 0, 3);
        return ucwords(implode(' ', $fallback_words));
    }

    /**
     * Extract focus keyword from post title
     *
     * @param string $title
     * @return string
     */
    private function extract_focus_keyword_from_title($title) {
        // Remove common words and extract the main keyword
        $common_words = array('the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'how', 'what', 'when', 'where', 'why', 'guide', 'tips', 'best', 'top', 'ultimate', 'complete');
        
        $words = explode(' ', strtolower($title));
        $keywords = array();
        
        foreach ($words as $word) {
            $clean_word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($clean_word) > 3 && !in_array($clean_word, $common_words)) {
                $keywords[] = $clean_word;
            }
        }
        
        // Return the first significant keyword
        return !empty($keywords) ? $keywords[0] : '';
    }


    /**
     * Set focus keywords for all installed SEO plugins
     *
     * @param int $post_id
     * @param string $focus_keyword
     */
    private function set_seo_plugin_keywords($post_id, $focus_keyword) {
        $plugins_set = array();
        
        // Check for Yoast SEO
        if ($this->is_plugin_active('wordpress-seo/wp-seo.php') || class_exists('WPSEO_Options')) {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
            $plugins_set[] = 'Yoast SEO';
            
            if (get_option('aiapg_enable_debug_log', false)) {
                // Debug logging disabled for production compatibility
            }
        }
        
        // Check for All in One SEO (AIOSEO)
        if ($this->is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php') || 
            $this->is_plugin_active('aioseo/aioseo.php') || 
            class_exists('AIOSEO')) {
            update_post_meta($post_id, '_aioseo_focus_keyphrase', $focus_keyword);
            $plugins_set[] = 'All in One SEO';
            
            if (get_option('aiapg_enable_debug_log', false)) {
                // Debug logging disabled for production compatibility
            }
        }
        
        // Check for Rank Math
        if ($this->is_plugin_active('seo-by-rank-math/rank-math.php') || class_exists('RankMath')) {
            update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
            $plugins_set[] = 'Rank Math';
            
            if (get_option('aiapg_enable_debug_log', false)) {
                // Debug logging disabled for production compatibility
            }
        }
        
        // Log summary
        if (get_option('aiapg_enable_debug_log', false)) {
            // Debug logging disabled for production compatibility
        }
    }

    /**
     * Check if a plugin is active
     *
     * @param string $plugin_file
     * @return bool
     */
    private function is_plugin_active($plugin_file) {
        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        return is_plugin_active($plugin_file);
    }

    /**
     * Build topic list from custom prompts, preserving categories and publish_at.
     *
     * When any prompt has a publish_at value, posts are mapped 1:1 from prompts
     * (up to posts_per_run) so each generated post keeps its scheduled datetime.
     *
     * @param array  $custom_prompts
     * @param int    $count
     * @param string $model
     * @return array
     */
    private function build_prompt_topics($custom_prompts, $count, $model = 'gpt-3.5-turbo') {
        $normalized = array();
        $has_publish_at = false;

        foreach ($custom_prompts as $prompt_data) {
            if (is_string($prompt_data)) {
                $text = trim($prompt_data);
                $categories = array();
                $publish_at = '';
            } elseif (is_array($prompt_data) && !empty($prompt_data['text'])) {
                $text = trim($prompt_data['text']);
                $categories = isset($prompt_data['categories']) && is_array($prompt_data['categories'])
                    ? array_map('intval', $prompt_data['categories'])
                    : array();
                $publish_at = AIAPG_Utils::normalize_datetime($prompt_data['publish_at'] ?? '');
            } else {
                continue;
            }

            if ($text === '') {
                continue;
            }

            if ($publish_at !== '') {
                $has_publish_at = true;
            }

            $normalized[] = array(
                'text'       => $text,
                'categories' => array_values(array_filter($categories)),
                'publish_at' => $publish_at,
            );
        }

        if (empty($normalized)) {
            return array();
        }

        // Specific date/time publishing: one post per prompt with its publish_at.
        if ($has_publish_at) {
            $topics = array();
            $slice = array_slice($normalized, 0, $count);
            foreach ($slice as $prompt) {
                $topics[] = array(
                    'topic'      => $prompt['text'],
                    'categories' => $prompt['categories'],
                    'publish_at' => $prompt['publish_at'],
                );
            }
            return $topics;
        }

        // Default: AI invents topics from prompts, then round-robin attach prompt meta.
        $prompt_texts = array_column($normalized, 'text');
        $generated = $this->generate_topics_from_prompts($prompt_texts, $count, $model);
        $topics = array();
        $prompt_count = count($normalized);

        foreach (array_values($generated) as $index => $topic_text) {
            $prompt = $normalized[$index % $prompt_count];
            $topics[] = array(
                'topic'      => $topic_text,
                'categories' => $prompt['categories'],
                'publish_at' => $prompt['publish_at'],
            );
        }

        return $topics;
    }

    /**
     * Generate topics from prompts by understanding what the prompt is asking for
     *
     * @param array $prompts
     * @param int $count
     * @return array
     */
    private function generate_topics_from_prompts($prompts, $count, $model = 'gpt-3.5-turbo') {
        if (empty($prompts)) {
            return array();
        }

        // Combine all prompts into one instruction
        $combined_prompt = implode('; ', $prompts);
        
        $prompt = sprintf(
            'Based on this instruction: "%s"
            
Generate %d specific topics that fulfill this instruction. Do NOT write topics about the instruction itself, but rather topics that the instruction is asking for.

Examples:
- If instruction is "Discover today\'s trending news from around the world" → Generate specific news topics like "Tesla Stock Surges After Q3 Earnings", "New Climate Agreement Signed"
- If instruction is "Write about healthy recipes" → Generate specific recipe topics like "Mediterranean Quinoa Bowl Recipe", "Low-Carb Cauliflower Pizza"
- If instruction is "Share productivity tips" → Generate specific tip topics like "5-Minute Morning Routine for Maximum Productivity", "Time Blocking Techniques That Actually Work"

Each topic should be:
- Specific and actionable
- SEO-friendly
- Directly related to what the instruction is asking for
- Not about the instruction itself

Return only the specific topics, one per line, without numbering.',
            $combined_prompt,
            $count
        );

        $response = $this->generate_text_with_ai($prompt, $model);
        
        if ($response['success'] && isset($response['content'])) {
            $topics = array_filter(array_map('trim', explode("\n", $response['content'])));
            return array_slice($topics, 0, $count);
        }

        // Fallback: use the prompts as topics if AI generation fails
        return array_slice($prompts, 0, $count);
    }
    

    /**
     * Clean AI-generated content
     *
     * Removes invisible Unicode artifacts and normalizes typographic characters
     * commonly flagged by AI-content detectors (em dashes, smart quotes, etc.).
     *
     * @param string $content
     * @return string
     */
    private function clean_ai_content($content) {
        if (!is_string($content) || $content === '') {
            return '';
        }

        // Remove markdown code fences
        $content = preg_replace('/```html\s*/i', '', $content);
        $content = preg_replace('/```[a-z]*\s*/i', '', $content);
        $content = preg_replace('/```/', '', $content);

        // Strip leading/trailing leftover backticks
        $content = preg_replace('/^`+/', '', $content);
        $content = preg_replace('/`+$/', '', $content);

        // Convert common Markdown headings to HTML so raw "##" signs do not appear.
        // H1 is intentionally downgraded because WordPress already uses the post title as H1.
        $content = preg_replace('/^\s*###\s+(.+?)\s*#*\s*$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^\s*##\s+(.+?)\s*#*\s*$/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^\s*#\s+(.+?)\s*#*\s*$/m', '<h2>$1</h2>', $content);

        // Normalize / drop broken image shortcodes early.
        $content = $this->normalize_aiapg_image_shortcodes($content);

        // Invisible / zero-width characters used as AI copy-paste fingerprints.
        $invisible = array(
            "\u{FEFF}", // ZWNBSP / BOM
            "\u{200B}", // Zero-width space
            "\u{200C}", // Zero-width non-joiner
            "\u{200D}", // Zero-width joiner
            "\u{2060}", // Word joiner
            "\u{00AD}", // Soft hyphen
            "\u{200E}", // LTR mark
            "\u{200F}", // RTL mark
            "\u{202A}", // LRE
            "\u{202B}", // RLE
            "\u{202C}", // PDF
            "\u{202D}", // LRO
            "\u{202E}", // RLO
            "\u{2066}", // LRI
            "\u{2067}", // RLI
            "\u{2068}", // FSI
            "\u{2069}", // PDI
            "\u{2062}", // Invisible times
            "\u{2063}", // Invisible separator
            "\u{2064}", // Invisible plus
            "\u{180E}", // Mongolian vowel separator
            "\u{034F}", // Combining grapheme joiner
        );
        $content = str_replace($invisible, '', $content);

        // Also strip UTF-8 BOM if present as bytes
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Variation selectors (U+FE00–U+FE0F)
        $content = preg_replace('/[\x{FE00}-\x{FE0F}]/u', '', $content);

        // Unicode tag characters (U+E0000–U+E007F)
        $content = preg_replace('/[\x{E0000}-\x{E007F}]/u', '', $content);

        // Typographic characters commonly treated as AI tells → ASCII equivalents
        $typo_map = array(
            "\u{2014}" => '-',   // Em dash
            "\u{2013}" => '-',   // En dash
            "\u{2012}" => '-',   // Figure dash
            "\u{2015}" => '-',   // Horizontal bar
            "\u{2212}" => '-',   // Minus sign
            "\u{201C}" => '"',   // Left double quotation mark
            "\u{201D}" => '"',   // Right double quotation mark
            "\u{201E}" => '"',   // Double low-9 quotation mark
            "\u{201F}" => '"',   // Double high-reversed-9 quotation mark
            "\u{2018}" => "'",   // Left single quotation mark
            "\u{2019}" => "'",   // Right single quotation mark / apostrophe
            "\u{201A}" => "'",   // Single low-9 quotation mark
            "\u{201B}" => "'",   // Single high-reversed-9 quotation mark
            "\u{2032}" => "'",   // Prime
            "\u{2033}" => '"',   // Double prime
            "\u{2026}" => '...', // Horizontal ellipsis
            "\u{00A0}" => ' ',   // Non-breaking space
            "\u{202F}" => ' ',   // Narrow no-break space
            "\u{2007}" => ' ',   // Figure space
            "\u{2008}" => ' ',   // Punctuation space
            "\u{2009}" => ' ',   // Thin space
            "\u{200A}" => ' ',   // Hair space
            "\u{2002}" => ' ',   // En space
            "\u{2003}" => ' ',   // Em space
            "\u{2004}" => ' ',   // Three-per-em space
            "\u{2005}" => ' ',   // Four-per-em space
            "\u{2006}" => ' ',   // Six-per-em space
            "\u{3000}" => ' ',   // Ideographic space
            "\u{2028}" => "\n",  // Line separator
            "\u{2029}" => "\n\n",// Paragraph separator
            "\u{2022}" => '-',   // Bullet
            "\u{2023}" => '-',   // Triangular bullet
            "\u{25CF}" => '-',   // Black circle
            "\u{25CB}" => '-',   // White circle
            "\u{00B7}" => '-',   // Middle dot
            "\u{2043}" => '-',   // Hyphen bullet
            "\u{2219}" => '-',   // Bullet operator
            "\u{00AB}" => '"',   // Left guillemet
            "\u{00BB}" => '"',   // Right guillemet
            "\u{2039}" => "'",   // Single left-pointing angle quotation
            "\u{203A}" => "'",   // Single right-pointing angle quotation
        );
        $content = str_replace(array_keys($typo_map), array_values($typo_map), $content);

        // Common Cyrillic/Greek lookalikes mixed into Latin SEO text
        $homoglyphs = array(
            "\u{0430}" => 'a', "\u{0435}" => 'e', "\u{043E}" => 'o', "\u{0440}" => 'p',
            "\u{0441}" => 'c', "\u{0445}" => 'x', "\u{0443}" => 'y', "\u{0456}" => 'i',
            "\u{0410}" => 'A', "\u{0412}" => 'B', "\u{0415}" => 'E', "\u{041A}" => 'K',
            "\u{041C}" => 'M', "\u{041D}" => 'H', "\u{041E}" => 'O', "\u{0420}" => 'P',
            "\u{0421}" => 'C', "\u{0422}" => 'T', "\u{0425}" => 'X',
            "\u{0391}" => 'A', "\u{0392}" => 'B', "\u{0395}" => 'E', "\u{0397}" => 'H',
            "\u{0399}" => 'I', "\u{039A}" => 'K', "\u{039C}" => 'M', "\u{039D}" => 'N',
            "\u{039F}" => 'O', "\u{03A1}" => 'P', "\u{03A4}" => 'T', "\u{03A5}" => 'Y',
            "\u{03A7}" => 'X', "\u{03BF}" => 'o', "\u{03B1}" => 'a',
        );
        $content = str_replace(array_keys($homoglyphs), array_values($homoglyphs), $content);

        // Fullwidth ASCII forms → normal ASCII
        $content = preg_replace_callback(
            '/[\x{FF01}-\x{FF5E}]/u',
            function ($matches) {
                if (function_exists('mb_ord')) {
                    return chr(mb_ord($matches[0], 'UTF-8') - 0xFEE0);
                }
                $bytes = unpack('N', mb_convert_encoding($matches[0], 'UCS-4BE', 'UTF-8'));
                return isset($bytes[1]) ? chr($bytes[1] - 0xFEE0) : $matches[0];
            },
            $content
        );

        // Collapse odd spaces left behind
        $content = preg_replace('/[ \t]{2,}/', ' ', $content);
        $content = preg_replace('/^\s*\n+/', '', $content);
        $content = preg_replace('/\n+\s*$/', '', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return trim($content);
    }

    /**
     * Resolve content length settings for a schedule.
     *
     * @param object|null $schedule
     * @return array{key:string,min:int,max:int,label:string,keyword_min:int,keyword_max:int,max_tokens:int}
     */
    public static function get_content_length_config($schedule = null) {
        $presets = self::get_content_length_presets();
        $key = 'long';

        if (is_object($schedule) && !empty($schedule->content_length)) {
            $key = sanitize_key($schedule->content_length);
        } else {
            $key = sanitize_key(get_option('aiapg_default_content_length', 'long'));
        }

        if (!isset($presets[$key])) {
            $key = 'long';
        }

        return array_merge(array('key' => $key), $presets[$key]);
    }

    /**
     * Available content length presets.
     *
     * @return array
     */
    public static function get_content_length_presets() {
        return array(
            'short' => array(
                'label'        => __('Short (400-600 words)', 'ai-auto-post-image-generator'),
                'min'          => 400,
                'max'          => 600,
                'keyword_min'  => 4,
                'keyword_max'  => 8,
                'max_tokens'   => 2500,
            ),
            'medium' => array(
                'label'        => __('Medium (800-1000 words)', 'ai-auto-post-image-generator'),
                'min'          => 800,
                'max'          => 1000,
                'keyword_min'  => 8,
                'keyword_max'  => 15,
                'max_tokens'   => 4000,
            ),
            'long' => array(
                'label'        => __('Long (1200-1500 words)', 'ai-auto-post-image-generator'),
                'min'          => 1200,
                'max'          => 1500,
                'keyword_min'  => 12,
                'keyword_max'  => 30,
                'max_tokens'   => 6000,
            ),
            'extra_long' => array(
                'label'        => __('Extra Long (1800-2500 words)', 'ai-auto-post-image-generator'),
                'min'          => 1800,
                'max'          => 2500,
                'keyword_min'  => 18,
                'keyword_max'  => 40,
                'max_tokens'   => 8000,
            ),
        );
    }
}
