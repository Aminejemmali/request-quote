/**
 * RequestQuote Module JavaScript
 * Handles form validation, AJAX submission, and user interactions
 */

(function($) {
    'use strict';

    // Module namespace
    var RequestQuote = {
        
        // Configuration
        config: {
            formSelector: '#requestQuoteForm',
            modalSelector: '#requestQuoteModal',
            buttonSelector: '.request-quote-btn',
            messagesSelector: '#requestQuoteMessages',
            loadingSelector: '#requestQuoteLoading',
            submitButtonSelector: '#submitQuoteBtn',
            ajaxUrl: prestashop.urls.base_url + 'modules/requestquote/views/controllers/front/quote.php'
        },

        // Initialize the module
        init: function() {
            this.bindEvents();
            this.setupFormValidation();
            console.log('RequestQuote module initialized');
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Form submission
            $(this.config.formSelector).on('submit', function(e) {
                e.preventDefault();
                self.handleFormSubmission();
            });

            // Modal events
            $(this.config.modalSelector).on('shown.bs.modal', function() {
                self.onModalShown();
            });

            $(this.config.modalSelector).on('hidden.bs.modal', function() {
                self.onModalHidden();
            });

            // Input validation on blur
            $(this.config.formSelector + ' input, ' + this.config.formSelector + ' textarea').on('blur', function() {
                self.validateField($(this));
            });

            // Real-time validation for required fields
            $(this.config.formSelector + ' input[required], ' + this.config.formSelector + ' textarea[required]').on('input', function() {
                self.validateField($(this));
            });

            // Character counter for note field
            $(this.config.formSelector + ' textarea[name="note"]').on('input', function() {
                self.updateCharacterCount($(this));
            });
        },

        // Setup form validation
        setupFormValidation: function() {
            var self = this;
            
            // Add character counter to note field
            var noteField = $(this.config.formSelector + ' textarea[name="note"]');
            if (noteField.length) {
                var counter = $('<small class="form-text text-muted character-counter"></small>');
                noteField.after(counter);
                self.updateCharacterCount(noteField);
            }
        },

        // Handle form submission
        handleFormSubmission: function() {
            var self = this;
            var form = $(this.config.formSelector);
            var submitBtn = $(this.config.submitButtonSelector);

            // Validate all fields
            if (!this.validateForm()) {
                this.showMessage('Please correct the errors in the form.', 'danger');
                return;
            }

            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Submitting...');
            this.showLoading(true);

            // Collect form data
            var formData = this.serializeFormData(form);

            // Submit via AJAX
            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    self.handleSubmissionResponse(response);
                },
                error: function(xhr, status, error) {
                    self.handleSubmissionError(xhr, status, error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('<i class="icon-send"></i> Submit Quote Request');
                    self.showLoading(false);
                }
            });
        },

        // Serialize form data
        serializeFormData: function(form) {
            var data = {};
            form.find('input, textarea, select').each(function() {
                var field = $(this);
                var name = field.attr('name');
                var value = field.val();

                if (name && value !== undefined) {
                    data[name] = value;
                }
            });
            return data;
        },

        // Handle submission response
        handleSubmissionResponse: function(response) {
            if (response.success) {
                this.showMessage(response.message, 'success');
                this.resetForm();
                
                // Auto-close modal after 3 seconds
                setTimeout(function() {
                    $(this.config.modalSelector).modal('hide');
                }.bind(this), 3000);
            } else {
                this.showMessage(response.message || 'An error occurred. Please try again.', 'danger');
            }
        },

        // Handle submission error
        handleSubmissionError: function(xhr, status, error) {
            var errorMessage = 'An error occurred while submitting your request.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (status === 'error') {
                errorMessage = 'Network error. Please check your connection and try again.';
            }

            this.showMessage(errorMessage, 'danger');
            console.error('RequestQuote AJAX Error:', {xhr: xhr, status: status, error: error});
        },

        // Validate form
        validateForm: function() {
            var isValid = true;
            var form = $(this.config.formSelector);

            // Validate all required fields
            form.find('input[required], textarea[required]').each(function() {
                if (!this.validateField($(this))) {
                    isValid = false;
                }
            });

            // Validate email format
            var emailField = form.find('input[name="email"]');
            if (emailField.length && emailField.val()) {
                if (!this.validateEmail(emailField.val())) {
                    this.showFieldError(emailField, 'Please enter a valid email address.');
                    isValid = false;
                }
            }

            // Validate phone if required
            var phoneField = form.find('input[name="phone"]');
            if (phoneField.length && phoneField.val() && phoneField.attr('required')) {
                if (!this.validatePhone(phoneField.val())) {
                    this.showFieldError(phoneField, 'Please enter a valid phone number.');
                    isValid = false;
                }
            }

            return isValid;
        },

        // Validate individual field
        validateField: function(field) {
            var value = field.val().trim();
            var isRequired = field.attr('required');
            var minLength = field.attr('minlength');
            var maxLength = field.attr('maxlength');

            // Clear previous errors
            this.clearFieldError(field);

            // Check if required
            if (isRequired && !value) {
                this.showFieldError(field, 'This field is required.');
                return false;
            }

            // Check minimum length
            if (minLength && value.length < parseInt(minLength)) {
                this.showFieldError(field, 'Minimum length is ' + minLength + ' characters.');
                return false;
            }

            // Check maximum length
            if (maxLength && value.length > parseInt(maxLength)) {
                this.showFieldError(field, 'Maximum length is ' + maxLength + ' characters.');
                return false;
            }

            // Field is valid
            field.addClass('is-valid');
            return true;
        },

        // Show field error
        showFieldError: function(field, message) {
            field.removeClass('is-valid').addClass('is-invalid');
            field.siblings('.invalid-feedback').text(message);
        },

        // Clear field error
        clearFieldError: function(field) {
            field.removeClass('is-invalid is-valid');
            field.siblings('.invalid-feedback').text('');
        },

        // Validate email format
        validateEmail: function(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        // Validate phone format
        validatePhone: function(phone) {
            var phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            return phoneRegex.test(phone);
        },

        // Update character count for note field
        updateCharacterCount: function(field) {
            var currentLength = field.val().length;
            var maxLength = field.attr('maxlength');
            var counter = field.siblings('.character-counter');
            
            if (counter.length && maxLength) {
                var remaining = maxLength - currentLength;
                counter.text(currentLength + ' / ' + maxLength + ' characters');
                
                if (remaining < 50) {
                    counter.addClass('text-warning');
                } else {
                    counter.removeClass('text-warning');
                }
            }
        },

        // Show message
        showMessage: function(message, type) {
            var messagesContainer = $(this.config.messagesSelector);
            messagesContainer.removeClass().addClass('alert alert-' + type).text(message).show();
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                messagesContainer.fadeOut();
            }, 5000);
        },

        // Show/hide loading spinner
        showLoading: function(show) {
            if (show) {
                $(this.config.loadingSelector).show();
            } else {
                $(this.config.loadingSelector).hide();
            }
        },

        // Reset form
        resetForm: function() {
            var form = $(this.config.formSelector);
            form[0].reset();
            form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            form.find('.invalid-feedback').text('');
            this.updateCharacterCount(form.find('textarea[name="note"]'));
        },

        // Modal shown event
        onModalShown: function() {
            // Focus on first input
            $(this.config.formSelector + ' input:first').focus();
            
            // Clear any previous messages
            $(this.config.messagesSelector).hide();
        },

        // Modal hidden event
        onModalHidden: function() {
            // Reset form when modal is closed
            this.resetForm();
        },

        // Utility function to check if element exists
        elementExists: function(selector) {
            return $(selector).length > 0;
        },

        // Debug function
        debug: function(message, data) {
            if (console && console.log) {
                console.log('RequestQuote Debug:', message, data);
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Check if we're on a product page
        if (RequestQuote.elementExists(RequestQuote.config.formSelector)) {
            RequestQuote.init();
        }
    });

    // Make RequestQuote available globally for debugging
    window.RequestQuote = RequestQuote;

})(jQuery); 