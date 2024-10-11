<?php

namespace App\Config;


class CoreUpdateConfig
{
    public static $plugin_page_title = "Core Updater";
    public static $plugin_menu_title = "Core Updater";
    public static $plugin_capability = "manage_options";
    public static $plugin_menu_slug = "redev-wpcore-updater";
    public static $plugin_icon = "dashicons-admin-plugins";

    public static $endpoint_site = "https://websiteredev.com";
    public static $endpoint_slug = "/wp-downloads/version_data.json";

    public static $files_to_update = [
        'wp-includes',
        'wp-admin',
        'wp-settings.php',
        'wp-login.php',
        'wp-load.php',
        'xmlrpc.php',
        'wp-activate.php',
        'wp-mail.php',
        'wp-trackback.php',
        'wp-signup.php',
        'wp-comments-post.php',
        'wp-cron.php',
        'wp-links-opml.php',
        'index.php',
        'wp-blog-header.php',
    ];


    const API_VERSION = 'v1';
    const API_TIMEOUT = 30; // response timeout

    public static function getEndpoint()
    {
        return self::$endpoint_site . self::$endpoint_slug;
    }

    // Example of non-static method using static properties
    public function getCustomEndpoint($custom_slug)
    {
        return self::$endpoint_site . $custom_slug;
    }
}
