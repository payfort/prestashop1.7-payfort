{if $status == 'cancelled'}
     <h2>{l s='Payment Cancelled!' mod='payfortfort'}</h2>
        <p class="warning">
            {l s='You have canceled the payment, please try agian.' mod='payfortfort'} 
        </p>
{else}
    {if $status == 'ok'}
        <h2>{l s='Payment Accepted!' mod='payfortfort'}</h2>
            <p>{l s='Your order on' mod='payfortfort'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='payfortfort'}
                    <br /><br /><span class="bold">{l s='Your order will be sent as soon as possible.' mod='payfortfort'}</span>
                    <br /><br />{l s='For any questions or for further information, please contact our' mod='payfortfort'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortfort'}</a>.
            </p>
    {else}
        <h2>{l s='Payment Failed!' mod='payfortfort'}</h2>
            <p class="warning">
                    {l s='Sorry, Could not complete payment for your order, please check your payment details and try again. If you think this is an error, you can contact our' mod='payfortfort'} 
                    <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payfortfort'}</a>.
            </p>
    {/if}
{/if}