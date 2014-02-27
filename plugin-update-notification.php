<?php
/**
 * Plugin Name: Plugin Update Notification
 * Plugin URI: http://co.deme.me/projects/plugin-update-notification/
 * Description: Receive a daily email if you have any plugins that require updating.
 * Author: Dan Coulter
 * Version: 0.1.6
 * Author URI: http://dancoulter.com
*/

class cm_pun {
	function add_menu_page() {
		if ( function_exists('add_submenu_page') ) {
			add_submenu_page('plugins.php', __('Update Notification', 'cm_pun'), __('Update Notification', 'cm_pun'), 'manage_options', 'update_notification', array('cm_pun', 'create_menu_page'));
		}
	}
	
	function create_menu_page() {
		?>
			<div class="wrap">
				<h2><?php _e('Plugin Update Notification', 'cm_pun') ?></h2>
				<form method="post" action="options.php">
					<input type="hidden" name="action" value="update" />
					<?php wp_nonce_field('update-options'); ?>
					<input type="hidden" name="page_options" value="pun-email" />
					<p>
						<label>
							<?php _e('Email address for notifications:', 'cm_pun') ?>
							<input type="text" name="pun-email" value="<?php echo get_option('pun-email') === false ? get_bloginfo('admin_email') : get_option('pun-email'); ?>" />
						</label>
					</p>
					
					<p class="submit">
						<input type="submit" name="Submit" value="<?php _e('Save Changes', 'cm_pun') ?>" />
					</p>
					
				</form>
				<p>
					This plugin is a production of <a href="http://dancoulter.com">Dan Coulter</a>.  If you find it useful, please consider <a href="http://dancoulter.com/donate">donating</a> a few dollars or bitcoins.
				</p>
			</div>
		<?php
	}
	
	function init() {
		add_action('admin_menu', array('cm_pun', 'add_menu_page'));
	}
	
	function activate() {
		wp_schedule_event(time()+1800, 'daily', 'pun-email');
	}

	function deactivate() {
		wp_clear_scheduled_hook('pun-email');
	}
	
	function send_email() {
		$plugins = get_option("_site_transient_update_plugins");
		$count = count($plugins->response);
		if ( $count ) {
			$subject = sprintf(__ngettext(
				'%1$d plugin needs updated at %2$s', 
				'%1$d plugins need updated at %2$s',
				$count,
				'cm_pun'
			), $count, get_bloginfo('name'));
			
		
			if ( wp_mail(get_option('pun-email'), $subject, get_bloginfo("wpurl") . '/wp-admin/update-core.php') ) {
			} else {
			}
		}
	}
	
	/**
	 * Inserts the settings link on the plugin's page
	 *
	 */
	function settings_link($links) {
		$settings_link = '<a href="plugins.php?page=update_notification">' . __('Settings', 'cm_pun') . '</a>'; 
		array_unshift( $links, $settings_link ); 
		return $links; 
	}
}

add_action('init', array('cm_pun', 'init'));
add_action('pun-email', array('cm_pun', 'send_email'));
register_activation_hook(__FILE__, array('cm_pun', 'activate'));
register_deactivation_hook(__FILE__, array('cm_pun', 'deactivate'));
add_filter("plugin_action_links_" . plugin_basename(__FILE__), array('cm_pun', 'settings_link') ); 

?>