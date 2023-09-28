<?php
require_once('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function gf_accessibility_render_settings_submenu() {
    $secret_key = $_ENV['STRIPE_SECRET_KEY']; // or try $_SERVER['STRIPE_SECRET_KEY'] if $_ENV doesn't work
    
    \Stripe\Stripe::setApiKey($secret_key);    if (isset($_POST['gf_accessibility_save_license_key']) && check_admin_referer('gf_accessibility_settings_save')) {
        $email = isset($_POST['gf_license_key']) ? sanitize_email($_POST['gf_license_key']) : '';
        update_option('gf_license_key', $email);
        
        try {
            $customers = \Stripe\Customer::all(['email' => $email]);
        
            $isActive = false;
            $productType = '';
        
            foreach ($customers->autoPagingIterator() as $customer) {
                $subscriptions = \Stripe\Subscription::all(['customer' => $customer->id]);
                foreach ($subscriptions->autoPagingIterator() as $subscription) {
                    $productId = $subscription->items->data[0]->price->product;
                    if ($productId === 'prod_650f8af4d476bee5f9077462') {
                        $isActive = true;
                        $productType = 'Yearly';
                        break 2;
                    } elseif ($productId === 'prod_OisRWINPtkJXNJ') {
                        $isActive = true;
                        $productType = 'Lifetime';
                        break 2;
                    }
                }
            }

            if ($isActive) {
                echo '<div class="notice notice-success is-dismissible">';
                echo "<p>You are active and can get updates. You have a $productType subscription.</p>";
                echo '</div>';
            } else {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>The provided email does not have an active subscription.</p>';
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
            echo '</div>';
        }
    }

    // Continue with your form rendering here...

    $email = get_option('gf_license_key', '');
    
    echo '<div class="wrap">';
    echo '<h1>Settings</h1>';
    echo '<p>In order to use this plugin, you need to enter your license key. This is the email that you used when you bought the plugin.</p>';
    echo '<form method="POST" action="">';
    echo '<label for="gf_license_key">License Key: </label>';
    echo '<input type="email" id="gf_license_key" name="gf_license_key" value="' . esc_attr($email) . '">';
    wp_nonce_field('gf_accessibility_settings_save');
    echo '<input type="submit" name="gf_accessibility_save_license_key" value="Save">';
    echo '</form>';
    echo '</div>';
}
