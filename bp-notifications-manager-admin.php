<?php
/**
 * Buddypress Notifications manager admin functions and UI
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * update the notifications meta of members, and save the new configuration
 * @return [type] [description]
 */
function bp_notifications_manager_admin() {
	global $current_user, $bp_settings_updated,$wpdb;
	$bp_settings_updated = false;

	if ( $_POST['submit'] ) {
		check_admin_referer('bp_settings_notifications');
		
		if ( $_POST['notifications'] ) {
			update_option('bp_notifications', $_POST['notifications']);
			$wp_user_search = $wpdb->get_results("SELECT ID FROM $wpdb->users where ID <> 1 ORDER BY ID");
				
			$bp_notifications_update_registred_members = $_POST['bp_notifications_manager']['bp_notifications_manager_update_registred_members'];
			
			if( isset($bp_notifications_update_registred_members) 
			 && $bp_notifications_update_registred_members == 'yes'){
			 	update_option('bp_notifications_manager_update_registred_members','no');		
				foreach ( $wp_user_search as $userid ) {
					$user_id = (int) $userid->ID;
					foreach ( (array)$_POST['notifications'] as $key => $value ) {
						update_user_meta($user_id, $key, $value );
					}
				}
			}
			update_option('bp_notifications_manager_create_meta_when_update','no');	
		}
		
		if( $_POST['bp_notifications_manager'] ){
			update_option('bp_notifications_manager_disabled', $_POST['bp_notifications_manager']['bp_notifications_manager_disabled']);
		}
		$bp_settings_updated = true;
	}
	bp_notifications_content();
}

/**
 * print the content of the bp notifications manager screen
 * @return [type] [description]
 */
function bp_notifications_content() {
	global $bp, $current_user, $bp_settings_updated;
	$bp_notifications = get_option('bp_notifications');
	
	// here, i save the admin notification in a temp array,
	// than i set the current config of bp Notif's Manager in the 
	// meta of the admin. 
	// bp_notification_settings() use bp_displayed_user_id() to get meta of the current user, 
	// // but in this case, bp_displayed_user_id return 0 because it not excute in bp context screen
	// i set $bp->displayed_user->id to 1 (admin id ) to force showing the config meta 
	if(isset($bp_notifications) && $bp_notifications != false){
		$admin_notifications = array();
		$bp->displayed_user->id = 1;
		foreach ( (array)$bp_notifications as $key => $value ){
			$admin_notifications[$key] = get_user_meta( $bp->displayed_user->id , $key, true);
			update_user_meta( $bp->displayed_user->id , $key, $value );
		}
	}

	?>
	<?php if ( $bp_settings_updated ) { ?>
		<div id="message" class="updated fade">
			<p><?php _e( 'Changes Saved.', 'buddypress' ) ?></p>
		</div>
	<?php }
	?>

<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div><h2>Buddypress Notification Manager Settings</h2>

	<div class="message updated"><h3><?php _e( 'Note : All modifications are not applied to <strong>admin</strong> account.', 'bp-notif-manger' ) ?></h3></div>

	<style> table{ width:100%;border:1px;border-color:black;cellpadding:3px;cellspacing:3px;text-align: left;} .title{width:80%;} </style>
	
	<form  method="post" id="bp-notifications-settings" name="bp-notifications-settings">

		<h3><?php _e( 'Email Notifications', 'buddypress' ) ?></h3>
		<p><?php _e( 'Send a notification by email when:', 'buddypress' ) ?></p>

		<?php do_action( 'bp_notification_settings' ) ?>

		<h3><?php _e( 'Email Notifications user access', 'buddypress' ) ?></h3>
		<table>
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Disable access', 'buddypress' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
					<th class="no"><?php _e( 'No', 'buddypress' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
				    <td></td>
					<td><?php _e( 'Disable access to notifications page', 'bp-notif-manager' ) ?></td>
					<td class="yes"><input type="radio" name="bp_notifications_manager[bp_notifications_manager_disabled]" value="yes" <?php if ( 'yes' == get_option('bp_notifications_manager_disabled') ) { ?>checked="checked" <?php } ?>/></td>
					<td class="no"><input  type="radio" name="bp_notifications_manager[bp_notifications_manager_disabled]" value="no"  <?php if ( 'no'  == get_option('bp_notifications_manager_disabled') ) { ?>checked="checked" <?php } ?>/></td>
				</tr>
			</tbody>
	    </table>

	    <h3><?php _e( 'Settings', 'bp-notifs-manager' ) ?></h3>
	    <table>
	   		<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Apply change', 'bp-notif-manager' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
					<th class="no"><?php _e( 'No', 'buddypress' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td class="title"><?php _e( 'Apply change to registered users (it may take a few seconds - apply just one time)', 'bp-notif-manger' ) ?></td>	
					<td class="yes"><input type="radio" name="bp_notifications_manager[bp_notifications_manager_update_registred_members]" value="yes" <?php if ( 'yes' == get_option('bp_notifications_manager_update_registred_members') ) { ?>checked="checked" <?php } ?>/></td>
					<td class="no"><input  type="radio" name="bp_notifications_manager[bp_notifications_manager_update_registred_members]" value="no"  <?php if ( 'no'  == get_option('bp_notifications_manager_update_registred_members') ) { ?>checked="checked" <?php } ?>/></td>
				</tr>
			</tbody>
	    </table>
	    
	    <div class="submit">
			<input type="submit" name="submit" value="<?php _e( 'Save Changes', 'buddypress' ) ?>" id="submit" class="auto" />
		</div>

		<?php wp_nonce_field('bp_settings_notifications') ?>
	</form>
</div>

<?php
	// here, i reset the default admin notification setting 
	// (admin notif meta are not changed by the the plugin)
	if(isset($bp_notifications)){
		foreach ( (array)$admin_notifications as $key => $value ) 
			update_user_meta( $bp->displayed_user->id , $key, $value );
	}
}