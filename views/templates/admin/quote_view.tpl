<div class="panel">
    <div class="panel-heading">
        <i class="icon-eye"></i>
        Quote Request Details - ID #{$quote.id_quote}
    </div>
    
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h4>Client Information</h4>
                <table class="table">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{$quote.client_name|escape:'html':'UTF-8'}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><a href="mailto:{$quote.email|escape:'html':'UTF-8'}">{$quote.email|escape:'html':'UTF-8'}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{if $quote.phone}{$quote.phone|escape:'html':'UTF-8'}{else}-{/if}</td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong></td>
                        <td>{$quote.date_add|date_format:"%Y-%m-%d %H:%M:%S"}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h4>Product Information</h4>
                <table class="table">
                    <tr>
                        <td><strong>Product:</strong></td>
                        <td>{if $quote.product_name}{$quote.product_name|escape:'html':'UTF-8'}{else}Product #{$quote.id_product}{/if}</td>
                    </tr>
                    <tr>
                        <td><strong>Product ID:</strong></td>
                        <td>#{$quote.id_product}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        {if $quote.message}
        <div class="row">
            <div class="col-md-12">
                <h4>Message</h4>
                <div class="well">
                    {$quote.message|escape:'html':'UTF-8'|nl2br}
                </div>
            </div>
        </div>
        {/if}
    </div>
    
    <div class="panel-footer">
        <a href="{$back_url}" class="btn btn-default">
            <i class="icon-arrow-left"></i>
            Back to List
        </a>
    </div>
</div> 