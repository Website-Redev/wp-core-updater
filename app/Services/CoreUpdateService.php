<?php

namespace App\Services;

use App\Config\CoreUpdateConfig;

class CoreUpdateService
{

    public function getApiData()
    {

        $response = wp_remote_get(CoreUpdateConfig::getEndpoint(), [
            'timeout' => CoreUpdateConfig::API_TIMEOUT
        ]);

        if (is_wp_error($response)) {
            header('Content-Type: application/json');
            return ['error' => 'Unable to reach the API'];
        }

        $body = wp_remote_retrieve_body($response);

        if (!empty($body)) {
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE  || empty($data['version']) || empty($data['zip_url'])) {
                return ['error' => 'Invalid API response'];
            }
            return [
                'version' => $data['version'],
                'zip_url' => (new CoreUpdateConfig)->getCustomEndpoint($data['zip_url'])
            ];
        } else {
            return ['error' => 'Invalid API response'];
        }
    }

    public function processCoreUpdate()
    {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $creds = request_filesystem_credentials('', '', false, false, null);
            if (!WP_Filesystem($creds)) {
                wp_send_json_error(['message' => 'Could not initialize filesystem API.']);
                return;
            }
        }

        if (!$wp_filesystem->is_writable(ABSPATH)) {
            wp_send_json_error(['message' => 'Could not access filesystem.']);
            return;
        }

        $temp_dir = get_temp_dir();
        if (!$wp_filesystem->is_writable($temp_dir)) {
            wp_send_json_error(['message' => 'Could not write to the temp folder.']);
            return;
        }

        $api_data = $this->getApiData();

        if (empty($api_data['zip_url'])) {
            wp_send_json_error(['message' => 'Invalid zip URL.']);
            return;
        }

        $download_file = download_url($api_data['zip_url']);
        if (is_wp_error($download_file)) {
            wp_send_json_error(['message' => $download_file->get_error_message()]);
            return;
        }

        $unzip_dir = trailingslashit($temp_dir) . uniqid('wp_core_update_');
        if (!wp_mkdir_p($unzip_dir)) {
            $wp_filesystem->delete($download_file); // Clean up downloaded file on failure
            wp_send_json_error(['message' => 'Could not create temporary directory.']);
            return;
        }

     
        $unzip_result = unzip_file($download_file, $unzip_dir);
        $wp_filesystem->delete($download_file); // Clean up after unzipping
        if (is_wp_error($unzip_result)) {
            $wp_filesystem->delete($unzip_dir, true); // Clean up temp folder
            wp_send_json_error(['message' => $unzip_result->get_error_message()]);
            return;
        }


        foreach (CoreUpdateConfig::$files_to_update as $file) {
            $source = trailingslashit($unzip_dir) . 'wordpress/' . $file;
            $destination = ABSPATH . $file;

            if (!$wp_filesystem->exists($source)) {
                $wp_filesystem->delete($unzip_dir, true);
                wp_send_json_error(['message' => "Source file does not exist: $file"]);
                return;
            }

            if (is_dir($source)) {
                if (!self::recursive_copy_dir($wp_filesystem, $source, $destination)) {
                    $wp_filesystem->delete($unzip_dir, true); // Clean up temp folder
                    wp_send_json_error(['message' => "Could not copy directory: $file"]);
                    return;
                }
            } else {
                if (!$wp_filesystem->copy($source, $destination, true)) {
                    $wp_filesystem->delete($unzip_dir, true); // Clean up temp folder
                    wp_send_json_error(['message' => "Could not copy file: $file"]);
                    return;
                }
            }
        }
       
        $wp_filesystem->delete($unzip_dir, true);

        wp_send_json_success(['message' => 'WordPress core updated successfully.']);
    }

    public static function recursive_copy_dir($wp_filesystem, $source, $destination)
    {
       
        if (!$wp_filesystem->is_dir($source)) {
            return false;
        }

        if (!$wp_filesystem->is_dir($destination)) {
            if (!$wp_filesystem->mkdir($destination)) {
                return false;
            }
        }

        $files = $wp_filesystem->dirlist($source);

        foreach ($files as $file_name => $file_info) {
            $src_file = trailingslashit($source) . $file_name;
            $dest_file = trailingslashit($destination) . $file_name;

            if ('f' === $file_info['type']) {
                if (!$wp_filesystem->copy($src_file, $dest_file, true)) {
                    return false;
                }
            } elseif ('d' === $file_info['type']) {
                if (!self::recursive_copy_dir($wp_filesystem, $src_file, $dest_file)) {
                    return false;
                }
            }
        }

        return true;
    }
}
