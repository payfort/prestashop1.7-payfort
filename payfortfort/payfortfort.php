<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if ( ! defined( '_PS_VERSION_' ) ) 
	exit;

include_once dirname(__FILE__) . '/lib/payfortFort/init.php';

class Payfortfort extends PaymentModule 
{
    
    private         $html           = '';
    protected       $statuses_array = array();
    
    public function __construct() {
        $this->name                      = 'payfortfort';
        $this->tab                       = 'payments_gateways';
        $this->version                   = '1.0.1';
        $this->author                    = 'Payfort';
        $this->controllers               = array('payment');
        $this->author_uri                = 'https://github.com/payfort/prestashop1.7-payfort';
        $this->fort_available_currencies = array('USD', 'AUD', 'CAD', 'EUR', 'GBP', 'NZD', 'SAR', 'JOD', 'QAR', 'AED');
        parent::__construct();

        $this->displayName               = 'Payfort FORT Gateway';
        $this->description               = $this->l('Receive payment with Credit/Debit cards');

        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        /* Check if cURL is enabled */
        if (!is_callable('curl_exec'))
            $this->warning = $this->l('CURL extension must be enabled on your server to use this module.');

    }

    public function install() {

        // check if the order status is defined
        if (!defined('PS_OS_PAYFORT_PENDING')) {

            // order status is not defined - check if, it exists in the table
            $rq = Db::getInstance()->getRow('
            SELECT `id_order_state` FROM `' . _DB_PREFIX_ . 'order_state_lang`
            WHERE id_lang = \'' . pSQL('1') . '\' AND  name = \'' . pSQL('Pending Payfort Payment') . '\'');

            if ($rq && isset($rq['id_order_state']) && intval($rq['id_order_state']) > 0) {

                // order status exists in the table - define it.
                define('PS_OS_PAYFORT_PENDING', $rq['id_order_state']);
            }
            else {

                // order status doesn't exist in the table
                // insert it into the table and then define it.
                Db::getInstance()->Execute('
                INSERT INTO `' . _DB_PREFIX_ . 'order_state` (`unremovable`, `color`) VALUES(1, \'orange\')');

                $stateid = Db::getInstance()->Insert_ID();

                Db::getInstance()->Execute('INSERT INTO `' . _DB_PREFIX_ . 'order_state_lang` (`id_order_state`, `id_lang`, `name`, `template`)
                VALUES(' . intval($stateid) . ', 1, \'Pending Payfort Payment\', \'\')');

                define('PS_OS_PAYFORT_PENDING', $stateid);
            }
        }

        return parent::install() &&
                $this->registerHook('paymentOptions') &&
                $this->registerHook('paymentReturn') &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1) &&
                Configuration::updateValue('PAYFORT_FORT_MADA_BRANDING', 1) &&                
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en') &&
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION') &&
                Configuration::updateValue('PAYFORT_HASH_ALGORITHM', 'SHA1') &&
                Configuration::updateValue('PAYFORT_FORT_HOLD_REVIEW_OS', _PS_OS_PAYMENT_) &&
                Configuration::updateValue('PS_OS_PAYFORT_PENDING', PS_OS_PAYFORT_PENDING) &&
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', 'redirection') &&
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 0) &&
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', 'base');
    }

    public function uninstall() {
        
        Configuration::deleteByName('PAYFORT_FORT_SANDBOX_MODE');
        Configuration::deleteByName('PAYFORT_FORT_MADA_BRANDING');        
        Configuration::deleteByName('PAYFORT_FORT_LANGUAGE');
        Configuration::deleteByName('PAYFORT_FORT_MERCHANT_IDENTIFIER');
        Configuration::deleteByName('PAYFORT_FORT_ACCESS_CODE');
        Configuration::deleteByName('PAYFORT_FORT_COMMAND');
        Configuration::deleteByName('PAYFORT_FORTSHA_ALGORITHM');
        Configuration::deleteByName('PAYFORT_FORT_REQUEST_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_FORTRESPONSE_SHA_PHRASE');
        Configuration::deleteByName('PAYFORT_FORT_HOLD_REVIEW_OS');
        Configuration::deleteByName('PAYFORT_FORT_INTEGRATION_TYPE');
        Configuration::deleteByName('PAYFORT_FORT_GATEWAY_CURRENCY');
        Configuration::deleteByName('PAYFORT_FORT_DEBUG_MODE');

        /* Removing credentials configuration variables */
        $currencies = Currency::getCurrencies(false, true);
        foreach ($currencies as $currency) {
            if (in_array($currency['iso_code'], $this->fort_available_currencies)) {
                Configuration::deleteByName('PAYFORT_FORT_LOGIN_ID_' . $currency['iso_code']);
                Configuration::deleteByName('PAYFORT_FORT_KEY_' . $currency['iso_code']);
            }
        }
        
        return parent::uninstall();
    }

