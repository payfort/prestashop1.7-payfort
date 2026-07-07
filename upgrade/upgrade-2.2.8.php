<?php
/**
* 2007-2024 PrestaShop
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
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Security fix: migrate Apple Pay certificates from web-accessible /upload/ directory
 * to non-web-accessible /config/ directory with randomized filenames.
 *
 * Resolves Shepherd risk: d5271002-65bb-4ddf-9f9c-2e77b06cdaf8
 */
function upgrade_module_2_2_8($module)
{
    $old_dir = _PS_UPLOAD_DIR_ . 'aps_certificate/';
    $new_dir = _PS_CONFIG_DIR_ . 'aps_certificate/';

    if (!file_exists($new_dir)) {
        @mkdir($new_dir, 0750, true);
        @chmod($new_dir, 0750);
    }

    // Migrate certificate file
    $old_crt = Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', null);
    if ($old_crt && file_exists($old_dir . $old_crt)) {
        $new_crt_filename = bin2hex(random_bytes(16)) . '.crt.pem';
        if (copy($old_dir . $old_crt, $new_dir . $new_crt_filename)) {
            @chmod($new_dir . $new_crt_filename, 0640);
            @unlink($old_dir . $old_crt);
            Configuration::updateValue('AMAZONPAYMENTSERVICES_APPLE_PAY_CRT_FILE', $new_crt_filename);
        }
    }

    // Migrate key file
    $old_key = Configuration::get('AMAZONPAYMENTSERVICES_APPLE_PAY_KEY_FILE', null);
    if ($old_key && file_exists($old_dir . $old_key)) {
        $new_key_filename = bin2hex(random_bytes(16)) . '.key.pem';
        if (copy($old_dir . $old_key, $new_dir . $new_key_filename)) {
            @chmod($new_dir . $new_key_filename, 0640);
            @unlink($old_dir . $old_key);
            Configuration::updateValue('AMAZONPAYMENTSERVICES_APPLE_PAY_KEY_FILE', $new_key_filename);
        }
    }

    // Remove old directory if empty
    if (file_exists($old_dir)) {
        $remaining = array_diff(scandir($old_dir), ['.', '..', 'index.php']);
        if (empty($remaining)) {
            @unlink($old_dir . 'index.php');
            @rmdir($old_dir);
        }
    }

    return true;
}
