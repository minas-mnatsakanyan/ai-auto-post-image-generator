/**
 * AI Auto Post & Image Generator - Admin JavaScript
 *
 * @package AI_Auto_Post_Generator
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Main admin object
    const AIAPG = {
        // Initialize
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.initDatePickers();
            this.initSelect2();
            this.checkModelAvailability();
            this.toggleIntervalOptions();
            this.toggleCustomTextModel();
        },

        // Bind event handlers
        bindEvents: function() {
            // Schedule form handling
            $(document).on('click', '.add-schedule-btn, #aiapg-add-schedule, #aiapg-add-first-schedule', this.showScheduleModal);
            $(document).on('click', '.edit-schedule-btn, .edit-schedule', this.editSchedule);
            $(document).on('click', '.delete-schedule-btn, .delete-schedule', this.deleteSchedule);
            $(document).on('click', '.run-schedule-btn, .run-schedule', this.runSchedule);
            $(document).on('click', '.clear-schedule-lock', this.clearScheduleLock);
            $(document).on('click', '.toggle-schedule-btn', this.toggleSchedule);
            
            // Modal handling
            $(document).on('click', '.aiapg-modal-close, .aiapg-modal', this.closeModal);
            $(document).on('click', '#cancel-schedule', this.closeModal);
            $(document).on('click', '.aiapg-modal-content', function(e) {
                e.stopPropagation();
            });
            
            // Form submissions
            $(document).on('click', '#save-schedule', this.saveSchedule);
            $(document).on('submit', '#aiapg-api-keys-form', this.saveApiKeys);
            $(document).on('submit', '#aiapg-defaults-form', this.saveDefaults);
            $(document).on('submit', '#aiapg-advanced-form', this.saveAdvanced);
            
            // API key testing
            $(document).on('click', '.test-api-key', this.testApiKey);
            
            // Log management
            $(document).on('click', '.view-log-details', this.viewLogDetails);
            $(document).on('click', '.retry-schedule', this.retrySchedule);
            
            // Import/Export
            $(document).on('submit', '#aiapg-export-form', this.exportSettings);
            $(document).on('submit', '#aiapg-import-form', this.importSettings);
            $(document).on('submit', '#aiapg-reset-form', this.resetSettings);
            

            
            // Dynamic form handling
            $(document).on('change', 'input[name="content_source"]', this.toggleContentType);
            $(document).on('change', 'input[name="enable_images"]', this.toggleImageOptions);
            $(document).on('change', 'input[name="create_sample_schedule"]', this.toggleSampleSchedule);
            $(document).on('change', '#image_placement', this.toggleImagesPerPost);
            $(document).on('change', '#interval_type', this.toggleIntervalOptions);
            $(document).on('change', '#text_model, #default_text_model', this.toggleCustomTextModel);
            $(document).on('click', '#aiapg-refresh-gemini-models, #aiapg-refresh-gemini-models-settings', this.refreshGeminiModels);
            
            // Check model availability when image options change
            $(document).on('change', 'input[name="enable_images"], #image_placement', this.checkModelAvailability);
                
            // Prompt handling
            $(document).on('click', '#add-prompt', this.addPrompt);
            $(document).on('click', '.remove-prompt', this.removePrompt);
            
            // Tab functionality
            $(document).on('click', '.nav-tab', this.switchTab);
            
            // FAQ accordion
            $(document).on('click', '.faq-question', this.toggleFaq);
            

        
        // FAQ accordion functionality
        $(document).on('click', '.faq-question', function() {
            const answer = $(this).next('.faq-answer');
            const isOpen = answer.is(':visible');
            
            // If clicking the currently open answer, just close it
            if (isOpen) {
                answer.slideUp(300);
                $(this).removeClass('active');
            } else {
                // If clicking a closed answer, close others first, then open this one
                $('.faq-answer').not(answer).slideUp(300);
                $('.faq-question').not(this).removeClass('active');
                
                // Open the clicked answer after a short delay to ensure others are closed
                setTimeout(function() {
                    answer.slideDown(300);
                    $(this).addClass('active');
                }.bind(this), 50);
            }
        });
        
        // Smooth scrolling for anchor links
        $(document).on('click', 'a[href^="#"]', function(e) {
            e.preventDefault();
            const target = $($(this).attr('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 50
                }, 500);
            }
        });
            
            // Check model availability on modal open
            $(document).on('shown.bs.modal', '.aiapg-modal', this.checkModelAvailability);
        },

        // Initialize tooltips
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const tooltipText = $element.data('tooltip');
                
                $element.tooltip({
                    content: tooltipText,
                    position: {
                        my: 'left top+5',
                        at: 'left bottom'
                    },
                    show: {
                        duration: 200
                    },
                    hide: {
                        duration: 200
                    }
                });
            });
        },

        // Initialize date pickers
        initDatePickers: function() {
            if (typeof $.fn.datepicker !== 'undefined') {
                $('.aiapg-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    yearRange: '-10:+10'
                });
            }
        },

        // Initialize Select2 for enhanced dropdowns
        initSelect2: function() {
            if (typeof $.fn.select2 !== 'undefined') {
                $('.aiapg-select2').select2({
                    width: '100%',
                    placeholder: 'Select options...',
                    allowClear: true
                });
            }
        },

        // Show schedule modal
        showScheduleModal: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            
            if (scheduleId) {
                // Edit existing schedule
                AIAPG.loadSchedule(scheduleId);
            } else {
                // Add new schedule
                AIAPG.resetScheduleForm();
            }
            
            $('#aiapg-schedule-modal').addClass('active');
        },

        // Load schedule data for editing
        loadSchedule: function(scheduleId) {
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_get_schedule',
                    schedule_id: scheduleId,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.populateScheduleForm(response.data);
                    } else {
                        AIAPG.showError('Error loading schedule: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error loading schedule. Please try again.');
                }
            });
        },

        // Populate schedule form with data
        populateScheduleForm: function(data) {
            const form = $('#aiapg-schedule-form');
            
            // Basic fields
            form.find('input[name="schedule_id"]').val(data.id);
            form.find('input[name="name"]').val(data.name);
            form.find('textarea[name="description"]').val(data.description);
            form.find('input[name="posts_per_run"]').val(data.posts_per_run);
            form.find('select[name="content_length"]').val(data.content_length || 'long');
            form.find('select[name="post_status"]').val(data.post_status || 'publish');
            
            // Determine content source and populate accordingly
            let hasCategories = false;
            let hasPrompts = false;
            let categories = [];
            let customPrompts = [];
            
            // Check if data is already arrays (from AJAX response) or strings (that need parsing)
            if (Array.isArray(data.categories)) {
                categories = data.categories;
            } else if (typeof data.categories === 'string') {
                try {
                    categories = data.categories ? JSON.parse(data.categories) : [];
                } catch (e) {
                    if (data.categories.startsWith('a:')) {
                        categories = AIAPG.parsePHPSerialized(data.categories) || [];
                    } else {
                        categories = [];
                    }
                }
            } else {
                categories = [];
            }
            
            if (Array.isArray(data.custom_prompts)) {
                customPrompts = data.custom_prompts;
            } else if (typeof data.custom_prompts === 'string') {
                try {
                    customPrompts = data.custom_prompts ? JSON.parse(data.custom_prompts) : [];
                } catch (e) {
                    if (data.custom_prompts.startsWith('a:')) {
                        customPrompts = AIAPG.parsePHPSerialized(data.custom_prompts) || [];
                    } else {
                        customPrompts = [];
                    }
                }
            } else {
                customPrompts = [];
            }
            
            // Filter out empty values for proper detection
            hasCategories = categories && categories.length > 0 && categories.some(cat => cat !== null && cat !== undefined && cat !== '');
            hasPrompts = customPrompts && customPrompts.length > 0 && customPrompts.some(prompt => {
                // Handle both old format (string) and new format (object with text)
                if (typeof prompt === 'string') {
                    return prompt.trim() !== '';
                } else if (typeof prompt === 'object' && prompt.text) {
                    return prompt.text.trim() !== '';
                }
                return false;
            });
            
            // Set content source - prioritize the one that actually has content
            if (hasPrompts && hasCategories) {
                // If both have content, check which one has more meaningful data
                // Default to prompts if both exist as user likely set prompts more recently
                form.find('input[name="content_source"][value="prompts"]').prop('checked', true);
            } else if (hasPrompts) {
                form.find('input[name="content_source"][value="prompts"]').prop('checked', true);
            } else if (hasCategories) {
                form.find('input[name="content_source"][value="categories"]').prop('checked', true);
            } else {
                // Default to categories if neither is set
                form.find('input[name="content_source"][value="categories"]').prop('checked', true);
            }
            
            AIAPG.toggleContentType();
            
            // Reset prompts container first
            $('#prompts-container').html(`
                <div class="prompt-input">
                    <div class="prompt-content">
                        <textarea name="custom_prompts[0][text]" placeholder="Enter a prompt for content generation..."></textarea>
                        <div class="prompt-categories">
                            <label>Assign posts to categories:</label>
                            <select name="custom_prompts[0][categories][]" multiple class="prompt-category-select">
                                ${window.aiapgCategoryOptions || ''}
                            </select>
                        </div>
                        <div class="prompt-publish-at">
                            <label>Publish at specific date & time (optional):</label>
                            <input type="datetime-local" name="custom_prompts[0][publish_at]" class="prompt-publish-at-input">
                            <p class="description">Creates a WordPress scheduled post at this date/time. Leave empty to use the default post status.</p>
                        </div>
                    </div>
                    <button type="button" class="remove-prompt" style="display: none;">&times;</button>
                </div>
            `);
            
            // Populate categories or prompts
            if (hasCategories && Array.isArray(categories)) {
                // Convert categories to strings to match dropdown option values
                const categoryValues = categories.map(cat => String(cat));
                
                // Clear any existing selections first
                form.find('select[name="categories[]"] option').prop('selected', false);
                
                // Set the values by finding and selecting the options
                categoryValues.forEach(function(categoryValue) {
                    const option = form.find('select[name="categories[]"] option[value="' + categoryValue + '"]');
                    option.prop('selected', true);
                });
            } else if (hasPrompts && Array.isArray(customPrompts)) {
                // Populate the prompts
                customPrompts.forEach(function(promptData, index) {
                    let promptText = '';
                    let promptCategories = [];
                    let publishAt = '';
                    
                    // Handle both old format (string) and new format (object with text and categories)
                    if (typeof promptData === 'string') {
                        promptText = promptData;
                    } else if (typeof promptData === 'object' && promptData.text) {
                        promptText = promptData.text;
                        promptCategories = promptData.categories || [];
                        publishAt = promptData.publish_at || '';
                    }
                    
                    if (index === 0) {
                        // First prompt - use existing field
                        $('#prompts-container .prompt-input:first textarea').val(promptText);
                        $('#prompts-container .prompt-input:first .prompt-publish-at-input').val(AIAPG.toDatetimeLocalValue(publishAt));
                        
                        // Set categories for first prompt
                        if (promptCategories.length > 0) {
                            const categoryValues = promptCategories.map(cat => String(cat));
                            categoryValues.forEach(function(categoryValue) {
                                const option = $('#prompts-container .prompt-input:first select option[value="' + categoryValue + '"]');
                                option.prop('selected', true);
                            });
                        }
                        
                        // Show remove button if there are multiple prompts
                        if (customPrompts.length > 1) {
                            $('#prompts-container .prompt-input:first .remove-prompt').show();
                        }
                    } else {
                        // Add additional prompts
                        AIAPG.addPromptField(promptText, promptCategories, publishAt);
                    }
                });
            }
            
            // Models
            AIAPG.setTextModelSelection(data.text_model);
            form.find('select[name="image_model"]').val(data.image_model);
            form.find('select[name="fallback_image_model"]').val(data.fallback_image_model);
            
            // Image settings
            form.find('input[name="enable_images"]').prop('checked', data.enable_images == 1);
            AIAPG.toggleImageOptions();
            form.find('select[name="image_placement"]').val(data.image_placement);
            AIAPG.toggleImagesPerPost(); // Apply the images per post logic
            form.find('select[name="image_size"]').val(data.image_size);
            form.find('input[name="images_per_post"]').val(data.images_per_post);
            
            // Schedule settings
            form.find('select[name="interval_type"]').val(data.interval_type);
            form.find('input[name="interval_value"]').val(data.interval_value);
            form.find('input[name="custom_cron"]').val(data.custom_cron);
            form.find('input[name="scheduled_at"]').val(AIAPG.toDatetimeLocalValue(data.scheduled_at || ''));
            form.find('input[name="is_active"]').prop('checked', data.is_active == 1);
            AIAPG.toggleIntervalOptions();
            AIAPG.toggleCustomTextModel();
            
            // Update form title
            $('#modal-title').text('Edit Schedule');
        },

        // Reset schedule form
        resetScheduleForm: function() {
            const form = $('#aiapg-schedule-form')[0];
            form.reset();
            form.querySelector('input[name="schedule_id"]').value = '';
            
            // Reset prompts container to original state
            $('#prompts-container').html(`
                <div class="prompt-input">
                    <div class="prompt-content">
                        <textarea name="custom_prompts[0][text]" placeholder="Enter a prompt for content generation..."></textarea>
                        <div class="prompt-categories">
                            <label>Assign posts to categories:</label>
                            <select name="custom_prompts[0][categories][]" multiple class="prompt-category-select">
                                ${window.aiapgCategoryOptions || ''}
                            </select>
                        </div>
                        <div class="prompt-publish-at">
                            <label>Publish at specific date & time (optional):</label>
                            <input type="datetime-local" name="custom_prompts[0][publish_at]" class="prompt-publish-at-input">
                            <p class="description">Creates a WordPress scheduled post at this date/time. Leave empty to use the default post status.</p>
                        </div>
                    </div>
                    <button type="button" class="remove-prompt" style="display: none;">&times;</button>
                </div>
            `);
            
            // Reset to defaults
            form.querySelector('input[name="content_source"][value="categories"]').checked = true;
            form.querySelector('input[name="enable_images"]').checked = true;
            form.querySelector('select[name="interval_type"]').value = 'daily';
            form.querySelector('input[name="interval_value"]').value = '1';
            if (form.querySelector('input[name="scheduled_at"]')) {
                form.querySelector('input[name="scheduled_at"]').value = '';
            }
            if (form.querySelector('input[name="custom_text_model"]')) {
                form.querySelector('input[name="custom_text_model"]').value = '';
            }
            
            AIAPG.toggleContentType();
            AIAPG.toggleImageOptions();
            AIAPG.toggleImagesPerPost();
            AIAPG.toggleIntervalOptions();
            AIAPG.toggleCustomTextModel();
            
            // Update form title
            $('#modal-title').text('Add New Schedule');
        },

        // Edit schedule
        editSchedule: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            AIAPG.loadSchedule(scheduleId);
            $('#aiapg-schedule-modal').addClass('active');
        },

        // Delete schedule
        deleteSchedule: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            const scheduleName = $(this).data('schedule-name');
            
            if (confirm('Are you sure you want to delete the schedule "' + scheduleName + '"? This action cannot be undone.')) {
                $.ajax({
                    url: aiapg_ajaxurl.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'aiapg_delete_schedule',
                        schedule_id: scheduleId,
                        nonce: aiapg_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            AIAPG.showSuccess('Schedule deleted successfully!');
                            location.reload();
                        } else {
                            AIAPG.showError('Error deleting schedule: ' + response.data);
                        }
                    },
                    error: function() {
                        AIAPG.showError('Error deleting schedule. Please try again.');
                    }
                });
            }
        },

        // Run schedule
        runSchedule: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            const button = $(this);
            const originalText = button.text();
            
            button.prop('disabled', true).text('Running...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_run_schedule',
                    schedule_id: scheduleId,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Schedule executed successfully! ' + response.data.posts_created + ' posts created.');
                        location.reload();
                    } else {
                        AIAPG.showError('Error running schedule: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error running schedule. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        // Clear a stale schedule lock after a timed-out/aborted request
        clearScheduleLock: function(e) {
            e.preventDefault();

            const scheduleId = $(this).data('schedule-id');
            const button = $(this);
            const originalText = button.text();

            if (!window.confirm('Clear this schedule lock? Use this only if the previous run has already timed out or stopped.')) {
                return;
            }

            button.prop('disabled', true).text('Clearing...');

            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_clear_schedule_lock',
                    schedule_id: scheduleId,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const message = response.data && response.data.message
                            ? response.data.message
                            : 'Schedule lock cleared.';
                        AIAPG.showSuccess(message);
                    } else {
                        AIAPG.showError('Unable to clear schedule lock: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Unable to clear schedule lock. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        // Toggle schedule status
        toggleSchedule: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            const isActive = $(this).data('is-active');
            const button = $(this);
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_toggle_schedule',
                    schedule_id: scheduleId,
                    is_active: isActive,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Schedule status updated successfully!');
                        location.reload();
                    } else {
                        AIAPG.showError('Error updating schedule status: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error updating schedule status. Please try again.');
                }
            });
        },

        // Save schedule
        saveSchedule: function(e) {
            e.preventDefault();
            const form = $('#aiapg-schedule-form');
            const submitButton = $(this);
            const originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Saving...');
            
            // Get form data manually to ensure multiple select values are properly captured
            const formData = new FormData(form[0]);
            const serializedData = new URLSearchParams(formData).toString();
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_save_schedule',
                    data: serializedData,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Schedule saved successfully!');
                        $('#aiapg-schedule-modal').removeClass('active');
                        location.reload();
                    } else {
                        AIAPG.showError('Error saving schedule: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error saving schedule. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        // Close modal
        closeModal: function(e) {
            if (e.target === this || $(e.target).hasClass('aiapg-modal-close')) {
                $('.aiapg-modal').removeClass('active');
            }
        },

        // Save API keys
        saveApiKeys: function(e) {
            e.preventDefault();
            AIAPG.saveSettings('api_keys', $(this));
        },

        // Save default settings
        saveDefaults: function(e) {
            e.preventDefault();
            AIAPG.saveSettings('defaults', $(this));
        },

        // Save advanced settings
        saveAdvanced: function(e) {
            e.preventDefault();
            AIAPG.saveSettings('advanced', $(this));
        },

        // Generic settings save function
        saveSettings: function(type, form) {
            const submitButton = form.find('button[type="submit"]');
            const originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_save_settings',
                    data: form.serialize(),
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Settings saved successfully!');
                    } else {
                        AIAPG.showError('Error saving settings: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error saving settings. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        // Test API key
        testApiKey: function(e) {
            e.preventDefault();
            const button = $(this);
            const provider = button.data('provider');
            const key = button.closest('td').find('input[type="password"]').val();
            
            if (!key) {
                AIAPG.showError('Please enter an API key first.');
                return;
            }
            
            button.prop('disabled', true).text('Testing...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_test_api_key',
                    provider: provider,
                    api_key: key,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Connection successful!');
                    } else {
                        AIAPG.showError('Connection failed: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Connection failed. Please check your API key and try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text('Test Connection');
                }
            });
        },

        // Export settings
        exportSettings: function(e) {
            e.preventDefault();
            const form = $(this);
            const submitButton = form.find('button[type="submit"]');
            const originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Exporting...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_export_settings',
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        const blob = new Blob([JSON.stringify(response.data, null, 2)], {type: 'application/json'});
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'aiapg-settings-' + new Date().toISOString().split('T')[0] + '.json';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        
                        AIAPG.showSuccess('Settings exported successfully!');
                    } else {
                        AIAPG.showError('Error exporting settings: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error exporting settings. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        // Import settings
        importSettings: function(e) {
            e.preventDefault();
            const form = $(this);
            const submitButton = form.find('button[type="submit"]');
            const originalText = submitButton.text();
            
            submitButton.prop('disabled', true).text('Importing...');
            
            const formData = new FormData(this);
            formData.append('action', 'aiapg_import_settings');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Settings imported successfully!');
                        location.reload();
                    } else {
                        AIAPG.showError('Error importing settings: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error importing settings. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        // Reset settings
        resetSettings: function(e) {
            e.preventDefault();
            const form = $(this);
            const submitButton = form.find('button[type="submit"]');
            const originalText = submitButton.text();
            
            if (!confirm('Are you sure you want to reset all settings? This action cannot be undone.')) {
                return;
            }
            
            submitButton.prop('disabled', true).text('Resetting...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_reset_settings',
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Settings reset successfully!');
                        location.reload();
                    } else {
                        AIAPG.showError('Error resetting settings: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error resetting settings. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalText);
                }
            });
        },

        // View log details
        viewLogDetails: function(e) {
            e.preventDefault();
            const logId = $(this).data('log-id');
            const modal = $('#log-details-modal');
            
            // Show loading
            $('#log-details-content').html('<div class="aiapg-loading">Loading...</div>');
            modal.addClass('active');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_get_log_details',
                    log_id: logId,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#log-details-content').html(response.data);
                    } else {
                        $('#log-details-content').html('<div class="aiapg-error">Error loading log details.</div>');
                    }
                },
                error: function() {
                    $('#log-details-content').html('<div class="aiapg-error">Error loading log details.</div>');
                }
            });
        },

        // Retry schedule
        retrySchedule: function(e) {
            e.preventDefault();
            const scheduleId = $(this).data('schedule-id');
            const button = $(this);
            
            if (!confirm('Are you sure you want to retry this schedule?')) {
                return;
            }
            
            button.prop('disabled', true).text('Retrying...');
            
            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_retry_schedule',
                    schedule_id: scheduleId,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        AIAPG.showSuccess('Schedule retry initiated successfully!');
                        location.reload();
                    } else {
                        AIAPG.showError('Error retrying schedule: ' + response.data);
                    }
                },
                error: function() {
                    AIAPG.showError('Error retrying schedule. Please try again.');
                },
                complete: function() {
                    button.prop('disabled', false).text('Retry');
                }
            });
        },



        // Dynamic form handling
        toggleContentType: function() {
            const contentType = $('input[name="content_source"]:checked').val();
            
            if (contentType === 'categories') {
                $('#categories-section').show();
                $('#prompts-section').hide();
            } else {
                $('#categories-section').hide();
                $('#prompts-section').show();
            }
        },

        toggleIntervalOptions: function() {
            const intervalType = $('#interval_type').val();
            const $intervalValue = $('#interval-value-group');
            const $customCron = $('#custom-cron-group');
            const $scheduledAt = $('#scheduled-at-group');

            $intervalValue.hide();
            $customCron.hide();
            $scheduledAt.hide();

            if (intervalType === 'once') {
                $scheduledAt.show();
            } else if (intervalType === 'custom') {
                $customCron.show();
            } else {
                $intervalValue.show();
            }
        },

        toggleCustomTextModel: function() {
            const $scheduleSelect = $('#text_model');
            const $defaultSelect = $('#default_text_model');

            if ($scheduleSelect.length) {
                const isCustom = $scheduleSelect.val() === '__custom__';
                $('#custom-text-model-group').toggle(isCustom);
            }

            if ($defaultSelect.length) {
                const isCustomDefault = $defaultSelect.val() === '__custom__';
                $('#custom-default-text-model-group').toggle(isCustomDefault);
            }
        },

        setTextModelSelection: function(modelId) {
            const $select = $('#text_model');
            if (!$select.length) {
                return;
            }

            const value = modelId || '';
            if (value && $select.find('option[value="' + value.replace(/"/g, '\\"') + '"]').length) {
                $select.val(value);
                $('#custom_text_model').val('');
            } else if (value) {
                if ($select.find('option[value="__custom__"]').length === 0) {
                    $select.append('<option value="__custom__">Custom Gemini model ID…</option>');
                }
                $select.val('__custom__');
                $('#custom_text_model').val(value);
            }

            AIAPG.toggleCustomTextModel();
        },

        toDatetimeLocalValue: function(value) {
            if (!value) {
                return '';
            }
            // Accept "YYYY-MM-DD HH:MM:SS" or ISO strings.
            const normalized = String(value).trim().replace(' ', 'T');
            return normalized.length >= 16 ? normalized.substring(0, 16) : '';
        },

        refreshGeminiModels: function(e) {
            e.preventDefault();
            const $button = $(this);
            const originalText = $button.text();
            $button.prop('disabled', true).text('Refreshing…');

            $.ajax({
                url: aiapg_ajaxurl.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aiapg_fetch_gemini_models',
                    force: 1,
                    nonce: aiapg_ajax.nonce
                },
                success: function(response) {
                    if (response.success && response.data && response.data.models) {
                        AIAPG.mergeGeminiModelsIntoSelects(response.data.models);
                        AIAPG.showSuccess(response.data.message || 'Gemini models updated.');
                    } else {
                        AIAPG.showError((response.data && response.data.message) ? response.data.message : (response.data || 'Could not refresh Gemini models.'));
                    }
                },
                error: function() {
                    AIAPG.showError('Could not refresh Gemini models. Please try again.');
                },
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        mergeGeminiModelsIntoSelects: function(models) {
            const selectors = ['#text_model', '#default_text_model'];
            selectors.forEach(function(selector) {
                const $select = $(selector);
                if (!$select.length) {
                    return;
                }

                const current = $select.val();
                models.forEach(function(model) {
                    if (!model.value || $select.find('option[value="' + model.value + '"]').length) {
                        return;
                    }
                    const $customOption = $select.find('option[value="__custom__"]');
                    const optionHtml = '<option value="' + model.value + '">' + $('<div>').text(model.label || model.value).html() + '</option>';
                    if ($customOption.length) {
                        $customOption.before(optionHtml);
                    } else {
                        $select.append(optionHtml);
                    }
                });
                if (current) {
                    $select.val(current);
                }
            });
        },

        toggleImageOptions: function() {
            const enableImages = $('input[name="enable_images"]').is(':checked');
            if (enableImages) {
                $('.image-settings').show();
            } else {
                $('.image-settings').hide();
            }
        },

        toggleImagesPerPost: function() {
            const imagePlacement = $('#image_placement').val();
            const imagesPerPostGroup = $('.form-group:has(#images_per_post)');
            
            // Hide "Images per Post" field if "Featured Image Only" is selected
            if (imagePlacement === 'featured') {
                imagesPerPostGroup.slideUp(300);
                // Set default value to 1 for featured image only
                $('#images_per_post').val(1);
            } else {
                imagesPerPostGroup.slideDown(300);
            }
        },

        toggleSampleSchedule: function() {
            const createSample = $('input[name="create_sample_schedule"]').is(':checked');
            $('.sample-schedule-details').toggle(createSample);
        },

        // Add prompt field
        addPrompt: function(e) {
            e.preventDefault();
            AIAPG.addPromptField();
        },

        // Remove prompt field
        removePrompt: function(e) {
            e.preventDefault();
            $(this).closest('.prompt-input').remove();
            
            // Reindex remaining prompt fields
            AIAPG.reindexPromptFields();
        },

        // Reindex prompt fields after removal
        reindexPromptFields: function() {
            $('#prompts-container .prompt-input').each(function(index) {
                $(this).find('textarea').attr('name', `custom_prompts[${index}][text]`);
                $(this).find('select').attr('name', `custom_prompts[${index}][categories][]`);
                $(this).find('.prompt-publish-at-input').attr('name', `custom_prompts[${index}][publish_at]`);
            });
        },

        // Add prompt field with optional value, categories, and publish_at
        addPromptField: function(value = '', categories = [], publishAt = '') {
            const promptIndex = $('#prompts-container .prompt-input').length;
            const promptHtml = `
                <div class="prompt-input">
                    <div class="prompt-content">
                        <textarea name="custom_prompts[${promptIndex}][text]" placeholder="Enter a prompt for content generation...">${value}</textarea>
                        <div class="prompt-categories">
                            <label>Assign posts to categories:</label>
                            <select name="custom_prompts[${promptIndex}][categories][]" multiple class="prompt-category-select">
                                ${window.aiapgCategoryOptions || ''}
                            </select>
                        </div>
                        <div class="prompt-publish-at">
                            <label>Publish at specific date & time (optional):</label>
                            <input type="datetime-local" name="custom_prompts[${promptIndex}][publish_at]" class="prompt-publish-at-input" value="${AIAPG.toDatetimeLocalValue(publishAt)}">
                            <p class="description">Creates a WordPress scheduled post at this date/time. Leave empty to use the default post status.</p>
                        </div>
                    </div>
                    <button type="button" class="remove-prompt">&times;</button>
                </div>
            `;
            $('#prompts-container').append(promptHtml);
            
            // Set selected categories if provided
            if (categories.length > 0) {
                const categoryValues = categories.map(cat => String(cat));
                const $select = $('#prompts-container .prompt-input:last select');
                categoryValues.forEach(function(categoryValue) {
                    const option = $select.find('option[value="' + categoryValue + '"]');
                    option.prop('selected', true);
                });
            }
        },

        // Tab functionality
        switchTab: function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Hide all tab content
            $('.tab-content').removeClass('active');
            
            // Show the target tab content
            const targetId = $(this).attr('href');
            $(targetId).addClass('active');
        },

        // FAQ accordion
        toggleFaq: function() {
            const answer = $(this).next('.faq-answer');
            const isOpen = answer.is(':visible');
            
            // Close all other answers
            $('.faq-answer').slideUp();
            $('.faq-question').removeClass('active');
            
            // Toggle current answer
            if (!isOpen) {
                answer.slideDown();
                $(this).addClass('active');
            }
        },

        // Utility functions
        showSuccess: function(message) {
            this.showNotification(message, 'success');
        },

        showError: function(message) {
            this.showNotification(message, 'error');
        },

        showWarning: function(message) {
            this.showNotification(message, 'warning');
        },

        showNotification: function(message, type) {
            const notification = $('<div class="aiapg-notification aiapg-notification-' + type + '">' + message + '</div>');
            
            $('body').append(notification);
            
            // Position notification
            notification.css({
                position: 'fixed',
                top: '20px',
                right: '20px',
                zIndex: '999999',
                padding: '12px 20px',
                borderRadius: '4px',
                color: '#fff',
                fontWeight: '600',
                boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
                transform: 'translateX(100%)',
                transition: 'transform 0.3s ease'
            });
            
            // Set background color based on type
            switch (type) {
                case 'success':
                    notification.css('background-color', '#46b450');
                    break;
                case 'error':
                    notification.css('background-color', '#dc3232');
                    break;
                case 'warning':
                    notification.css('background-color', '#ffb900');
                    break;
                default:
                    notification.css('background-color', '#0073aa');
            }
            
            // Animate in
            setTimeout(function() {
                notification.css('transform', 'translateX(0)');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                notification.css('transform', 'translateX(100%)');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 5000);
        },

        // Format date
        formatDate: function(date) {
            return new Date(date).toLocaleDateString() + ' ' + new Date(date).toLocaleTimeString();
        },

        // Format duration
        formatDuration: function(seconds) {
            if (seconds < 60) {
                return seconds + 's';
            } else if (seconds < 3600) {
                return Math.floor(seconds / 60) + 'm ' + (seconds % 60) + 's';
            } else {
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                return hours + 'h ' + minutes + 'm';
            }
        },

        // Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        // Parse PHP serialized data (basic implementation for arrays)
        parsePHPSerialized: function(data) {
            try {
                if (!data || !data.startsWith('a:')) {
                    return null;
                }
                
                // Simple parser for PHP serialized arrays like a:2:{i:0;s:6:"prompt1";i:1;s:6:"prompt2";}
                const matches = data.match(/a:(\d+):\{(.+)\}/);
                if (!matches) {
                    return null;
                }
                
                const count = parseInt(matches[1]);
                const content = matches[2];
                const result = [];
                
                // Simple regex to extract string values: s:length:"value"
                const stringMatches = content.match(/s:\d+:"([^"]*)"/g);
                if (stringMatches) {
                    stringMatches.forEach(match => {
                        const valueMatch = match.match(/s:\d+:"([^"]*)"/);
                        if (valueMatch && valueMatch[1]) {
                            result.push(valueMatch[1]);
                        }
                    });
                }
                
                return result;
            } catch (e) {
                return null;
            }
        },

        // Throttle function
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        // Check model availability and disable form if no models are available
        checkModelAvailability: function() {
            const $modal = $('.aiapg-modal.active');
            if (!$modal.length) return;

            const $textModelSelect = $modal.find('#text_model');
            const $imageModelSelect = $modal.find('#image_model');
            const $fallbackModelSelect = $modal.find('#fallback_image_model');
            const $saveButton = $modal.find('#save-schedule');
            const $enableImagesCheckbox = $modal.find('#enable_images');

            let hasTextModels = $textModelSelect.length > 0 && $textModelSelect.find('option').length > 0;
            let hasImageModels = $imageModelSelect.length > 0 && $imageModelSelect.find('option').length > 0;
            let hasFallbackModels = $fallbackModelSelect.length > 0 && $fallbackModelSelect.find('option').length > 0;

            // Check if any models are available
            if (!hasTextModels) {
                AIAPG.showNotification('No text models available. Please configure API keys in settings.', 'warning');
                $saveButton.prop('disabled', true).addClass('disabled');
                return;
            }

            // If images are enabled but no image models available, show warning
            if ($enableImagesCheckbox.is(':checked') && !hasImageModels) {
                AIAPG.showNotification('Images are enabled but no image models available. Please configure API keys in settings.', 'warning');
                $saveButton.prop('disabled', true).addClass('disabled');
                return;
            }

            // If images are enabled but no fallback models available, show warning
            if ($enableImagesCheckbox.is(':checked') && !hasFallbackModels) {
                AIAPG.showNotification('Images are enabled but no fallback image models available. Please configure API keys in settings.', 'warning');
                $saveButton.prop('disabled', true).addClass('disabled');
                return;
            }

            // Enable save button if all required models are available
            $saveButton.prop('disabled', false).removeClass('disabled');
        }
    };

            // Initialize when document is ready
        $(document).ready(function() {
            AIAPG.init();
            
            // Initialize first FAQ item as open by default
            $('.faq-item:first .faq-question').addClass('active');
            $('.faq-item:first .faq-answer').show();
        });

    // Make AIAPG available globally
    window.AIAPG = AIAPG;

})(jQuery);
