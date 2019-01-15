{extends file='page.tpl'}

{block name='page_content_container'}
    <section id="content">
        <div>
            <h3>{l s='An error occurred' mod='payfortfort'}:</h3>
            <ul class="alert alert-danger">
                <li>{$error|escape:'html':'UTF-8'}</li>
                <li><a href="{$link->getPageLink('cart&action=show')}">{l s='Return to your cart' mod='payfortfort'}</a> </li>
            </ul>
        </div>
    </section>
{/block}
