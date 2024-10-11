<?php

namespace App\Controllers;

use App\Config\CoreUpdateConfig;
use App\Services\CoreUpdateService;
use Timber\Timber as TimberRedev;

class CoreUpdateController
{

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('wp_ajax_ccu_update_core', [$this, 'updateCore']);

        add_filter('automatic_updater_disabled', '__return_true');
        add_filter('pre_site_transient_update_core', '__return_false');
        add_filter('pre_site_transient_update_plugins', '__return_false');
        add_filter('pre_site_transient_update_themes', '__return_false');

        add_action('admin_menu', function() {
            remove_action('admin_notices', 'update_nag', 3);
        });

        add_action('admin_notices', [$this, 'redevUpdateNotice']);
    }

    public function addAdminMenu()
    {
        add_menu_page(
            CoreUpdateConfig::$plugin_page_title,
            CoreUpdateConfig::$plugin_menu_title,
            CoreUpdateConfig::$plugin_capability,
            CoreUpdateConfig::$plugin_menu_slug,
            [$this, 'renderAdminPage'],
            CoreUpdateConfig::$plugin_icon,
        );
    }

    public function renderAdminPage()
    {

        $context = TimberRedev::context();
        $context['nonce'] =  wp_create_nonce('update_core_action');
        $context['current_version'] =  get_bloginfo('version');

        $api_data = (new CoreUpdateService())->getApiData();

        if (isset($api_data['error'])) {
            $context['server_version'] = 'Error fetching version';
            $context['error'] = $api_data['error'];
            $context['button_disabled'] = true; 
        } else {
            $context['server_version'] = $api_data['version'];
            $context['zip_url'] = $api_data['zip_url'];

            if ($context['current_version'] === $context['server_version']) {
                $context['button_disabled'] = true; // Disable the button
                $context['up_to_date_message'] = 'Your WordPress version is not outdated.';
            } else {
                $context['button_disabled'] = false; // Enable the button
            }
        }


        TimberRedev::render('@admin/core-update.twig', $context);

    }

    public function redevUpdateNotice()
    {
        $current_version = get_bloginfo('version');
   
        $api_data = (new CoreUpdateService())->getApiData();

        if (!isset($api_data['error'])) {
            $new_version = $api_data['version']; 
        }

        // Show the notice only if the new version is different
        if (version_compare($current_version, $new_version, '<')) {
            $message = sprintf(
                'WordPress %s is available! <a href="%s">Please update now</a>.',
                $new_version,
                admin_url('admin.php?page='.CoreUpdateConfig::$plugin_menu_slug) 
            );

            echo '<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>';
        }
    }

    public function updateCore()
    {
        $nonce_check = check_ajax_referer('update_core_action', 'nonce', false);

        if (!$nonce_check) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
            return;
        }

        (new CoreUpdateService())->processCoreUpdate();
       
    }

}
