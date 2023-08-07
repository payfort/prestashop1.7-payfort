# Amazon Payment Services plugin for Prestashop
<a href="https://paymentservices.amazon.com/" target="_blank">Amazon Payment Services</a> plugin offers seamless payments for Prestashop platform merchants.  If you don't have an APS account click [here](https://paymentservices.amazon.com/) to sign up for Amazon Payment Services account.


## Getting Started
We know that payment processing is critical to your business. With this plugin we aim to increase your payment processing capabilities. Do you have a business-critical questions? View our quick reference [documentation](https://paymentservices.amazon.com/docs/EN/index.html) for key insights covering payment acceptance, integration, and reporting.


## Configuration and User Guide
You can download the archive [file](/prestashop-aps.zip) of the plugin and easily install it via Prestashop admin screen.
Prestashop Plugin user guide is included in the repository [here](https://github.com/payfort/prestashop1.7-payfort/wiki) 

# Installation
## Admin Panel
1. Login to [Admin Panel] of PrestaShop website 
1. Navigate to Modules-> Modules Manager.   
1. If prestashop version is < 1.7, navigate to Modules and Services -> Modules and Services. 
    > > Click on “Upload a module” and choose the module zip file.  
1. If prestashop version is < 1.7. 
    > > Click “Add a new module” and choose the module zip file and upload the module. 
1. - Navigate to Modules-> Modules Manager. If prestashop version is < 1.7 then Navigate to Modules and Services -> Modules and Services. 
1. - Choose “Amazon Payment Services” payment method 
1. - Click the Install icon 
1. - Follow the configuration steps mentioned in Step 3 

# SFTP
1. Connect via SFTP and navigate to [your site root folder/modules] 
1. Copy PrestaShop APS module folder under modules folder 
1. Navigate to Modules-> Modules Manager.  If prestashop version is < 1.7 then Navigate to Modules and Services -> Modules and Services. 
1. Choose “Amazon Payment Services” payment method 
1. Click the Install icon 
1. Follow the configuration steps mentioned in Step 3 
# Configuration

Follow the below instruction to access configuration page of APS PrestaShop module:  

1. Navigate to Modules-> Modules Manager. If prestashop version is < 1.7 then Navigate to Modules and Services -> Modules and Services. 
1. Choose “Amazon Payment Services” payment method 
1. Click on configuration 
1. On the configuration page, update the configuration and save. 

**Amazon Payment Services Account:** 

If you don't have an APS account click here to sign up for Amazon Payment Services account  https://paymentservices.amazon.com/ 
   

## Payment Options

* Integration Types
   * Redirection
   * Merchant Page
   * Hosted Merchant Page
   * Installments
   * Embedded Hosted Installments

* Payment methods
   * Mastercard
   * VISA
   * American Express
   * VISA Checkout
   * valU
   * mada
   * Meeza
   * KNET
   * NAPS
   * Apple Pay
   

## Changelog

| Plugin Version | Release Notes |
| :---: | :--- |
| 2.1.0 |   * valU changes: downpayment, ToU and Cashback amounts are included in checkout page | 
| 2.0.0 |   * Integrated payment options: MasterCard, Visa, AMEX, mada, Meeza, KNET, NAPS, Visa Checkout, ApplePay, valU <br/> * Tokenization is enabled for Debit/Credit Cards and Installments <br/> * ApplePay is activated in Product and Cart pages <br/> * Installments are embedded in Debit/Credit Card payment option <br/> * Partial/Full Refund, Single/Multiple Capture and Void events are manage in Prestashop order management screen | 


## API Documentation
This plugin has been implemented by using following [API library](https://paymentservices-reference.payfort.com/docs/api/build/index.html)


## Further Questions
Have any questions? Just get in [touch](https://paymentservices.amazon.com/get-in-touch)

## License
Released under the [MIT License](/LICENSE).
