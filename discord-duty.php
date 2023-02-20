<?php
/*
Plugin Name: Duty Time
Description: A plugin for saving on duty and off duty times.
Version: 1.0
Author: Your Name
*/
ini_set('display_errors', 1);

function register_duty_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Duties',
        'supports' => array( 'title', 'editor', 'custom-fields' ),
        'show_in_rest' => true,
    );
    register_post_type( 'duty', $args );
}
add_action( 'init', 'register_duty_post_type' );


function check_duty_time($on_duty, $off_duty) {
    // Parse the duty time strings into DateTime objects
    $on_duty_dt = DateTime::createFromFormat('Y-m-d H:i', $on_duty);
    $off_duty_dt = DateTime::createFromFormat('Y-m-d H:i', $off_duty);

    // Calculate the difference between the duty times in hours
    $diff_hours = ($off_duty_dt->getTimestamp() - $on_duty_dt->getTimestamp()) / (60 * 60);

    // Check if the duty time is between 6 and 14 hours
    if ($diff_hours >= 6 && $diff_hours <= 14) {
        return true;
    } else {
        return false;
    }
}

function discord_duty_handler() {
    // Get the duty time data from the POST request
    $on_duty = $_POST['on_duty'];
    $off_duty = $_POST['off_duty'];

    // Check if the duty time is valid
    $is_valid = check_duty_time($on_duty, $off_duty);

    if ($is_valid) {
        // Create a new duty post
        $post_id = wp_insert_post(array(
            'post_type' => 'duty',
            'post_title' => 'Duty Time',
            'post_status' => 'publish'
        ));

        // Add custom fields for the on and off duty times
        update_post_meta($post_id, 'on_duty', $on_duty);
        update_post_meta($post_id, 'off_duty', $off_duty);

        // Return a success message to the Discord bot
        echo 'Success';
    } else {
        // Return an error message to the Discord bot
        echo 'Error: Invalid duty time';
    }
    exit;
}

add_action('wp_ajax_discord_duty', 'discord_duty_handler');
add_action('wp_ajax_nopriv_discord_duty', 'discord_duty_handler');

function discord_duty_shortcode($atts) {
    $duties = get_posts(array(
        'post_type' => 'duty',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ));

    $output = '<table>';
    $output .= '<tr><th>On Duty</th><th>Off Duty</th></tr>';

    foreach ($duties as $duty) {
        $on_duty = get_post_meta($duty->ID, 'on_duty', true);
        $off_duty = get_post_meta($duty->ID, 'off_duty', true);

        $output .= "<tr><td>{$on_duty}</td><td>{$off_duty}</td></tr>";
    }

    $output .= '</table>';

    return $output;
}

add_shortcode('discord_duty', 'discord_duty_shortcode');

function load_scripts() {
    wp_enqueue_script( 'my-ajax-script', plugin_dir_url( __FILE__ ) . 'js/my-ajax-script.js', array('jquery') );
    wp_localize_script( 'my-ajax-script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_enqueue_scripts', 'load_scripts');

function my_ajax_handler() {
// Get the duty time data from the POST request
$on_duty = $_POST['on_duty'];
$off_duty = $_POST['off_duty'];
	
	// Check if the duty time is valid
$is_valid = check_duty_time($on_duty, $off_duty);

if ($is_valid) {
    // Create a new duty post
    $post_id = wp_insert_post(array(
        'post_type' => 'duty',
        'post_title' => 'Duty Time',
        'post_status' => 'publish'
    ));

    // Add custom fields for the on and off duty times
    update_post_meta($post_id, 'on_duty', $on_duty);
    update_post_meta($post_id, 'off_duty', $off_duty);

    // Return a success message to the AJAX request
    wp_send_json_success('Success');
} else {
    // Return an error message to the AJAX request
    wp_send_json_error('Error: Invalid duty time');
}
exit;
}

add_action('wp_ajax_my_ajax_handler', 'my_ajax_handler');
add_action('wp_ajax_nopriv_my_ajax_handler', 'my_ajax_handler');

function duty_times_shortcode() {
    $args = array(
        'post_type' => 'duty',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $duties = new WP_Query($args);
    if($duties->have_posts()):
        $output = '<ul>';
        while($duties->have_posts()): $duties->the_post();
            $on_duty = get_post_meta(get_the_ID(), 'on_duty', true);
            $off_duty = get_post_meta(get_the_ID(), 'off_duty', true);
            $output .= '<li>On Duty: ' . $on_duty . ', Off Duty: ' . $off_duty . '</li>';
        endwhile;
        $output .= '</ul>';
        wp_reset_postdata();
    else:
        $output = 'No duty times found.';
    endif;

    return $output;
}
add_shortcode('duty_times', 'duty_times_shortcode');


















