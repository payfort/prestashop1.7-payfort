<link rel="stylesheet" href="{$urls.base_url}css/checkout.css" type="text/css"/>

<form style="display:none" name="payfort_payment_form" id="payfort_payment_form" method="post"></form>
<div class="pf-iframe-background" id="div-pf-iframe" style="display:none">
    <div class="pf-iframe-container">
        <span class="pf-close-container">
            <i class="fa fa-times-circle pf-iframe-close" onclick="payfortFortMerchantPage.closePopup()"></i>
        </span>
        <i class="fa fa-spinner fa-spin pf-iframe-spin"></i>
        <div class="pf-iframe" id="pf_iframe_content"></div>
    </div>
    <input type='hidden' id="baseDir" value="{$urls.base_url}">
</div>