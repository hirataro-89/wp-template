<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use Plugin_Upgrader;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Views\EducationElements;
use Exception;
use Duplicator\Libs\OneClickUpgrade\UpgraderSkin;
use DUP_Log;
use DUP_Settings;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapUtil;

class ServicesEducation extends AbstractAjaxService
{
    const OPTION_KEY_ONE_CLICK_UPGRADE_OTH = 'duplicator_one_click_upgrade_oth';
    const LICENSE_KEY_OPTION_AUTO_ACTIVE   = 'duplicator_pro_license_auto_active';
    const DUPLICATOR_STORE_URL             = "https://duplicator.com";
    const REMOTE_SUBSCRIBE_URL             = 'https://duplicator.com/?lite_email_signup=1';

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_settings_callout_cta_dismiss', 'dismissCalloutCTA');
        $this->addAjaxCall('wp_ajax_duplicator_packages_bottom_bar_dismiss', 'dismissBottomBar');
        $this->addAjaxCall('wp_ajax_duplicator_email_subscribe', 'setEmailSubscribed');
        $this->addAjaxCall('wp_ajax_duplicator_one_click_upgrade_prepare', 'prepareForOneClickUpgrade');
        $this->addAjaxCall('wp_ajax_duplicator_finalize_oneclick_upr', 'finalizeOneClickUpgrade');
        $this->addAjaxCall('wp_ajax_nopriv_duplicator_lite_run_one_click_upgrade', 'oneClickUpgrade');
        $this->addAjaxCall('wp_ajax_duplicator_lite_run_one_click_upgrade', 'oneClickUpgrade');
        $this->addAjaxCall('wp_ajax_duplicator_enable_usage_stats', 'enableUsageStats');
    }

    /**
     * Set email subscribed
     *
     * @return bool
     */
    public static function setEmailSubscribedCallback()
    {
        if (EducationElements::userIsSubscribed()) {
            return true;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        if (is_null($email)) {
            throw new \Exception('Invalid email');
        }

        $response = wp_remote_post(self::REMOTE_SUBSCRIBE_URL, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'body'        => array('email' => $email)
        ));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $error_msg = $response->get_error_code() . ': ' . $response->get_error_message();
            SnapUtil::errorLog($error_msg);
            throw new \Exception($error_msg);
        }

        return (update_user_meta(get_current_user_id(), EducationElements::DUP_EMAIL_SUBSCRIBED_OPT_KEY, true) !== false);
    }

    /**
     * Set recovery action
     *
     * @return void
     */
    public function setEmailSubscribed()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'setEmailSubscribedCallback'),
            'duplicator_email_subscribe',
            $_POST['nonce'],
            'export'
        );
    }

    /**
     * Set dismiss callout CTA callback
     *
     * @return bool
     */
    public static function dismissCalloutCTACallback()
    {
        return (update_user_meta(get_current_user_id(), EducationElements::DUP_SETTINGS_FOOTER_CALLOUT_DISMISSED, true) !== false);
    }

    /**
     * Dismiss callout CTA
     *
     * @return void
     */
    public function dismissCalloutCTA()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissCalloutCTACallback'),
            'duplicator_settings_callout_cta_dismiss',
            $_POST['nonce'],
            'export'
        );
    }

    /**
     * Dismiss bottom bar callback
     *
     * @return bool
     */
    public static function dismissBottomBarCallback()
    {
        return (update_user_meta(get_current_user_id(), EducationElements::DUP_PACKAGES_BOTTOM_BAR_DISMISSED, true) !== false);
    }

    /**
     * Dismiss bottom bar
     *
     * @return void
     */
    public function dismissBottomBar()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissBottomBarCallback'),
            'duplicator_packages_bottom_bar_dismiss',
            $_POST['nonce'],
            'export'
        );
    }


    /**
     * Generate oth and save it into a db option, save also license key into a db option.
     * Returns all values necessary for creating a request to remote endpoint
     *
     * @throws Exception In case it failed to save data into db
     * @return array     Contains:
     */
    public static function prepareForOneClickUpgradeCallback()
    {
        $oth        = wp_generate_password(30, false, false); // Generate random oth
        $licenseKey = sanitize_text_field($_REQUEST["license_key"]);

        delete_option(self::OPTION_KEY_ONE_CLICK_UPGRADE_OTH);
        delete_option(self::LICENSE_KEY_OPTION_AUTO_ACTIVE);
        $ok1 = update_option(self::OPTION_KEY_ONE_CLICK_UPGRADE_OTH, $oth);
        $ok2 = update_option(self::LICENSE_KEY_OPTION_AUTO_ACTIVE, $licenseKey);

        if (!$ok1 || !$ok2) {
            throw new Exception("Problem saving new parameters into options table in prepareForOneClickUpgradeCallback.");
        }
        $returnData = array();

        $returnData["success"]   = true;
        $returnData["error_msg"] = "";

        $returnData["oth"]         = self::hashOth($oth);
        $returnData["license_key"] = $licenseKey;
        $returnData["version"]     = DUPLICATOR_VERSION;
        $returnData["redirect"]    = base64_encode(admin_url('admin-ajax.php?action=duplicator_finalize_oneclick_upr'));
        $returnData["endpoint"]    = admin_url('admin-ajax.php');
        $returnData["siteurl"]     = admin_url();
        $returnData["homeurl"]     = home_url();

        $returnData["response"] = ""; // Store response
        $returnData["file"]     = "";

        // Activate license for this home_url. We can't use download link
        // without first activating license.
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $licenseKey,
            'item_name'  => "Duplicator Pro", // the name of our product in EDD,
            'url'        => home_url()
        );

        global $wp_version;
        $agent_string = "WordPress/" . $wp_version;
        DUP_Log::trace("Wordpress agent string $agent_string");

        $requestParam = array(
            'timeout'    => 15,
            'sslverify'  => true,
            'user-agent' => $agent_string,
            'body'       => $api_params
        );

        if (($data = self::licenseUpgradeRequests($requestParam, $requestError)) === false) {
            throw new Exception($requestError['message']);
        } elseif (!isset($data->license) || $data->license != 'valid') {
            throw new Exception(__('Invalid license key.', 'duplicator'));
        }

        // Fetch direct url and set $returnData["file"]. It is a direct url to download the upgrade version.
        // It can be used only after license is activated for the given website.
        $api_params = array(
            'edd_action'  => 'get_version',
            'license'     => $licenseKey,
            'item_name'   => "Duplicator Pro",
            'slug'        => "duplicator-pro",
            'author'      => "Snap Creek Software",
            'url'         => home_url(),
            'beta'        => false,
            'php_version' => phpversion(),
            'wp_version'  => get_bloginfo('version'),
        );

        $requestParam = array(
            'timeout'   => 15,
            'sslverify' => true,
            'body'      => $api_params,
        );

        if (($data = self::licenseUpgradeRequests($requestParam, $requestError)) === false) {
            throw new Exception($requestError['message']);
        } elseif (!isset($data->download_link) || empty($data->download_link)) {
            throw new Exception('License key download URL is invalid');
        }

        $returnData["file"] = base64_encode($data->download_link);
        return $returnData;
    }

    /**
     * Returh hashed OTH
     *
     * @param string $oth OTH
     *
     * @return string Hashed OTH
     */
    protected static function hashOth($oth)
    {
        return  hash_hmac('sha512', $oth, wp_salt());
    }

    /**
     * License upgrade requests
     *
     * @param array $params       Parameters
     * @param array $requestError Request error
     *
     * @return false|object False on error, array data on success
     */
    protected static function licenseUpgradeRequests($params, &$requestError = array())
    {
        try {
            $requestError = array(
                'code'    => -1,
                'message' => '',
                'details' => '',
            );

            $response = wp_remote_post(self::DUPLICATOR_STORE_URL, $params);
            if (is_wp_error($response)) {
                /** @var WP_Error  $response */
                $requestError['code']    = $response->get_error_code();
                $requestError['message'] = $response->get_error_message();
                $requestError['details'] = SnapJson::jsonEncodePPrint($response->get_error_data());
            } elseif ($response['response']['code'] < 200 || $response['response']['code'] >= 300) {
                $requestError['code']    = $response['response']['code'];
                $requestError['message'] = $response['response']['message'];
                $requestError['details'] = SnapJson::jsonEncodePPrint($response);
            } else {
                $data = json_decode(wp_remote_retrieve_body($response));
                if (!is_object($data)) {
                    $requestError['code']    = -1;
                    $requestError['message'] = __('Invalid license JSON data.', 'duplicator');
                    $requestError['details'] = 'Response: ' . wp_remote_retrieve_body($response);
                } else {
                    return $data;
                }
            }
        } catch (Exception $e) {
            $requestError['code']    = -1;
            $requestError['message'] = 'Exception ' . $e->getMessage();
            $requestError['details'] = $e->getTraceAsString();
        }

        return false;
    }

    /**
     * Enable usage stats
     *
     * @return void
     */
    public function enableUsageStats()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'enableUsageStatsCallback'),
            'duplicator_enable_usage_stats',
            SnapUtil::sanitizeTextInput(INPUT_POST, 'nonce'),
            'manage_options'
        );
    }

    /**
     * Enable usage stats callback
     *
     * @return void
     */
    public static function enableUsageStatsCallback()
    {
        $result = true;
        if (DUP_Settings::Get('usage_tracking') !== true) {
            DUP_Settings::setUsageTracking(true);
            $result = DUP_Settings::Save();
        }

        return $result && self::setEmailSubscribedCallback();
    }

    /**
     * Prepare for One Click Upgrade from Lite to Pro
     *
     * @return void
     */
    public function prepareForOneClickUpgrade()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'prepareForOneClickUpgradeCallback'),
            'duplicator_one_click_upgrade_prepare',
            $_POST['nonce'],
            'export'
        );
    }

    /**
     * Accepts oth and file from remote endpoint, installs Pro if it's not already installed.
     * Prints json with error=true or success=true.
     *
     * @return void
     */
    public function oneClickUpgrade()
    {
        $othReceived = sanitize_text_field($_REQUEST["oth"]);
        $oth         = get_option(self::OPTION_KEY_ONE_CLICK_UPGRADE_OTH);
        if (self::hashOth($oth) !== $othReceived) { // Verify that oth is fine
            DUP_Log::trace("ERROR: Wrong oth token.");
            wp_send_json_error(array('message' => 'Invalid security token'));
        }

        // Verify upgrade version is not installed
        if (!is_dir(WP_PLUGIN_DIR . "/duplicator-pro")) {
            DUP_Log::trace("There is no upgrade version in plugins directory, we need to download it!");

            // Prepare variables.
            $url = esc_url_raw(
                add_query_arg(
                    array( 'page' => 'duplicator-settings' ),
                    admin_url('admin.php')
                )
            );

            $creds = request_filesystem_credentials($url, '', false, false, null);

            // Check for file system permissions.
            if (false === $creds || ! \WP_Filesystem($creds)) {
                wp_send_json_error(array('message' => 'File system permissions error. Please check permissions and try again.'));
            }
            // We do not need any extra credentials if we have gotten this far, so let's install the plugin.
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

            // Do not allow WordPress to search/download translations, as this will break JS output.
            remove_action('upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20);

            // Get license key and fetch download URL
            $license_key = get_option(self::LICENSE_KEY_OPTION_AUTO_ACTIVE);
            if (empty($license_key)) {
                DUP_Log::trace("ERROR: No license key found for upgrade.");
                wp_send_json_error(array('message' => 'No license key found for upgrade'));
            }

            // Get download URL from license API
            $api_params = array(
                'edd_action'  => 'get_version',
                'license'     => $license_key,
                'item_name'   => "Duplicator Pro",
                'slug'        => "duplicator-pro",
                'author'      => "Snap Creek Software",
                'url'         => home_url(),
                'beta'        => false,
                'php_version' => phpversion(),
                'wp_version'  => get_bloginfo('version'),
            );

            global $wp_version;
            $agent_string = "WordPress/" . $wp_version;

            $requestParam = array(
                'timeout'    => 15,
                'sslverify'  => true,
                'user-agent' => $agent_string,
                'body'       => $api_params
            );

            if (($data = self::licenseUpgradeRequests($requestParam, $requestError)) === false) {
                DUP_Log::trace("ERROR: Failed to get download URL: " . $requestError['message']);
                wp_send_json_error(array('message' => 'Failed to get download URL: ' . $requestError['message']));
            }

            if (empty($data->download_link)) {
                DUP_Log::trace("ERROR: No download link returned from license API.");
                wp_send_json_error(array('message' => 'No download link returned from license API'));
            }

            $download_url = $data->download_link;

            // Create the plugin upgrader with custom skin.
            $installer = new Plugin_Upgrader(new UpgraderSkin());
            $installer->install($download_url); // Use validated download URL from license API

            // Flush the cache and return the newly installed plugin basename.
            wp_cache_flush();
            $plugin_basename = $installer->plugin_info();

            if ($plugin_basename) {
                $upgradeDir = dirname($plugin_basename);
                if (
                    $upgradeDir != "duplicator-pro" &&
                    !rename(WP_PLUGIN_DIR . "/" . $upgradeDir, WP_PLUGIN_DIR . "/duplicator-pro")
                ) {
                    wp_send_json_error(array('message' => 'Failed renaming plugin directory'));
                }
            } else {
                wp_send_json_error(array('message' => 'Installation of upgrade version failed'));
            }
        }

        wp_send_json_success(array('message' => 'Plugin exists and is ready to be activated'));
    }

    /**
     * Checks if duplicator-pro exists. If it does not, then it redirects to Lite settings license page.
     * Otherwise deactivates Lite and redirects to upgrade plugin activation link.
     *
     * @return void
     */
    public function finalizeOneClickUpgrade()
    {
        $othReceived = sanitize_text_field($_REQUEST["oth"]);
        $oth         = get_option(self::OPTION_KEY_ONE_CLICK_UPGRADE_OTH);
        $newFolder   = WP_PLUGIN_DIR . "/duplicator-pro";

        if (!current_user_can('activate_plugins') || (self::hashOth($oth) !== $othReceived)) {
            $duplicatorPage = ControllersManager::getMenuLink(
                ControllersManager::PACKAGES_SUBMENU_SLUG,
                null,
                null,
                ['action' => 'upgrade_finalize_fail']
            );
            wp_safe_redirect($duplicatorPage);
            exit;
        }
        delete_option(self::OPTION_KEY_ONE_CLICK_UPGRADE_OTH);

        if (!is_dir($newFolder)) {
            $licensePageUrl = ControllersManager::getMenuLink(
                ControllersManager::SETTINGS_SUBMENU_SLUG,
                'general'
            );
            header("Location: $licensePageUrl");
            die();
        }
        deactivate_plugins(DUPLICATOR_PLUGIN_PATH . "/duplicator.php");

        $plugin          = "duplicator-pro/duplicator-pro.php";
        $pluginsAdminUrl = is_multisite() ? network_admin_url('plugins.php') : admin_url('plugins.php');
        $activateProUrl  = esc_url_raw(
            add_query_arg(
                array(
                    'action' => 'activate',
                    'plugin' => $plugin,
                    '_wpnonce' => wp_create_nonce("activate-plugin_$plugin")
                ),
                $pluginsAdminUrl
            )
        );
        header("Location: $activateProUrl");
        die();
        // $activated = activate_plugin($plugin, '', true, true); // This won't work
    }
}
