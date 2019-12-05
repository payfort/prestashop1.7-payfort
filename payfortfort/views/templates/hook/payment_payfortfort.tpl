<form action="#" id="frm_payfortfort_merchantpage2" name="frm_payfortfort_merchantpage2">
  
  
  
  <p> 
	{if $mada_branding eq 1}
     <img src="{$moduleDir|addslashes}payfortfort/img/mada.jpeg" alt="mada" height="26" width="45"/>	
     {/if}
     <img src="{$moduleDir|addslashes}payfortfort/img/visa.png" alt="mada" height="26" width="45" style="margin:0px 5px"/>
     <img src="{$moduleDir|addslashes}payfortfort/img/mastercard.jpeg" alt="mada" height="26" width="45"/>   
  </p>

  <p>
    <label>{l s='Card number'}</label>
    <input type="text" size="19" autocomplete="off" id="payfort_fort_card_number">
  </p>

  <p>
    <label>{l s='Name'}</label>
    <input type="text" autocomplete="off" id="payfort_fort_card_holder_name">
  </p>

  <p>
    <label>{l s='Expiration (MM/AAAA)'}</label>
    <select id="payfort_fort_expiry_month" >
      {foreach from=$months item=month}
        <option value="{$month}">{$month}</option>
      {/foreach}
    </select>
    <span> / </span>
    <select id="payfort_fort_expiry_year">
      {foreach from=$years item=year}
        <option value="{$year|substr:2}">{$year}</option>
      {/foreach}
    </select>
  </p>
  <p>
    <label>{l s='CVC'}</label>
    <input type="text" size="4" autocomplete="off" id="payfort_fort_card_security_code">
  </p>
    <input type='hidden' id="baseDir" value="{$urls.base_url}">
</form>
