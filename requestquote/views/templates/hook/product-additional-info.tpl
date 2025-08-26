{*
 * Product Additional Info Template for RequestQuote Module
 * Alternative hook template for product page modifications
 *}

{* Hide the original add-to-cart section *}
<style>
    .product-add-to-cart,
    .product-variants,
    .product-customization,
    .product-prices,
    .product-quantity,
    .product-actions {
        display: none !important;
    }
</style>

{* Quote Request Section *}
<div class="request-quote-section request-quote-additional">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">
                <i class="icon-quote-left text-primary"></i> 
                {l s='Request a Quote' mod='requestquote'}
            </h4>
            
            <p class="card-text">
                {l s='Interested in this product? Contact us for a personalized quote tailored to your needs.' mod='requestquote'}
            </p>
            
            <button type="button" class="btn btn-primary btn-lg request-quote-btn" data-toggle="modal" data-target="#requestQuoteModal">
                <i class="icon-quote-left"></i> {l s='Request Quote Now' mod='requestquote'}
            </button>
            
            <div class="request-quote-benefits mt-3">
                <ul class="list-unstyled">
                    <li><i class="icon-check text-success"></i> {l s='Personalized pricing' mod='requestquote'}</li>
                    <li><i class="icon-check text-success"></i> {l s='Bulk order discounts' mod='requestquote'}</li>
                    <li><i class="icon-check text-success"></i> {l s='Custom specifications' mod='requestquote'}</li>
                    <li><i class="icon-check text-success"></i> {l s='Fast response time' mod='requestquote'}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{* Include the same modal as in the main template *}
{include file='./product-actions.tpl'} 