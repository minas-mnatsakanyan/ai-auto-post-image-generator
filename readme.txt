=== AI Auto Post & Image Generator ===
Contributors: minas1500
Tags: ai, automation, image generation, scheduler, generator
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.5.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automate your WordPress content creation with AI-powered text and image generation. Support for OpenAI, Google Gemini, Pollinations, and Leonardo.AI.

== Description ==

**AI Auto Post & Image Generator** is a powerful WordPress plugin that automates your content creation process using cutting-edge AI technology. Generate engaging blog posts and stunning images automatically, saving you hours of manual work.

= 🚀 Key Features =

* **AI Content Generation**: Create blog posts using OpenAI GPT models or Google Gemini
* **AI Image Creation**: Generate relevant images with DALL-E, Google Gemini, Pollinations, or Leonardo.AI
* **Flexible Scheduling**: Hourly, daily, weekly, monthly, or once at a specific date and time
* **Prompt Publish Times**: For custom prompts, schedule each generated post for a specific WordPress publish date/time
* **Selectable Gemini Models**: Choose current Gemini models, enter a custom model ID, or refresh the live list from Google's API
* **Multiple AI Providers**: Support for OpenAI, Google Gemini, Pollinations, and Leonardo.AI
* **Smart Content Sources**: Generate content based on WordPress categories or custom prompts
* **Custom Prompt Categories**: Assign specific categories to each custom prompt for targeted content organization
* **Advanced SEO Optimization**: Automatic focus keyword extraction and SEO plugin integration (Rank Math, Yoast SEO, AIOSEO)
* **Smart Prompt Detection**: Intelligently handles both detailed instructions and simple topics
* **SEO-Optimized Content**: Generates content with proper keyword density, meta descriptions, and alt text
* **Comprehensive Logging**: Track all automation activities with detailed logs

= 🎯 Perfect For =

* **Bloggers** who want to maintain consistent posting schedules
* **Content Marketers** looking to scale their content production
* **Small Businesses** needing regular website updates
* **Developers** building automated content systems
* **Anyone** who wants to leverage AI for content creation

= 🔧 Supported AI Services =

**Text Generation:**
* OpenAI GPT-3.5 Turbo & GPT-4
* Google Gemini (selectable models + custom model ID)

**Image Generation:**
* OpenAI DALL-E 2 & DALL-E 3
* Google Gemini (3.1 Flash Image, Flash Lite Image, 3 Pro Image, 2.5 Flash Image)
* Pollinations Flux (built-in default key; optional your own key)
* Leonardo.AI (Advanced AI models)

= 🎯 SEO Optimization Features =

**Advanced SEO Integration:**
* **Smart Focus Keyword Extraction**: Automatically extracts meaningful keywords from prompts and topics
* **SEO Plugin Support**: Seamless integration with Rank Math, Yoast SEO, and All in One SEO
* **Optimized Content Structure**: Proper heading hierarchy (H2, H3) and keyword density
* **SEO-Friendly URLs**: Shorter, keyword-focused slugs for better rankings
* **Meta Description Generation**: Compelling descriptions with focus keywords
* **Image Alt Text Optimization**: SEO-optimized alt text for all generated images
* **Intelligent Prompt Detection**: Handles both detailed instructions and simple topics

== External Services ==

This plugin connects to several third-party AI services to generate content and images. These services are essential for the plugin's functionality.

**OpenAI (Text & Image Generation):**
* Used for: Generating blog post content and creating images
* Data sent: Your content prompts and image descriptions
* When: When you run a schedule or manually generate content
* Terms of Service: https://openai.com/policies/terms-of-use
* Privacy Policy: https://openai.com/policies/privacy-policy

**Google Gemini (Text & Image Generation):**
* Used for: Generating blog post content and featured images
* Data sent: Your content prompts and image prompts
* When: When you run a schedule or manually generate content / images with a Gemini model
* Terms of Service: https://ai.google.dev/terms
* Privacy Policy: https://policies.google.com/privacy

**Leonardo.AI (Image Generation):**
* Used for: Creating high-quality images for your posts
* Data sent: Your image prompts and generation parameters
* When: When you run a schedule with image generation enabled
* Terms of Service: https://leonardo.ai/terms-of-service
* Privacy Policy: https://leonardo.ai/privacy-policy

