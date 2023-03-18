<?php
/*
Plugin Name: Custom Mail Sending
Plugin URI: https://srs.com/
Description: A custom plugin for sending emails to all users
Version: 1.0.0
Author: SRS
Author URI: https://srs.com/
*/

// Check if Action Scheduler class exists
/*if (!class_exists('ActionScheduler')) {
    include_once(WP_PLUGIN_DIR . '/woocommerce/includes/libraries/action-scheduler/classes/ActionScheduler.php');
}*/
// Enqueue the JavaScript code
function send_emails_scripts() {
  wp_enqueue_script( 'send-emails', plugin_dir_url( __FILE__ ) . 'main.js', array( 'jquery' ), '1.0', true );
  wp_localize_script( 'send-emails', 'sendEmailsAjax', array(
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
    'security' => wp_create_nonce( 'send-emails-nonce' ),
  ) );
}

add_action( 'admin_enqueue_scripts', 'send_emails_scripts' );
function custom_menu() {
  add_menu_page( 'Send Emails', 'Send Emails', 'manage_options', 'send_emails', 'send_emails_page' );
}

function send_emails_page() {
  ?>
  <div class="wrap">
    <h1>Send Emails</h1>
    <button id="send-emails-button" class="button button-primary">Send Emails</button>
    <div id="send-emails-result"></div>
  </div>
  <?php
}

function send_emails_ajax() {
  check_ajax_referer( 'send-emails-nonce', 'security' );

  // Call the send_email_to_customers() function here
  send_email_to_customers();

  wp_die();
}

add_action( 'admin_menu', 'custom_menu' );
add_action( 'wp_ajax_send_emails', 'send_emails_ajax' );

function send_email_to_customers() {
    $args = array(
        'role'    => 'customer',
        'orderby' => 'registered',
        'order'   => 'ASC'
    );
    $customers = get_users( $args );

    // Send emails in batches of 1000
    $batch_size = 1000;
    $batches = array_chunk( $customers, $batch_size );
    foreach ( $batches as $batch ) {
        // Schedule the email sending task for this batch
        as_schedule_single_action( time(), 'send_customer_emails', array( $batch ) );
    }
    wp_send_json_success( 'Email sending task scheduled successfully' );
}

// Add a callback function to send emails to customers
add_action( 'send_customer_emails', 'send_customer_emails_callback' );
function send_customer_emails_callback( $customers ) {
	foreach ( $customers as $customer ) {
		$to = $customer->user_email;
		$subject = 'Your subject here';
		$message = 'Your email message here';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( $to, $subject, $message, $headers );
	}
}
