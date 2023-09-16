<?php
/**
 * Plugin Name: Gravity Forms Accessibility Enhancements
 * Plugin URI: https://yourwebsite.com/gravity-forms-accessibility-enhancements
 * Description: An addon for Gravity Forms to enhance accessibility features and improve user experience.
 * Version: 1.0.0
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
    wp_enqueue_script('gf-accessibility-js', plugin_dir_url(__FILE__) . 'js/accessibility.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'gf_accessibility_enqueue_scripts');

// Check if Gravity Forms is active
if (class_exists('GFForms')) {
    add_action('admin_menu', 'gf_accessibility_add_menu');

    function gf_accessibility_add_menu() {
        add_menu_page(
            'Accessibility Enhancements',
            'Accessibility Enhancements',
            'manage_options',
            'gf_accessibility',
            'gf_accessibility_render_submenu',
            'dashicons-forms',
            30
        );
    }

    function gf_accessibility_render_submenu() {
        $forms = GFAPI::get_forms();
        $selected_form_id = isset($_POST['selected_form_id']) ? absint($_POST['selected_form_id']) : $forms[0]['id'];

        $selected_form = GFAPI::get_form($selected_form_id);

        echo '<div class="wrap">';
        echo '<h1>Reorder Fields</h1>';

        echo '<form method="POST" action="">';

        echo '<select id="gf_forms_dropdown" name="selected_form_id">';
        foreach ($forms as $form) {
            $selected = ($form['id'] == $selected_form_id) ? 'selected' : '';
            echo "<option value='{$form['id']}' {$selected}>{$form['title']}</option>";
        }
        echo '</select>';

        echo '<ul id="reorderFieldsList">';
        foreach ($selected_form['fields'] as $field) {
            echo "<li data-id='{$field->id}'>{$field->label} 
                  <button type='button' class='move-up'>Move Up</button> 
                  <button type='button' class='move-down'>Move Down</button>
                  </li>";
        }
        echo '</ul>';
        
        echo '<input type="hidden" name="field_order" id="field_order_input" value="">';  
        echo '<input type="submit" name="gf_reorder_fields" value="Save Order">';
        
        echo '</form>';
        echo '</div>';
    }
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
    // Retrieve the submitted order from POST data
    $order = explode(',', $_POST['field_order']);
    $order = array_map('sanitize_text_field', $order);  
    
    // Get the selected form
    $selected_form_id = absint($_POST['selected_form_id']);  
    $form = GFAPI::get_form($selected_form_id);
    
    // Reorder the fields
    $updated_form = gf_reorder_fields($form, $order);
    
    // Update the form using GFAPI
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