**Pollinations (Image Generation):**
* Used for: Creating images as a free/low-cost alternative
* Data sent: Your image prompts
* When: When you run a schedule with image generation enabled and Pollinations is selected
* Auth: Built-in default key included; optional custom key in Settings (https://enter.pollinations.ai)
* Terms of Service: https://pollinations.ai/terms
* Privacy Policy: https://pollinations.ai/privacy

**Note:** All API keys and sensitive data are stored securely in your WordPress database and are never shared with third parties except the respective AI services when generating content.

= 📊 Dashboard Features =

* **Real-time Statistics**: Monitor posts created, success rates, and API usage
* **Schedule Management**: Create, edit, and manage multiple automation schedules
* **Activity Logs**: Detailed logs of all automation activities
* **API Testing**: Test your API connections before running schedules
* **Import/Export**: Backup and restore your settings easily

= 🛡️ Security & Reliability =

* **Secure API Handling**: All API keys are encrypted and stored securely
* **Error Handling**: Comprehensive error handling and retry mechanisms
* **Rate Limiting**: Built-in rate limiting to prevent API abuse
* **Logging**: Detailed logging for troubleshooting and monitoring
* **WordPress Standards**: Follows WordPress coding standards and best practices

= 💡 Smart Features =

* **Content Quality Control**: All generated posts are saved as drafts for review
* **Image Placement Options**: Add images as featured images, inline content, or both
* **Custom Prompts**: Use your own prompts for more targeted content
* **Category-based Generation**: Generate content based on your existing categories
* **Per-Prompt Category Assignment**: Assign different categories to each custom prompt for precise content organization
* **Flexible Scheduling**: Multiple schedules can run simultaneously

= 🎨 User-Friendly Interface =

* **Modern Dashboard**: Clean, intuitive interface with card-based layout
* **Enhanced Custom Prompts UI**: Beautiful, user-friendly interface for creating and managing custom prompts
* **Responsive Design**: Works perfectly on desktop, tablet, and mobile
* **Real-time Updates**: Live updates without page refreshes
* **Help Documentation**: Comprehensive help section with FAQs

= 🔄 Automation Workflow =

1. **Configure API Keys**: Add your AI service API keys
2. **Create Schedules**: Set up automation schedules with your preferences
3. **Configure Custom Prompts**: Create custom prompts and assign specific categories to each
4. **Generate Content**: AI creates posts and images based on your settings
5. **Review & Publish**: Review generated content and publish when ready
6. **Monitor & Optimize**: Track performance and adjust settings as needed

= 📈 Benefits =

* **Save Time**: Automate repetitive content creation tasks
* **Increase Output**: Generate more content with less effort
* **Maintain Consistency**: Keep your posting schedule consistent
* **Scale Content**: Easily scale your content production
* **Reduce Costs**: Lower content creation costs with AI automation
* **Improve SEO**: Regular content updates improve search rankings
* **Better Organization**: Assign specific categories to each prompt for better content organization

= 🆘 Support & Documentation =

* **Comprehensive Help**: Built-in help section with FAQs and troubleshooting
* **API Documentation**: Links to official API documentation for all providers
* **Email Support**: Direct support for technical issues

= 🔧 Technical Requirements =

* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher
* Valid API keys for AI services
* Internet connection for API calls

= 💰 Pricing Information =

This plugin is **completely free** to use. You only pay for the AI services you use:

* **OpenAI**: GPT-3.5 Turbo (~$0.002 per 1K tokens), DALL-E 2 ($0.02 per image)
* **Google Gemini**: Free tier available, then pay-per-use
* **Pollinations**: Completely free - no API key required
* **Leonardo.AI**: Pay-per-image, varies by model

= 🚀 Getting Started =

1. Install and activate the plugin
2. Configure your API keys for AI services (OpenAI, Google Gemini, Leonardo.AI, etc.)
3. Create your first automation schedule
4. Test the setup with a manual run
5. Monitor logs and adjust settings as needed

**Ready to automate your content creation? Install AI Auto Post & Image Generator today!**

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ai-auto-post-image-generator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your API keys and default settings in the plugin Settings page
4. Create your first automation schedule
5. Start generating content automatically!

== Frequently Asked Questions ==

= How much do the AI services cost? =

Costs vary by provider and model:
* **OpenAI**: GPT-3.5 Turbo: ~$0.002 per 1K tokens, DALL-E 2: $0.02 per image
* **Google Gemini**: Gemini Pro: Free tier available, then pay-per-use
* **Pollinations**: Completely free - no API key required
* **Leonardo.AI**: Pay-per-image, varies by model

Check each provider's pricing page for current rates.

= Can I use multiple AI providers at once? =

Yes! You can configure different schedules to use different providers. For example, use OpenAI for text and Leonardo.AI for images.

= How do custom prompts with categories work? =

When using "By Custom Prompts" mode, you can create custom prompts and assign specific categories to each one. The generated posts will be automatically categorized according to the categories you selected for each prompt. This allows for precise content organization and targeted content generation.

= How do I ensure content quality? =

Use custom prompts to guide the AI, review generated content before publishing, and adjust your prompts based on results. The plugin saves all generated content as drafts by default.

= What happens if an API call fails? =

Failed runs are logged with error details. You can retry failed schedules manually from the Logs page. The plugin includes retry logic for temporary failures.

= Can I edit posts before they publish? =

Yes! Generated posts are saved as drafts by default. You can review, edit, and manually publish them when ready.

= How do I backup my settings? =

Use the Export feature in Settings to download all your configurations as a JSON file. You can import this file to restore settings on another site.

= Is my content original? =

The AI generates original content based on your prompts and categories. However, it's always recommended to review and edit content before publishing to ensure it meets your standards.

= Can I use this for commercial websites? =

Yes, you can use this plugin for commercial websites. Just ensure you comply with the terms of service of the AI providers you use.

= What if I run out of API credits? =

The plugin will log errors when API calls fail due to insufficient credits. You'll need to add more credits to your AI service account to continue generating content.

= Can I schedule posts for specific times? =

Yes, you can set up schedules to run at specific intervals (daily, weekly, custom). The plugin uses WordPress cron to execute schedules automatically.

= Is the plugin compatible with my theme? =

The plugin is designed to work with all WordPress themes. The admin interface is built using WordPress standard components and should integrate seamlessly.

== Screenshots ==

1. Dashboard with statistics and quick actions
2. Schedule Manager showing automation schedules
3. Schedule Manager - Schedule creation popup options
4. Schedule creation popup options 
5. Settings page with API key configuration
6. Settings page default settings
7. Logs page with detailed activity tracking
8. Help page with comprehensive documentation

== Changelog ==

= 1.5.7 =
* **CHANGED**: Default generated post status is Published (Draft remains available in Settings / per schedule)

= 1.5.6 =
* **NEW**: Choose generated post status (Draft, Pending Review, Private, or Published) in Settings and per schedule
* **CHANGED**: Default post status is Published; switch to Draft when you want to review posts first
* **UPDATED**: Compatibility declared for WordPress 7.0

= 1.5.5 =
* **UPDATED**: OpenAI text models now include GPT-5.4 (nano/mini/full), GPT-4.1, GPT-4o, plus legacy GPT-4 Turbo / 3.5
* **IMPROVED**: GPT-5 / o-series requests use max_completion_tokens; OpenAI connection test lists models instead of hardcoding GPT-3.5
* **IMPROVED**: Shared custom model ID works for both OpenAI and Gemini

= 1.5.4 =
* **FIXED**: Auto-migrates schedules still using Gemini 2.5 text models (blocked for new API keys) to gemini-3.5-flash
* **UPDATED**: Gemini model list marks 2.5 models as legacy for new keys

= 1.5.3 =
* **IMPROVED**: Natural titles/content — reduced Rank Math-style keyword stuffing that caused spammy phrases like "Urgent Claim Your…"
* **IMPROVED**: Anti-clickbait title rules and safer focus-keyword extraction
* **UPDATED**: Pollinations uses a built-in default key, with optional custom key override in Settings
* **IMPROVED**: Clearer Gemini image quota / Pollinations auth error messages

= 1.5.2 =
* **NEW**: Google Gemini image models (3.1 Flash Image, Flash Lite Image, 3 Pro Image, 2.5 Flash Image) available when a Gemini API key is configured
* **FIXED**: Gemini image generation no longer stubbed out — uses native generateContent image API

= 1.5.1 =
* **NEW**: Choose generated content length per schedule (Short / Medium / Long / Extra Long)
* **IMPROVED**: Post-generation cleanup removes invisible Unicode fingerprints and normalizes AI telltale punctuation (em dashes, smart quotes, ellipsis, exotic bullets, etc.)

= 1.5.0 =
* **NEW**: One-time schedules that run at a specific date and time
* **NEW**: Per-prompt WordPress publish date/time for custom prompt schedules (creates future/scheduled posts)
* **NEW**: Selectable Gemini models including current Flash/Pro options plus custom model ID
* **NEW**: Refresh Gemini models from Google’s live API list
* **FIXED**: Gemini API connection test no longer uses retired gemini-2.0-flash model
* **IMPROVED**: Monthly interval and every-N-hours/days WP-Cron registration

= 1.4.1 =
* **FIXED**: Include `assets/admin-menu-icon-fix.css` in the plugin package — resolves 404 / `ERR_ABORTED` for the admin menu icon stylesheet when the file was missing from some installs

= 1.4.0 =
* **IMPROVED**: Support links — in-page “Buy me a coffee” banners on Dashboard and Schedule Manager (removed broken floating third-party widget)
* **IMPROVED**: Admin sidebar menu icon alignment for AI Auto Post (dedicated stylesheet on all admin screens)
* **IMPROVED**: Custom prompts UI — decorative pencil hint above the textarea, clear of text and scrollbar
* **IMPROVED**: “Posts per run” always validated between 1 and 10 when saving schedules and defaults
* **FIXED**: PHP 8+ compatibility in admin script tag filter (`str_replace` reference argument)
* **UPDATED**: Help page support section text (donation pointers to Dashboard / Schedule Manager)

= 1.3.0 =
* **UPDATED**: Pollinations image API – now uses new endpoint (gen.pollinations.ai) with Flux model
* **UPDATED**: Pollinations requests include `?model=flux` for image generation
* **IMPROVED**: Title generation – SEO-optimized titles without forcing numbers or power words when not needed
* **IMPROVED**: Titles are more natural and context-appropriate while staying SEO-friendly

= 1.2.0 =
* **NEW**: Smart focus keyword extraction for better SEO optimization
* **NEW**: Advanced SEO plugin integration (Rank Math, Yoast SEO, AIOSEO)
* **NEW**: Intelligent prompt detection - handles both detailed instructions and simple topics
* **NEW**: SEO-optimized content generation with proper keyword density
* **NEW**: Enhanced meta description generation with focus keywords
* **NEW**: Improved image alt text generation for better SEO
* **IMPROVED**: Category assignment for custom prompts - respects individual prompt categories
* **IMPROVED**: URL slug generation - shorter, SEO-friendly URLs
* **IMPROVED**: Content structure - proper heading hierarchy (H2, H3 only)
* **IMPROVED**: Code cleanup - removed duplications and unused functions
* **FIXED**: Featured image alt text now shows proper SEO text instead of filename
* **FIXED**: Custom prompt posts no longer default to "Uncategorized"

= 1.1.1 =
* **IMPROVED**: Code quality and WordPress coding standards compliance
* **IMPROVED**: Database query security with proper prepared statements
* **IMPROVED**: Migration system compatibility with WordPress 5.0+
* **FIXED**: WordPress coding standards warnings
* **FIXED**: Database query caching warnings
* **FIXED**: Identifier placeholder compatibility issues
* **UPDATED**: Version tracking and migration system

= 1.1.0 =
* **NEW**: Custom prompts with category assignment - assign specific categories to each custom prompt
* **NEW**: Enhanced custom prompts UI with beautiful, user-friendly interface
* **NEW**: Per-prompt category assignment for precise content organization
* **IMPROVED**: Better visual design for custom prompts section
* **IMPROVED**: Enhanced form validation and error handling
* **IMPROVED**: Better user experience with cleaner interface
* **FIXED**: Category assignment now works correctly for custom prompts
* **FIXED**: Improved backward compatibility with existing schedules

= 1.0.0 =
* Initial release
* AI content generation with OpenAI and Google Gemini
* AI image generation with DALL-E, Pollinations, and Leonardo.AI
* Flexible scheduling system
* Comprehensive logging and monitoring
* Modern, responsive admin interface
* Import/Export functionality
* Help documentation and FAQs

== Upgrade Notice ==

= 1.5.7 =
Default post status is now Published. You can still choose Draft in Settings or per schedule.

= 1.5.6 =
Adds selectable post status (Published by default, or Draft for review before going live). Declares compatibility with WordPress 7.0.

= 1.5.5 =
Adds current OpenAI GPT-5.4 / GPT-4.1 / GPT-4o text models alongside Gemini, with better GPT-5 API compatibility.

= 1.5.3 =
Improves natural writing quality. Pollinations works with a built-in default key; you can optionally add your own key in Settings.

= 1.5.2 =
Adds Google Gemini as an image provider (Flash / Pro image models) when your Gemini API key is set.

= 1.5.1 =
Adds selectable content length and cleans AI Unicode fingerprints / typographic tells from generated posts.

= 1.5.0 =
Adds specific date/time scheduling (one-time runs + per-prompt WordPress publish times) and selectable/custom Gemini models so generation keeps working as Google retires older model IDs.

= 1.4.1 =
Patch release: ensures the admin menu icon stylesheet file is included — fixes console/network errors if your previous install was missing it.

= 1.4.0 =
UI and reliability update: cleaner support banners, admin menu icon fix, posts-per-run validation, custom prompt styling, and PHP 8 admin fix.

= 1.3.0 =
Pollinations image API updated to new endpoint with Flux model. Improved title generation for more natural, SEO-friendly headlines.

= 1.2.0 =
Major update with advanced SEO optimization features! Now includes smart focus keyword extraction, SEO plugin integration (Rank Math, Yoast SEO, AIOSEO), intelligent prompt detection, and improved content generation. Enhanced category assignment and better image alt text. Recommended for all users.

= 1.1.1 =
Minor update with code quality improvements and WordPress coding standards compliance. Enhanced database security and migration system compatibility.

= 1.1.0 =
Major update with custom prompts category assignment! Now you can assign specific categories to each custom prompt for precise content organization. Enhanced UI with beautiful custom prompts interface and improved user experience.

= 1.0.1 =
Initial release of AI Auto Post & Image Generator. This plugin provides comprehensive AI-powered content automation for WordPress websites.

== Developer Information ==

= Hooks and Filters =

The plugin provides various hooks and filters for developers:

**Actions:**
* `aiapg_before_post_generation` - Fired before generating a post
* `aiapg_after_post_generation` - Fired after generating a post
* `aiapg_before_image_generation` - Fired before generating an image
* `aiapg_after_image_generation` - Fired after generating an image
* `aiapg_schedule_completed` - Fired when a schedule completes

**Filters:**
* `aiapg_post_prompt` - Modify the prompt used for post generation
* `aiapg_image_prompt` - Modify the prompt used for image generation
* `aiapg_generated_content` - Modify generated content before saving
* `aiapg_schedule_settings` - Modify schedule settings before execution

= API Reference =

The plugin includes a comprehensive API for developers:

* `AIAPG_Post_Generator` - Class for generating AI content
* `AIAPG_Image_Generator` - Class for generating AI images
* `AIAPG_Schedule_Manager` - Class for managing schedules
* `AIAPG_Scheduler` - Class for executing schedules

= Contributing =

We welcome contributions! Please visit our GitHub repository to:
* Report bugs
* Request features
* Submit pull requests
* Contribute to documentation

GitHub Repository: [https://github.com/minas-mnatsakanyan/ai-auto-post-image-generator](https://github.com/minas-mnatsakanyan/ai-auto-post-image-generator)

= Support =

For developer support:
* GitHub Issues: [https://github.com/minas-mnatsakanyan/ai-auto-post-image-generator/issues](https://github.com/minas-mnatsakanyan/ai-auto-post-image-generator/issues)
* Email: contact@wallshoot.com

== Credits ==

This plugin was developed with the following technologies and services:

* **WordPress** - The world's most popular CMS
* **OpenAI** - Advanced AI models for text and image generation
* **Google Gemini** - Powerful language model for content creation
* **Pollinations** - Free AI image generation service
* **Leonardo.AI** - Advanced AI image generation models

Special thanks to the WordPress community and all the AI service providers for making this plugin possible.

== License ==

This plugin is licensed under the GPL v2 or later.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

== Privacy Policy ==

This plugin:

* Stores API keys securely in the WordPress database
* Logs automation activities for monitoring and troubleshooting
* Does not collect or transmit personal data
* Respects WordPress privacy settings
* Complies with GDPR requirements

For more information about data handling, please review our complete privacy policy at [Privacy Policy URL].
