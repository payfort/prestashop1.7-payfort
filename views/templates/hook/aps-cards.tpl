{*
 * 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='Amazon Payment Services Cards' mod='amazonpaymentservices'}
{/block}

{block name='page_content'}
    {if $cards|@count > 0}
        <table class="table table-striped table-bordered table-labeled table-responsive-lg">
            <thead class="thead-default">
                <tr>
                    <th class="text-sm-left">{l s='Cards details' mod='amazonpaymentservices'}</th>
                    <th class="text-sm-center">{l s='Expires' mod='amazonpaymentservices'}</th>
                    {if ($hide_delete_token != true)}
                        <th class="text-sm-center">{l s='Delete' mod='amazonpaymentservices'}</th>
                    {/if}
                </tr>
            </thead>
            <tbody>
                {foreach from=$cards item=card}
                    <tr>
                        <td class="text-sm-left" data-label="{l s='Cards details' mod='amazonpaymentservices'}">
                            {$card['card_type']|escape:'htmlall':'UTF-8'} card ending in {$card['masking_card']|escape:'htmlall':'UTF-8'}
                        </td>
                        <td class="text-sm-center" data-label="{l s='Expires' mod='amazonpaymentservices'}">
                            {$card['expiry_month']|escape:'htmlall':'UTF-8'}/{$card['expiry_year']|escape:'htmlall':'UTF-8'}
                        </td>
                        {if ($hide_delete_token != true)}
                            <td class="text-sm-center" data-label="{l s='Delete' mod='amazonpaymentservices'}">
                                <span class="remove_card" data-aps_card_token="{$card['token']|escape:'htmlall':'UTF-8'}">
                                    <i class="material-icons md-36">delete_forever</i>
                                </span>
                            </td>
                        {/if}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    {else}
        <p>{l s='There are no cards stored on our database.' mod='amazonpaymentservices'}</p>
    {/if}
{/block}