<?php
/**
 * Plugin Name: Gravity Forms Accessibility Enhancements
 * Plugin URI: https://yourwebsite.com/gravity-forms-accessibility-enhancements
 * Description: An addon for Gravity Forms to enhance accessibility features and improve user experience.
 * Version: 1.0.2
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: gf-accessibility-enhancements
 */

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue our JavaScript
function gf_accessibility_enqueue_scripts($hook) {
    if ('toplevel_page_gf_accessibility' !== $hook) return;
    wp_enqueue_script('gf-accessibility-js', plugin_dir_url(__FILE__) . 'js/accessibility.js', ['jquery'], '1.0.0', true);
    wp_localize_script('gf-accessibility-js', 'gf_accessibility', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('admin_enqueue_scripts', 'gf_accessibility_enqueue_scripts');

// Check if Gravity Forms is active
if (class_exists('GFForms')) {

    add_action('admin_menu', 'gf_accessibility_add_menu');

    function gf_accessibility_add_menu() {
        add_menu_page(
            'Accessibility Enhancements',
            ' GF    Accessibility Enhancements',
            'manage_options',
            'gf_accessibility',
            'gf_accessibility_render_submenu',
            'dashicons-forms',
            3
        );
    }
    // add the action
    add_action('admin_menu', 'gf_accessibility_add_menu');

    function gf_accessibility_render_submenu() {
        $forms = GFAPI::get_forms();

        // Check if there are forms, else set a default value to avoid errors
        $selected_form_id = isset($_POST['selected_form_id']) ? absint($_POST['selected_form_id']) : (!empty($forms) ? $forms[0]['id'] : 0);

        echo '<div class="wrap">';
        echo '<h1>Reorder Fields</h1>';
        echo '<form method="POST" action="">';

        // Select Form
        echo '<label>Select Form: </label>';
        echo '<select id="gf_forms_dropdown" name="selected_form_id">';
        foreach ($forms as $form) {
            $selected = ($form['id'] == $selected_form_id) ? 'selected' : '';
            echo "<option value='{$form['id']}' {$selected}>{$form['title']}</option>";
        }
        echo '</select><br><br>';

        // Field Dropdowns (to be populated by JavaScript based on AJAX call)
        echo '<label>Field 1: </label>';
        echo '<select id="gf_field_1_dropdown"></select><br><br>';

        echo '<label>Field 2: </label>';
        echo '<select id="gf_field_2_dropdown"></select><br><br>';

        // Move Above and Move Below buttons
        echo '<button type="button" id="move_above_btn">Move Above</button>';
        echo '<button type="button" id="move_below_btn">Move Below</button><br><br>';

        // Order Preview
        echo '<h2>Order Preview:</h2>';
        echo '<ul id="reorderFieldsList">';
        // This list will be populated by JS dynamically after fetching fields based on form selection
        echo '</ul>';

        echo '<input type="hidden" name="field_order" id="field_order_input" value="">';  
        echo '<input type="submit" name="gf_reorder_fields" value="Save Order">';
        
        echo '</form>';
        echo '</div>';
    }
    
    function gf_reorder_fields($form, $order) {
        $new_fields = [];
        foreach ($order as $field_id) {
            foreach ($form['fields'] as $field) {
                if ($field->id == $field_id) {
                    $new_fields[] = $field;
                    break;
                }
            }
        }
        $form['fields'] = $new_fields;
        return $form;
    }

    if (isset($_POST['gf_reorder_fields'])) {
        $order = explode(',', $_POST['field_order']);
        $order = array_map('sanitize_text_field', $order);

        $selected_form_id = absint($_POST['selected_form_id']);
        $form = GFAPI::get_form($selected_form_id);

        $updated_form = gf_reorder_fields($form, $order);

        $result = GFAPI::update_form($updated_form);
        
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error">';
            echo '<p>Error saving form. ' . $result->get_error_message() . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-success">';
            echo '<p>Form saved successfully!</p>';
            echo '</div>';
        }
    }

    add_action('wp_ajax_fetch_form_fields', 'gf_accessibility_fetch_form_fields');
    function gf_accessibility_fetch_form_fields() {
        $form_id = absint($_POST['form_id']);
        $form = GFAPI::get_form($form_id);

        if ($form) {
            $fields = [];
            foreach ($form['fields'] as $field) {
                $label = isset($field['label']) ? $field['label'] : '';
                $type = isset($field['type']) ? $field['type'] : '';
                $field['formatted_label'] = "{$label} ({$type})";  // This adds (type) to each field label
                $fields[] = $field;
            }
            wp_send_json_success($fields);
        } else {
            wp_send_json_error('Form not found.');
        }
    }
}