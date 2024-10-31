<?php
/**
 * Plugin Name: Save Button
 * Plugin URI:  https://thekrotek.com/wordpress-extensions/miscellaneous
 * Description: Adds a Save button to post editing page, which saves the post and redirects back to the posts list.
 * Version:     1.0.0
 * Author:      The Krotek
 * Author URI:  https://thekrotek.com
 * Text Domain: savebutton 
 * License:     GPL2
 */
 
defined("ABSPATH") or die("Restricted access");

$savebutton = new saveButton();

class saveButton
{
	var $textdomain;
	
	public function __construct()
	{	
		add_action('admin_init', array($this, 'admin_init'));
		add_action('post_submitbox_start', array($this, 'addButton'));
		add_action('admin_notices', array($this, 'addNotice'));
	
		add_filter('plugin_row_meta', array($this, 'updatePluginMeta'), 10, 2);
		add_filter('redirect_post_location', array($this, 'getRedirect'), 10, 2);
		
		$this->textdomain = 'savebutton';
	}
	
	public function admin_init()
	{
		setcookie('wp-save-button-post', '0', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl());
		setcookie('wp-save-button-referer', esc_url($_SERVER['HTTP_REFERER']), time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl());
	}
	
	public function updatePluginMeta($links, $file)
	{
		if ($file == plugin_basename(__FILE__)) {
			$links = array_merge($links, array('<a href="https://thekrotek.com/support">'.__('Donate & Support', $this->textdomain).'</a>'));
		}
	
		return $links;
	}
	
	public function addButton()
	{
		$referer = !empty($_COOKIE['wp-save-button-referer']) ? $_COOKIE['wp-save-button-referer'] : $_SERVER['HTTP_REFERER'];

		echo "<div id='save-action'>";
		echo "<input type='hidden' name='save_referer' value='".esc_url($referer)."' />";
		echo "<input type='submit' value='".__('Save', $this->textdomain)."' class='button button-secondary button-large' style='float: left; margin-right: 10px;' id='save-button' name='save_button' />";
		echo "</div>";
	}

	public function getRedirect($location, $post_id)
	{
		if (!empty($_POST['save_referer'])) {
			$url = $_POST['save_referer'];
		} else {
			$query = array(
				'post_type' => !empty($_POST['post_type']) ? $_POST['post_type'] : 'post',
				'post_status' => !empty($_POST['post_status']) ? $_POST['post_status'] : 'all');
			
			$url = get_admin_url().'edit.php?'.http_build_query($query);
		}
		
		$url = wp_validate_redirect($url, get_admin_url());
		
		setcookie('wp-save-button-referer', esc_url($url), time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl());
		
		if (!empty($_POST['save_button'])) {
			$location = $url;
			setcookie('wp-save-button-post', $post_id, time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl());
		}
		
		return $location;
	}

	public function addNotice()
	{
		if (!empty($_COOKIE['wp-save-button-post'])) {
			echo "<div id='message' class='updated notice notice-success is-dismissible'>";
			echo "<p>".__('Post saved.', $this->textdomain)." <a href='".esc_url(get_permalink($_COOKIE['wp-save-button-post']))."'>".__('View post', $this->textdomain)."</a></p>";
			echo "</div>";
		}
	}
}

?>