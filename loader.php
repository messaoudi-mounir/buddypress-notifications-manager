<?php
/*
Plugin Name: Buddypress Notifications Manager
Plugin URI: 
Description: Buddypress Notifications Manager : Manage the BP Notifcations Settings for all users in One screen.
Version: 1.0
Requires at least: Example: WP 3.2.1, BuddyPress 1.5
Tested up to: BuddyPress 1.5, 1.6 
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: MegaInfo
Author URI: http://profiles.wordpress.org/megainfo 
Network: true
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'BP_NOTIFICATIONS_MANAGER_VERSION', '1.0' );

// Define a constant that we can use to construct file paths throughout the component
define( 'BP_NOTIFICATIONS_MANAGER_PLUGIN_DIR', dirname( __FILE__ ) );

/**
 * bp_notifications_install
 * @return [type] [description]
 */
function bp_notifications_manager_activate() {
  	global $bp, $wpdb;

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		add_option('bp_notifications_update_registred_members','yes', 'Apply change to registered users (it may take a few seconds)');
		add_option('bp_notifications_manager_disabled','no', 'disable notifications access to all users');
	}else {
		deactivate_plugins( basename( __FILE__ ) ); // Deactivate this plugin
		die( _e( 'You cannot enable BuddyPress NotifManager because <strong>BuddyPress</strong> is not active. Please install and activate BuddyPress before trying to activate Buddypress NotifManager.' , 'bp-follow' ) );
	}
}
register_activation_hook(__FILE__,'bp_notifications_manager_activate');

/**
 * bp_notifications_remove
 * @return [type] [description]
 */
function bp_notifications_manager__deactivate() {
   delete_option('bp_notifications_manager_disabled');
   delete_option('bp_notifications_manager_create_meta_when_update');
}
register_deactivation_hook(__FILE__,'bp_notifications_manager__deactivate');

function bp_notifications_manager() {
	require ( dirname( __FILE__ ) . '/bp-notifications-manager-admin.php' );
	bp_notifications_manager_admin();
}

/** 
 * add Buddypress Notification manager link in Settings Menu 
 */
function bp_notifications_manager_admin_actions() {
	add_options_page("Buddypress Notifications Manager", "BP Notif's Manager", 1, "bp_notifications_manager_admin", "bp_notifications_manager");
}
add_action('admin_menu', 'bp_notifications_manager_admin_actions');


/** Add sign-up field to BuddyPress sign-up array */
function bp_notifications_manager_activated_user( $user_id, $key, $user ) {	
	$notifications = unserialize( get_option('bp_notifications') ); 
	if( isset($notifications) ){
		foreach ( (array)$notifications as $key => $value ) {
			//update_user_meta($user_id, $key, $value );
			$usermeta[$key] =  $value;
			add_user_meta($user_id, $key, $value, true);
		}
	}
}
add_filter( 'bp_core_activated_user', 'bp_notifications_manager_activated_user', 1, 3);

/** 
  * enable/disable Notification subnav item and menu element 
 */
function bp_notifications_manager_subnav(){
	if( get_option('bp_notifications_manager_disabled')=='yes' ){
		global $bp;
		//if ( $bp->current_component == $bp->settings->slug ) {
			// if current user is not admin
			if( !is_site_admin() ){
			
				// //remove notifcation subnav item and notification link for adminbar
				bp_core_remove_subnav_item($bp->settings->slug, 'notifications');
				remove_action( 'bp_adminbar_menus', 'bp_adminbar_notifications_menu', 8 );
				// remove notification setting link from wp adminbar if is bp use it
				if ( bp_use_wp_admin_bar() ) {
					$bp->temp_slug = $slug;
					add_action( 'wp_before_admin_bar_render', create_function(
					'', 'global $bp, $wp_admin_bar; $wp_admin_bar->remove_menu( "my-account-settings-notifications" );' ) );
				}
			}
		//}
	}
}
add_action( 'init', 'bp_notifications_manager_subnav', 2 );

if ( file_exists( BP_NOTIFICATIONS_MANAGER_PLUGIN_DIR . '/languages/' . get_locale() . '.mo' ) )
	load_textdomain( 'bp-notifs-manager', BP_NOTIFICATIONS_MANAGER_PLUGIN_DIR . '/languages/' . get_locale() . '.mo' );