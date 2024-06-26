{*
 * 2007-2019 PrestaShop
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
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) Stripe
 * @license   Commercial license
*}
{if $prestashop_version >= '1.7'}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" href="{$link->getModuleLink('amazonpaymentservices', 'apscards')|escape:'html':'UTF-8'}" title="{l s='Amazon Payment Services Credit Cards' mod='amazonpaymentservices'}">
        <span class="link-item">
            <i class="material-icons md-36">payment</i>
            {l s='Amazon Payment Services Credit Cards' mod='amazonpaymentservices'}
        </span>
    </a>
{else}
    <li>
        <a href="{$link->getModuleLink('amazonpaymentservices', 'apscards')|escape:'html':'UTF-8'}" title="{l s='Amazon Payment Services Credit Cards' mod='amazonpaymentservices'}">
            <i class="icon-credit-card"></i>
            <span>{l s='Amazon Payment Services Credit Cards' mod='amazonpaymentservices'}</span>
        </a>
    </li>
{/if}