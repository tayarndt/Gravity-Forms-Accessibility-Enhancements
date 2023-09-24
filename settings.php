<?php

// Register the autoloader
$autoload_file = plugin_dir_path(__FILE__) . 'src/autoload.php';
if (file_exists($autoload_file)) {
    require_once $autoload_file;
} else {
    error_log('Autoload file is missing. Please check your installation.');
    return;
}

use LicenseBridge\WordPressSDK\LicenseBridgeSDK;

function gf_accessibility_license() {
    static $instance = null;
    
    if (null === $instance) {
        $instance = new LicenseBridgeSDK('https://licensebridge.com/market/gravity-forms-accessibility-enhancements');
    }

    return $instance;
}

function gf_accessibility_add_settings_menu() {
    add_options_page(
        __('GF Accessibility Settings', 'your-text-domain'),
        __('GF Accessibility', 'your-text-domain'),
        'manage_options',
        'gf_accessibility_settings',
        'gf_accessibility_render_settings_page'
    );
}
add_action('admin_menu', 'gf_accessibility_add_settings_menu');

function gf_accessibility_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'your-text-domain'));
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('gf_accessibility_settings_save', 'gf_accessibility_settings_nonce');
        
        $license_key = sanitize_text_field($_POST['gf_accessibility_license_key'] ?? '');
        update_option('gf_accessibility_license_key', $license_key);
        
        $license_status = gf_accessibility_license()->is_license_active('gravity-forms-accessibility-enhancements') ? 'active' : 'inactive';
        update_option('gf_accessibility_license_status', $license_status);
    }
    
    $license_key = get_option('gf_accessibility_license_key', '');
    $license_status = get_option('gf_accessibility_license_status', 'inactive');
    
    ?>
    <div class="wrap">
        <h2><?php _e('GF Accessibility Settings', 'your-text-domain'); ?></h2>
        <form method="POST" action="">
            <?php wp_nonce_field('gf_accessibility_settings_save', 'gf_accessibility_settings_nonce'); ?>
            
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="gf_accessibility_license_key"><?php _e('License Key', 'your-text-domain'); ?></label></th>
                    <td>
                        <input name="gf_accessibility_license_key" type="text" id="gf_accessibility_license_key" value="<?php echo esc_attr($license_key); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('License Status', 'your-text-domain'); ?></th>
                    <td><?php echo esc_html(ucfirst($license_status)); ?></td>
                </tr>
            </table>
            
            <?php submit_button(__('Save Settings', 'your-text-domain')); ?>
        </form>
    </div>
    <?php
}
