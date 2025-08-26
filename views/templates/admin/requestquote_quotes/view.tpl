{*
 * Admin View Template for Quote Details
 * Displays full information about a quote request
 *}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-eye"></i>
        {l s='Quote Request Details' mod='requestquote'}
    </div>
    
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <h4>{l s='Client Information' mod='requestquote'}</h4>
                <table class="table table-striped">
                    <tr>
                        <td><strong>{l s='Name:' mod='requestquote'}</strong></td>
                        <td>{$quote->client_name|escape:'html':'UTF-8'}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Email:' mod='requestquote'}</strong></td>
                        <td>
                            <a href="mailto:{$quote->email|escape:'html':'UTF-8'}">
                                {$quote->email|escape:'html':'UTF-8'}
                            </a>
                        </td>
                    </tr>
                    {if $quote->phone}
                    <tr>
                        <td><strong>{l s='Phone:' mod='requestquote'}</strong></td>
                        <td>
                            <a href="tel:{$quote->phone|escape:'html':'UTF-8'}">
                                {$quote->phone|escape:'html':'UTF-8'}
                            </a>
                        </td>
                    </tr>
                    {/if}
                </table>
            </div>
            
            <div class="col-md-6">
                <h4>{l s='Product Information' mod='requestquote'}</h4>
                <table class="table table-striped">
                    <tr>
                        <td><strong>{l s='Product:' mod='requestquote'}</strong></td>
                        <td>
                            {if $product->name}
                                {$product->name|escape:'html':'UTF-8'}
                            {else}
                                {l s='Product #' mod='requestquote'}{$quote->id_product}
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Product ID:' mod='requestquote'}</strong></td>
                        <td>{$quote->id_product}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Shop:' mod='requestquote'}</strong></td>
                        <td>{$shop_name|escape:'html':'UTF-8'}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        {if $quote->note}
        <div class="row">
            <div class="col-md-12">
                <h4>{l s='Additional Notes' mod='requestquote'}</h4>
                <div class="well">
                    {$quote->note|escape:'html':'UTF-8'|nl2br}
                </div>
            </div>
        </div>
        {/if}
        
        <div class="row">
            <div class="col-md-12">
                <h4>{l s='Request Information' mod='requestquote'}</h4>
                <table class="table table-striped">
                    <tr>
                        <td><strong>{l s='Quote ID:' mod='requestquote'}</strong></td>
                        <td>{$quote->id_quote}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Date Submitted:' mod='requestquote'}</strong></td>
                        <td>{$quote->date_add|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                    </tr>
                    <tr>
                        <td><strong>{l s='Last Updated:' mod='requestquote'}</strong></td>
                        <td>{$quote->date_upd|date_format:'%Y-%m-%d %H:%M:%S'}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminRequestQuote')}" class="btn btn-default">
            <i class="icon-arrow-left"></i> {l s='Back to List' mod='requestquote'}
        </a>
        <a href="mailto:{$quote->email|escape:'html':'UTF-8'}?subject=Re: Quote Request for {$product->name|escape:'url'}" class="btn btn-primary">
            <i class="icon-envelope"></i> {l s='Reply via Email' mod='requestquote'}
        </a>
    </div>
</div> 