{*
 * Product Actions Template for RequestQuote Module
 * Replaces add-to-cart functionality with quote request button
 *}

{* Hide only pricing and add-to-cart elements, preserve images *}
<style>
    .product-add-to-cart,
    .product-variants,
    .product-customization,
    .product-prices,
    .product-quantity,
    .product-price,
    .current-price,
    .regular-price,
    .discount-percentage,
    .product-discounts {
        display: none !important;
    }
    
    /* Ensure images remain visible */
    .product-cover,
    .product-images,
    .product-cover-modal,
    .product-thumbs,
    .product-thumb,
    .product-cover-thumbnails {
        display: block !important;
    }
</style>

{* Quote Request Button *}
<div class="request-quote-section">
    <div class="request-quote-button-wrapper">
        <button type="button" class="btn btn-primary btn-lg request-quote-btn" data-toggle="modal" data-target="#requestQuoteModal">
            <i class="icon-quote-left"></i> {l s='Request Quote' mod='requestquote'}
        </button>
    </div>
    
    <div class="request-quote-info">
        <p class="text-muted">
            <i class="icon-info-circle"></i> 
            {l s='Contact us for a personalized quote for this product.' mod='requestquote'}
        </p>
    </div>
</div>

{* Quote Request Modal *}
<div class="modal fade" id="requestQuoteModal" tabindex="-1" role="dialog" aria-labelledby="requestQuoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestQuoteModalLabel">
                    <i class="icon-quote-left"></i> {l s='Request Quote' mod='requestquote'}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <form id="requestQuoteForm" class="request-quote-form">
                <div class="modal-body">
                    {* CSRF Token *}
                    <input type="hidden" name="csrf_token" value="{$csrf_token}">
                    <input type="hidden" name="product_id" value="{$product->id}">
                    
                    <div class="row">
                        {* Client Name *}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_name" class="form-control-label required">
                                    {l s='Full Name' mod='requestquote'} *
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="client_name" 
                                       name="client_name" 
                                       required 
                                       minlength="2"
                                       placeholder="{l s='Enter your full name' mod='requestquote'}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        {* Email *}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-control-label required">
                                    {l s='Email Address' mod='requestquote'} *
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required
                                       placeholder="{l s='Enter your email address' mod='requestquote'}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        {* Phone *}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="form-control-label">
                                    {l s='Phone Number' mod='requestquote'} {if $require_phone}*{/if}
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       {if $require_phone}required{/if}
                                       minlength="10"
                                       placeholder="{l s='Enter your phone number' mod='requestquote'}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        {* Product Info (Read-only) *}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-control-label">
                                    {l s='Product' mod='requestquote'}
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="{$product->name}" 
                                       readonly>
                                <small class="form-text text-muted">
                                    {l s='Product ID:' mod='requestquote'} {$product->id}
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    {* Note *}
                    <div class="form-group">
                        <label for="note" class="form-control-label">
                            {l s='Additional Notes' mod='requestquote'}
                        </label>
                        <textarea class="form-control" 
                                  id="note" 
                                  name="note" 
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="{l s='Any additional information or specific requirements...' mod='requestquote'}"></textarea>
                        <small class="form-text text-muted">
                            {l s='Maximum 1000 characters' mod='requestquote'}
                        </small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    {* Success/Error Messages *}
                    <div id="requestQuoteMessages" class="alert" style="display: none;"></div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {l s='Cancel' mod='requestquote'}
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitQuoteBtn">
                        <i class="icon-send"></i> {l s='Submit Quote Request' mod='requestquote'}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{* Loading Spinner *}
<div id="requestQuoteLoading" class="request-quote-loading" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">{l s='Loading...' mod='requestquote'}</span>
    </div>
</div>

{* JavaScript for form handling *}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('requestQuoteForm');
    const submitBtn = document.getElementById('submitQuoteBtn');
    const messagesDiv = document.getElementById('requestQuoteMessages');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> {l s='Submitting...' mod='requestquote'}';
            messagesDiv.style.display = 'none';
            
            // Prepare form data
            const formData = new FormData(form);
            formData.append('ajax', '1');
            formData.append('action', 'submitQuote');
            
            // Submit via AJAX
            fetch('{$link->getModuleLink('requestquote', 'quote')}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    messagesDiv.className = 'alert alert-success';
                    messagesDiv.innerHTML = '<i class="fa fa-check"></i> ' + data.message;
                    messagesDiv.style.display = 'block';
                    
                    // Reset form
                    form.reset();
                    
                    // Close modal after delay
                    setTimeout(function() {
                        $('#requestQuoteModal').modal('hide');
                        messagesDiv.style.display = 'none';
                    }, 3000);
                } else {
                    // Show error message
                    messagesDiv.className = 'alert alert-danger';
                    messagesDiv.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + data.message;
                    messagesDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messagesDiv.className = 'alert alert-danger';
                messagesDiv.innerHTML = '<i class="fa fa-exclamation-triangle"></i> {l s='An error occurred. Please try again.' mod='requestquote'}';
                messagesDiv.style.display = 'block';
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="icon-send"></i> {l s='Submit Quote Request' mod='requestquote'}';
            });
        });
    }
});
</script> 