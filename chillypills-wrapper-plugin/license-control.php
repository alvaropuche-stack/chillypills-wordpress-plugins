<?php
if (!defined('ABSPATH')) {
    exit;
}

class Chillypills_License_Control {

    public static function validate_license($license_key = '') {
        if (empty($license_key)) {
            $license_key = get_option('chillypills_license_key');
        }
        $site_url = get_site_url();
        $response = wp_remote_get("https://plugins-control.chillypills.com/validate_license.php?license_key={$license_key}&site_url={$site_url}");
        if (is_wp_error($response)) {
            return false;
        }
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        return $response_data['success'];
    }
}
?>
