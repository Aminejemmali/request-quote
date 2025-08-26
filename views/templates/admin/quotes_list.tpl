<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i>
        Quote Requests ({if $quotes}{$quotes|count}{else}0{/if} total)
    </div>
    
    <div class="panel-body">
        {if $quotes && count($quotes) > 0}
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Product</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$quotes item=quote}
                            <tr>
                                <td>#{$quote.id_quote}</td>
                                <td>{$quote.client_name|escape:'html':'UTF-8'}</td>
                                <td>
                                    <a href="mailto:{$quote.email|escape:'html':'UTF-8'}">
                                        {$quote.email|escape:'html':'UTF-8'}
                                    </a>
                                </td>
                                <td>{if $quote.phone}{$quote.phone|escape:'html':'UTF-8'}{else}-{/if}</td>
                                <td>
                                    {if $quote.product_name}
                                        {$quote.product_name|escape:'html':'UTF-8'}
                                    {else}
                                        Product #{$quote.id_product}
                                    {/if}
                                </td>
                                <td>{$quote.date_add|date_format:"%Y-%m-%d %H:%M"}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{$current_url}&view&id_quote={$quote.id_quote}" 
                                           class="btn btn-default btn-xs" title="View">
                                            <i class="icon-eye"></i> View
                                        </a>
                                        <a href="{$current_url}&delete&id_quote={$quote.id_quote}" 
                                           class="btn btn-danger btn-xs" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this quote?');">
                                            <i class="icon-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <div class="alert alert-info">
                <i class="icon-info-circle"></i>
                No quote requests found yet.
            </div>
        {/if}
    </div>
</div>

<style>
.btn-xs {
    padding: 2px 8px;
    font-size: 11px;
    margin: 0 2px;
}
.table th {
    background-color: #f8f9fa;
    font-weight: bold;
}
.table td {
    vertical-align: middle;
}
</style> 