    public function hookPaymentReturn($params) {
        
        if (!$this->active) {
            return;
        }
        
        if ($params['order']->module != $this->name)
            return;
        
        $successStatus = Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS');
        $declineStatus = Configuration::get('PS_OS_ERROR') ? Configuration::get('PS_OS_ERROR') : _PS_OS_ERROR_;
        $cancelStatus = Configuration::get('PS_OS_CANCELED') ? Configuration::get('PS_OS_CANCELED') : _PS_OS_CANCELED_;
        $state = $params['order']->getCurrentState();
        if ($state == $successStatus) {
            Configuration::updateValue('PAYFORTFORT_CONFIGURATION_OK', true);
            $this->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['order']->id)));
        }
        elseif($state == $cancelStatus) {
            $this->smarty->assign('status', 'cancelled');
        }
        else {
            $this->smarty->assign('status', 'failed');
        }

        $this->smarty->assign('order_status', $state);
        return $this->fetch('module:payfortfort/views/templates/hook/orderconfirmation.tpl');
    }
    


    public function hookBackOfficeHeader() {
        
        $this->context->controller->addJQuery();
        $this->context->controller->addJqueryPlugin('fancybox');
    }

    public function getContent() {
        
        $this->html = '';
        if (Tools::isSubmit('submitModule')) {
            $payfort_sandbox_mode = (int) Tools::getvalue('payfort_sandbox_mode');
            if ($payfort_sandbox_mode == 1) {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_SANDBOX_MODE', 0);
            }
            $payfort_installments = (int) Tools::getvalue('payfort_installments');
            if ($payfort_installments == 1) {
                Configuration::updateValue('PAYFORT_FORT_INSTALLMENTS', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INSTALLMENTS', 0);
            }
            $payfort_sadad = (int) Tools::getvalue('payfort_sadad');
            if ($payfort_sadad == 1) {
                Configuration::updateValue('PAYFORT_FORT_SADAD', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_SADAD', 0);
            }
            $payfort_naps = (int) Tools::getvalue('payfort_naps');
            if ($payfort_naps == 1) {
                Configuration::updateValue('PAYFORT_FORT_NAPS', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_NAPS', 0);
            }
            $payfort_credit_card = (int) Tools::getvalue('payfort_credit_card');
            if ($payfort_credit_card == 1) {
                Configuration::updateValue('PAYFORT_FORT_CREDIT_CARD', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_CREDIT_CARD', 0);
            }
            $payfort_integration_type = Tools::getvalue('payfort_integration_type');
            if (empty($payfort_integration_type)) {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', 'redirection');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', $payfort_integration_type);
            }
            
            
            
            $payfort_mada_branding = (int) Tools::getvalue('payfort_mada_branding');
            if ($payfort_mada_branding == 1) {
                Configuration::updateValue('PAYFORT_FORT_MADA_BRANDING', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_MADA_BRANDING', 0);
            }
            
            
            
            $payfort_integration_type_installments = Tools::getvalue('payfort_integration_type_installments');
            if (empty($payfort_integration_type_installments)) {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS', 'redirection');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS', $payfort_integration_type_INSTALLMENTS);
            }


            $payfort_language = Tools::getvalue('payfort_language');
            if ($payfort_language == 'ar') {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'ar');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_LANGUAGE', 'en');
            }
            $payfort_fort_command = Tools::getvalue('payfort_fort_command');
            if ($payfort_fort_command == 'AUTHORIZATION') {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'AUTHORIZATION');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_COMMAND', 'PURCHASE');
            }
            $payfort_fort_sha_algorithm = Tools::getvalue('payfort_fort_sha_algorithm');
            if (empty($payfort_fort_sha_algorithm)) {
                Configuration::updateValue('PAYFORT_FORT_SHA_ALGORITHM', 'SHA1');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', $payfort_fort_sha_algorithm);
            }

            $payfort_fort_gateway_currency = Tools::getvalue('payfort_fort_gateway_currency');
            if (empty($payfort_fort_gateway_currency)) {
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', 'base');
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', $payfort_fort_gateway_currency);
            }

            $payfort_fort_debug_mode = (int) Tools::getvalue('payfort_fort_debug_mode');
            if ($payfort_fort_debug_mode == 1) {
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 1);
            }
            else {
                Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 0);
            }

            foreach ($_POST as $key => $value) {
                if ($key != "tab" && $key != "submitModule") {
                    Configuration::updateValue(strtoupper($key), $value);
                }
            }
            
            $this->html .= $this->displayConfirmation($this->l('Configuration updated'));
        }
        // For "Hold for Review" order status
        $order_states = OrderState::getOrderStates((int) $this->context->cookie->id_lang);
        $this->smarty->assign(array(
            'available_currencies'                           => $this->fort_available_currencies,
            'module_dir'                                     => $this->_path,
            'order_states'                                   => $order_states,
            'PAYFORT_FORT_SANDBOX_MODE'                      => Configuration::get('PAYFORT_FORT_SANDBOX_MODE'),
            'PAYFORT_FORT_MADA_BRANDING'                     => Configuration::get('PAYFORT_FORT_MADA_BRANDING'),
            'PAYFORT_FORT_INSTALLMENTS'                      => Configuration::get('PAYFORT_FORT_INSTALLMENTS'),
            'PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS'     => Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS'),
            'PAYFORT_FORT_SADAD'                             => Configuration::get('PAYFORT_FORT_SADAD'),
            'PAYFORT_FORT_NAPS'                              => Configuration::get('PAYFORT_FORT_NAPS'),
            'PAYFORT_FORT_CREDIT_CARD'                       => Configuration::get('PAYFORT_FORT_CREDIT_CARD'),
            'PAYFORT_FORT_INTEGRATION_TYPE'                  => Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE'),
            'PAYFORT_FORT_HOLD_REVIEW_OS'                    => (int) Configuration::get('PAYFORT_FORT_HOLD_REVIEW_OS'),
            'PAYFORT_FORT_COMMAND'                           => Configuration::get('PAYFORT_FORT_COMMAND'),
            'PAYFORT_FORT_LANGUAGE'                          => Configuration::get('PAYFORT_FORT_LANGUAGE'),
            'PAYFORT_FORT_SHA_ALGORITHM'                     => Configuration::get('PAYFORT_FORT_SHA_ALGORITHM'),
            'PAYFORT_FORT_GATEWAY_CURRENCY'                  => Configuration::get('PAYFORT_FORT_GATEWAY_CURRENCY'),
            'PAYFORT_FORT_DEBUG_MODE'                        => Configuration::get('PAYFORT_FORT_DEBUG_MODE'),
        ));

        $configuration_merchant_identifier = 'PAYFORT_FORT_MERCHANT_IDENTIFIER';
        $configuration_access_code         = 'PAYFORT_FORT_ACCESS_CODE';
        $configuration_request_sha_phrase  = 'PAYFORT_FORT_REQUEST_SHA_PHRASE';
        $configuration_response_sha_phrase = 'PAYFORT_FORT_RESPONSE_SHA_PHRASE';
        $configuration_sha_algorithm       = 'PAYFORT_FORT_SHA_ALGORITHM';

        $this->smarty->assign($configuration_merchant_identifier, Configuration::get($configuration_merchant_identifier));
        $this->smarty->assign($configuration_access_code, Configuration::get($configuration_access_code));
        $this->smarty->assign($configuration_request_sha_phrase, Configuration::get($configuration_request_sha_phrase));
        $this->smarty->assign($configuration_response_sha_phrase, Configuration::get($configuration_response_sha_phrase));
        $this->smarty->assign($configuration_sha_algorithm, Configuration::get($configuration_sha_algorithm));
        $this->smarty->assign('host_to_host_url', $this->_getUrl('fc=module&module=payfortfort&controller=payment&action=processPaymentResponse'));
        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    public function hookPaymentOptions($params) {
        
        if (!$this->active) {
            return;
        }
        $currency         = Currency::getCurrencyInstance($this->context->cookie->id_currency);
        $isFailed         = Tools::getValue('payfortforterror');

        $url              = $this->_getUrl('fc=module&module=payfortfort&controller=payment&action=postPaymentForm');
        $installments     = Configuration::get('PAYFORT_FORT_INSTALLMENTS');
        $SADAD            = Configuration::get('PAYFORT_FORT_SADAD');
        $NAPS             = Configuration::get('PAYFORT_FORT_NAPS');
        $credit_card      = Configuration::get('PAYFORT_FORT_CREDIT_CARD');
        $integration_type = Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE');
        $mada_branding    = Configuration::get('PAYFORT_FORT_MADA_BRANDING');
        $integration_type_installments = Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE_INSTALLMENTS');

        $pfHelper = Payfort_Fort_Helper::getInstance();
        $frontCurrency = $pfHelper->getFrontCurrency();
        $baseCurrency  = $pfHelper->getBaseCurrency();
        $fortCurrency  = $pfHelper->getFortCurrency($baseCurrency, $frontCurrency);
        if ($fortCurrency != 'SAR') {
            $SADAD = 0;
        }
        if ($fortCurrency != 'QAR') {
            $NAPS = 0;
        }
        if ($fortCurrency != 'SAR') {
            $mada_branding = 0;
        }

        $this->smarty->assign('url', $url);
        $this->smarty->assign('SADAD', $SADAD);
        $this->smarty->assign('NAPS', $NAPS);
        $this->smarty->assign('credit_card', $credit_card);
        $this->smarty->assign('installments', $installments);
        $this->smarty->assign('integration_type', $integration_type);
        $this->smarty->assign('mada_branding', $mada_branding);        
        $this->smarty->assign('integration_type_installments', $integration_type_installments);
        $this->smarty->assign('payfort_path', $this->getPathUri());
        
        $arr_js_messages = 
                    array(
                        'error_invalid_card_number' => $this->l('error_invalid_card_number'),
                        'error_invalid_card_holder_name' => $this->l('error_invalid_card_holder_name'),
                        'error_invalid_expiry_date' => $this->l('error_invalid_expiry_date'),
                        'error_invalid_cvc_code' => $this->l('error_invalid_cvc_code'),
                        'error_invalid_cc_details' => $this->l('error_invalid_cc_details'),
                    );
        $js_msgs = $pfHelper->loadJsMessages($arr_js_messages);
        $this->smarty->assign('arr_js_messages', json_decode($js_msgs));
        
        $payment_options = array();
        
        if($credit_card) {
            $payment_options[] = $this->getCreditCardPaymentOption();
        }
        if($installments) {
            $payment_options[] = $this->getInstallmentsPaymentOption();
        }
        if($SADAD) {
            $payment_options[] = $this->getSadadPaymentOption();
        }
        if($NAPS) {
            $payment_options[] = $this->getNapsPaymentOption();
        }

	return $payment_options;
    }
    
    public function getCreditCardPaymentOption() {
        $pfHelper = Payfort_Fort_Helper::getInstance();
        $frontCurrency = $pfHelper->getFrontCurrency();
        $baseCurrency  = $pfHelper->getBaseCurrency();
        $fortCurrency  = $pfHelper->getFortCurrency($baseCurrency, $frontCurrency);
        
        
        $integration_type = Configuration::get('PAYFORT_FORT_INTEGRATION_TYPE');
        
        if($integration_type == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE) {
        $creditCardOption = new PaymentOption();
        $creditCardOption->setCallToActionText($this->l('Pay With Debit / Cradit Card'))
                       ->setForm($this->generateIframeForm())
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
        } else if($integration_type == PAYFORT_FORT_INTEGRATION_TYPE_MERCAHNT_PAGE2) {
        $mada_branding    = Configuration::get('PAYFORT_FORT_MADA_BRANDING');
        if ($fortCurrency != 'SAR') {
            $mada_branding = 0;
        }
        $creditCardOption = new PaymentOption();
            if ($mada_branding){ 
             $creditCardOption->setCallToActionText($this->l('Pay With Credit / Debit / mada Bank Card'))
                       ->setForm($this->generateForm())
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
            }
            else {
                $creditCardOption->setCallToActionText($this->l('Pay With Debit / Cradit Card'))
                ->setForm($this->generateForm())
                ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
            }
            
        } else {
        $creditCardOption = new PaymentOption();
        $creditCardOption->setCallToActionText($this->l('Pay With Debit / Cradit Card'))
                       ->setAction($this->context->link->getModuleLink($this->name, 'payment', array('action'=>'postPaymentForm'), true))
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
        }

        return $creditCardOption;
    }
    
    protected function generateIframeForm() {
         
        $this->smarty->assign([
            'url' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
        ]);

        return $this->fetch('module:payfortfort/views/templates/hook/payment_payfortfort_iframe.tpl');
        
    }
    
    protected function generateInstallmentsIframe() {
        
        $this->smarty->assign([
            'url' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
        ]);
        return $this->fetch('module:payfortfort/views/templates/hook/payment_payfortfort_installments_iframe.tpl');
    }


    protected function generateForm() {
            
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = sprintf("%02d", $i);
        }

        $years = [];
        for ($i = 0; $i <= 10; $i++) {
            $years[] = date('Y', strtotime('+' . $i . ' years'));
        }

        $this->smarty->assign([
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'months' => $months,
            'years' => $years,
        ]);

        return $this->fetch('module:payfortfort/views/templates/hook/payment_payfortfort.tpl');
    }

    public function getInstallmentsPaymentOption() {
        
        $pfConfig = new Payfort_Fort_Config();
        $integrationType = $pfConfig->getInstallmentsIntegrationType();
        if($integrationType == PAYFORT_FORT_INTEGRATION_TYPE_REDIRECTION) {
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Pay with Installments'))
                       ->setAction($this->context->link->getModuleLink($this->name, 'payment', array('action'=>'postPaymentForm', 'INSTALLMENTS' => 1), true))
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
        } else {
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Pay with Installments'))
                       ->setForm($this->generateInstallmentsIframe())
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));
        }
        return $externalOption;
    }
    
    public function getSadadPaymentOption() {
        
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Pay With SADAD'))
                       ->setAction($this->context->link->getModuleLink($this->name, 'payment', array('action'=>'postPaymentForm', 'SADAD' => 1), true))
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));

        return $externalOption;
    }

    public function getNapsPaymentOption() {
        
        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Pay with NAPS'))
                       ->setAction($this->context->link->getModuleLink($this->name, 'payment', array('action'=>'postPaymentForm', 'NAPS' => 1), true))
                       ->setAdditionalInformation($this->fetch('module:payfortfort/views/templates/hook/payment_infos.tpl'));

        return $externalOption;
    }
    
    public function hookHeader() {
        
        $this->context->controller->registerJavascript('creditValidator', 'modules/' . $this->name . '/js/jquery.creditCardValidator.js');
        $this->context->controller->registerJavascript('payfortFort', 'modules/' . $this->name . '/js/payfort_fort.js');
        $this->context->controller->registerJavascript('checkOut', 'modules/' . $this->name . '/js/checkout.js');
        $this->context->controller->registerStylesheet('fontAwesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css', ['server' => 'remote']);
        $this->context->controller->registerStylesheet('checkOutCss', 'modules/' . $this->name . '/css/checkout.css');
    }

    private function _getUrl($path) {
        
        $url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?' . $path;
        $ssl = Configuration::get('PS_SSL_ENABLED');
        if ($ssl) {
            $url = _PS_BASE_URL_SSL_ . __PS_BASE_URI__ . 'index.php?' . $path;
        }

        return $url;
    }

}

