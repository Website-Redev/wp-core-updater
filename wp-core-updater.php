<?php
/*
Plugin Name: WP Core Updater
Plugin URI: https://websiteredev.com/plugin
Description: The WP Core Updater plugin is designed to manage WordPress core updates.
Version: 1.0.3
Author: Jomar Redev
Author URI: https://websiteredev.com
License: GPLv2 or later
Text Domain: websiteredev-wpcore
*/

use App\InitRedevCore;
use Timber\Timber as TimberRedev;

defined('ABSPATH') or die('Contact Redev Permission Denied!');

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}


if (class_exists('App\\InitRedevCore')) {
    InitRedevCore::registerRedevServices();
}

if (class_exists('Timber\\Timber')) {
    TimberRedev::$dirname = array('templates', 'views');
    TimberRedev::$autoescape = false;


    add_filter('timber/loader/loader', function ($loader) {
        $baseTemplatePath = __DIR__ . '/templates';
        $folders = [
            'components',
            'admin'
        ];

        foreach ($folders as $folder) {
            $folderPath = $baseTemplatePath . '/' . $folder;

            if (is_dir($folderPath)) {
                $loader->addPath($folderPath, $folder);
            } else {
                mkdir($folderPath, 0775);
            }
        }

        return $loader;
    });
}


if (!function_exists('activate_redev_wpcore_plugin')) {
    function activate_redev_wpcore_plugin()
    {
        InitRedevCore::activate();
    }
}
register_activation_hook(__FILE__, 'activate_redev_wpcore_plugin');


if (!function_exists('deactivate_redev_wpcore_plugin')) {

    function deactivate_redev_wpcore_plugin()
    {
        InitRedevCore::deactivate();
    }
}
register_deactivation_hook(__FILE__, 'deactivate_redev_wpcore_plugin');
