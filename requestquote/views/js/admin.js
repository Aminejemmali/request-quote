/**
 * RequestQuote Module Admin JavaScript
 * Enhanced functionality for the back office quote request management interface
 */

(function($) {
    'use strict';

    // Admin module namespace
    var RequestQuoteAdmin = {
        
        // Configuration
        config: {
            tableSelector: '.table',
            bulkActionsSelector: '.bulk-actions',
            filterFormSelector: '.filter-form',
            searchInputSelector: 'input[name="search"]',
            paginationSelector: '.pagination',
            confirmDeleteSelector: '.confirm-delete',
            exportSelector: '.export-data',
            refreshSelector: '.refresh-data'
        },

        // Initialize the admin module
        init: function() {
            this.bindEvents();
            this.setupDataTables();
            this.setupFilters();
            this.setupBulkActions();
            this.setupSearch();
            this.setupPagination();
            this.setupExport();
            this.setupRefresh();
            console.log('RequestQuote Admin module initialized');
        },

        // Bind event handlers
        bindEvents: function() {
            var self = this;

            // Confirm delete actions
            $(document).on('click', this.config.confirmDeleteSelector, function(e) {
                e.preventDefault();
                self.confirmDelete($(this));
            });

            // Row selection for bulk actions
            $(document).on('change', 'input[name="bulk_action_select[]"]', function() {
                self.updateBulkActions();
            });

            // Select all checkbox
            $(document).on('change', 'input[name="bulk_action_select_all"]', function() {
                self.toggleSelectAll($(this));
            });

            // Quick actions
            $(document).on('click', '.quick-action', function(e) {
                e.preventDefault();
                self.handleQuickAction($(this));
            });

            // Status change
            $(document).on('change', '.status-select', function() {
                self.updateStatus($(this));
            });

            // Notes update
            $(document).on('blur', '.notes-input', function() {
                self.updateNotes($(this));
            });

            // Filter form submission
            $(this.config.filterFormSelector).on('submit', function(e) {
                e.preventDefault();
                self.applyFilters();
            });

            // Clear filters
            $(document).on('click', '.clear-filters', function(e) {
                e.preventDefault();
                self.clearFilters();
            });
        },

        // Setup data tables with enhanced functionality
        setupDataTables: function() {
            var self = this;
            
            // Add row hover effects
            $(this.config.tableSelector + ' tbody tr').hover(
                function() {
                    $(this).addClass('table-hover');
                },
                function() {
                    $(this).removeClass('table-hover');
                }
            );

            // Add click to select functionality
            $(this.config.tableSelector + ' tbody tr').on('click', function(e) {
                if (!$(e.target).is('input, a, button')) {
                    var checkbox = $(this).find('input[name="bulk_action_select[]"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });

            // Add keyboard navigation
            $(this.config.tableSelector + ' tbody tr').on('keydown', function(e) {
                if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
                    e.preventDefault();
                    var checkbox = $(this).find('input[name="bulk_action_select[]"]');
                    checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                }
            });
        },

        // Setup filters
        setupFilters: function() {
            var self = this;
            
            // Date range picker
            if ($.fn.daterangepicker) {
                $('.date-range').daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear'
                    }
                });

                $('.date-range').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
                });

                $('.date-range').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                });
            }

            // Product autocomplete
            if ($.fn.autocomplete) {
                $('.product-autocomplete').autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                            dataType: 'json',
                            data: {
                                action: 'search_products',
                                term: request.term
                            },
                            success: function(data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.label);
                        $(this).siblings('input[name="product_id"]').val(ui.item.value);
                        return false;
                    }
                });
            }
        },

        // Setup bulk actions
        setupBulkActions: function() {
            var self = this;
            
            // Bulk action form submission
            $(this.config.bulkActionsSelector + ' form').on('submit', function(e) {
                e.preventDefault();
                self.executeBulkAction($(this));
            });

            // Update bulk action button state
            this.updateBulkActions();
        },

        // Update bulk actions based on selection
        updateBulkActions: function() {
            var selectedCount = $('input[name="bulk_action_select[]"]:checked').length;
            var bulkActionBtn = $(this.config.bulkActionsSelector + ' .btn-primary');
            var bulkActionSelect = $(this.config.bulkActionsSelector + ' select');

            if (selectedCount > 0) {
                bulkActionBtn.prop('disabled', false);
                bulkActionBtn.text('Apply to ' + selectedCount + ' selected');
                bulkActionSelect.prop('disabled', false);
            } else {
                bulkActionBtn.prop('disabled', true);
                bulkActionBtn.text('Apply to selected');
                bulkActionSelect.prop('disabled', true);
            }
        },

        // Toggle select all
        toggleSelectAll: function(checkbox) {
            var isChecked = checkbox.prop('checked');
            $('input[name="bulk_action_select[]"]').prop('checked', isChecked);
            this.updateBulkActions();
        },

        // Execute bulk action
        executeBulkAction: function(form) {
            var self = this;
            var action = form.find('select[name="bulk_action"]').val();
            var selectedIds = [];

            $('input[name="bulk_action_select[]"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                this.showMessage('Please select at least one quote request.', 'warning');
                return;
            }

            if (!action) {
                this.showMessage('Please select an action to perform.', 'warning');
                return;
            }

            // Confirm action
            var confirmMessage = 'Are you sure you want to ' + action + ' ' + selectedIds.length + ' selected quote request(s)?';
            if (!confirm(confirmMessage)) {
                return;
            }

            // Show loading
            this.showLoading(true);

            // Execute action
            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'POST',
                data: {
                    action: 'bulk_action',
                    bulk_action: action,
                    quote_ids: selectedIds,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage(response.message, 'success');
                        self.refreshTable();
                    } else {
                        self.showMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while executing the bulk action.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Setup search functionality
        setupSearch: function() {
            var self = this;
            var searchInput = $(this.config.searchInputSelector);
            var searchTimeout;

            // Debounced search
            searchInput.on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    self.performSearch(searchInput.val());
                }, 500);
            });

            // Search on Enter
            searchInput.on('keypress', function(e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    self.performSearch($(this).val());
                }
            });
        },

        // Perform search
        performSearch: function(term) {
            var self = this;
            
            if (term.length < 2 && term.length > 0) {
                return; // Minimum search length
            }

            this.showLoading(true);

            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'GET',
                data: {
                    action: 'search',
                    term: term,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.updateTableContent(response.data);
                    } else {
                        self.showMessage(response.message, 'warning');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while searching.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Setup pagination
        setupPagination: function() {
            var self = this;
            
            $(this.config.paginationSelector).on('click', 'a', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                if (page) {
                    self.loadPage(page);
                }
            });
        },

        // Load specific page
        loadPage: function(page) {
            var self = this;
            
            this.showLoading(true);

            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'GET',
                data: {
                    action: 'get_page',
                    page: page,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.updateTableContent(response.data);
                        self.updatePagination(response.pagination);
                        // Update URL without page reload
                        window.history.pushState({}, '', '?page=' + page);
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while loading the page.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Setup export functionality
        setupExport: function() {
            var self = this;
            
            $(this.config.exportSelector).on('click', function(e) {
                e.preventDefault();
                self.exportData($(this).data('format'));
            });
        },

        // Export data
        exportData: function(format) {
            var self = this;
            var filters = this.getActiveFilters();
            
            this.showLoading(true);

            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'POST',
                data: {
                    action: 'export',
                    format: format,
                    filters: filters,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Download file
                        var link = document.createElement('a');
                        link.href = response.download_url;
                        link.download = response.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        self.showMessage('Export completed successfully.', 'success');
                    } else {
                        self.showMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while exporting data.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Setup refresh functionality
        setupRefresh: function() {
            var self = this;
            
            $(this.config.refreshSelector).on('click', function(e) {
                e.preventDefault();
                self.refreshTable();
            });

            // Auto-refresh every 5 minutes
            setInterval(function() {
                self.refreshTable();
            }, 300000);
        },

        // Refresh table data
        refreshTable: function() {
            var self = this;
            
            this.showLoading(true);

            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'GET',
                data: {
                    action: 'refresh',
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.updateTableContent(response.data);
                        self.showMessage('Table refreshed successfully.', 'info');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while refreshing the table.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Update table content
        updateTableContent: function(data) {
            var tbody = $(this.config.tableSelector + ' tbody');
            tbody.html(data);
            
            // Reinitialize table functionality
            this.setupDataTables();
        },

        // Update pagination
        updatePagination: function(pagination) {
            $(this.config.paginationSelector).html(pagination);
        },

        // Get active filters
        getActiveFilters: function() {
            var filters = {};
            $(this.config.filterFormSelector + ' input, ' + this.config.filterFormSelector + ' select').each(function() {
                var field = $(this);
                var name = field.attr('name');
                var value = field.val();
                
                if (name && value) {
                    filters[name] = value;
                }
            });
            return filters;
        },

        // Apply filters
        applyFilters: function() {
            var filters = this.getActiveFilters();
            var self = this;
            
            this.showLoading(true);

            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'POST',
                data: {
                    action: 'apply_filters',
                    filters: filters,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.updateTableContent(response.data);
                        self.updatePagination(response.pagination);
                        self.showMessage('Filters applied successfully.', 'success');
                    } else {
                        self.showMessage(response.message, 'warning');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while applying filters.', 'danger');
                },
                complete: function() {
                    self.showLoading(false);
                }
            });
        },

        // Clear filters
        clearFilters: function() {
            $(this.config.filterFormSelector)[0].reset();
            this.refreshTable();
        },

        // Handle quick actions
        handleQuickAction: function(button) {
            var action = button.data('action');
            var quoteId = button.data('id');
            var self = this;

            switch (action) {
                case 'view':
                    this.viewQuote(quoteId);
                    break;
                case 'edit':
                    this.editQuote(quoteId);
                    break;
                case 'delete':
                    this.deleteQuote(quoteId);
                    break;
                case 'status':
                    this.changeStatus(quoteId);
                    break;
                default:
                    console.log('Unknown action:', action);
            }
        },

        // View quote details
        viewQuote: function(quoteId) {
            // Open modal or navigate to view page
            window.open(prestashop.urls.base_url + 'admin/index.php?controller=AdminRequestQuote&id_quote=' + quoteId + '&view', '_blank');
        },

        // Edit quote
        editQuote: function(quoteId) {
            // Open edit modal or navigate to edit page
            window.open(prestashop.urls.base_url + 'admin/index.php?controller=AdminRequestQuote&id_quote=' + quoteId + '&update', '_blank');
        },

        // Delete quote
        deleteQuote: function(quoteId) {
            if (confirm('Are you sure you want to delete this quote request?')) {
                var self = this;
                
                $.ajax({
                    url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        quote_id: quoteId,
                        token: prestashop.token
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showMessage('Quote request deleted successfully.', 'success');
                            self.refreshTable();
                        } else {
                            self.showMessage(response.message, 'danger');
                        }
                    },
                    error: function() {
                        self.showMessage('An error occurred while deleting the quote request.', 'danger');
                    }
                });
            }
        },

        // Change quote status
        changeStatus: function(quoteId) {
            var self = this;
            var newStatus = prompt('Enter new status (new, processing, completed, cancelled):');
            
            if (newStatus && ['new', 'processing', 'completed', 'cancelled'].includes(newStatus.toLowerCase())) {
                $.ajax({
                    url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                    type: 'POST',
                    data: {
                        action: 'change_status',
                        quote_id: quoteId,
                        status: newStatus.toLowerCase(),
                        token: prestashop.token
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            self.showMessage('Status updated successfully.', 'success');
                            self.refreshTable();
                        } else {
                            self.showMessage(response.message, 'danger');
                        }
                    },
                    error: function() {
                        self.showMessage('An error occurred while updating the status.', 'danger');
                    }
                });
            }
        },

        // Update status via AJAX
        updateStatus: function(select) {
            var self = this;
            var quoteId = select.data('id');
            var newStatus = select.val();
            
            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'POST',
                data: {
                    action: 'update_status',
                    quote_id: quoteId,
                    status: newStatus,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage('Status updated successfully.', 'success');
                    } else {
                        self.showMessage(response.message, 'danger');
                        // Revert selection
                        select.val(select.data('original'));
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while updating the status.', 'danger');
                    // Revert selection
                    select.val(select.data('original'));
                }
            });
        },

        // Update notes via AJAX
        updateNotes: function(input) {
            var self = this;
            var quoteId = input.data('id');
            var notes = input.val();
            
            $.ajax({
                url: prestashop.urls.base_url + 'modules/requestquote/ajax.php',
                type: 'POST',
                data: {
                    action: 'update_notes',
                    quote_id: quoteId,
                    notes: notes,
                    token: prestashop.token
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showMessage('Notes updated successfully.', 'success');
                    } else {
                        self.showMessage(response.message, 'danger');
                    }
                },
                error: function() {
                    self.showMessage('An error occurred while updating the notes.', 'danger');
                }
            });
        },

        // Confirm delete action
        confirmDelete: function(button) {
            var message = button.data('confirm') || 'Are you sure you want to delete this item?';
            if (confirm(message)) {
                button.closest('form').submit();
            }
        },

        // Show message
        showMessage: function(message, type) {
            var alertClass = 'alert-' + type;
            var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                           message +
                           '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                           '<span aria-hidden="true">&times;</span>' +
                           '</button>' +
                           '</div>';
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert
            $('.panel-heading').after(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        },

        // Show/hide loading
        showLoading: function(show) {
            if (show) {
                $('body').addClass('loading');
            } else {
                $('body').removeClass('loading');
            }
        },

        // Utility function to check if element exists
        elementExists: function(selector) {
            return $(selector).length > 0;
        },

        // Debug function
        debug: function(message, data) {
            if (console && console.log) {
                console.log('RequestQuote Admin Debug:', message, data);
            }
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Check if we're on the admin page
        if (RequestQuoteAdmin.elementExists(RequestQuoteAdmin.config.tableSelector)) {
            RequestQuoteAdmin.init();
        }
    });

    // Make RequestQuoteAdmin available globally for debugging
    window.RequestQuoteAdmin = RequestQuoteAdmin;

})(jQuery); 