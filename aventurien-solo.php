<?php
/*
Plugin Name: Aventurien Solo Adventures
Plugin URI: https://wordpress.org/plugins/aventurien-solo/
Description: This plugin allows you to display twine text adventures in your wordpress blog using shortcodes.
Version: 1.00
Author: Klemens
Author URI: https://profiles.wordpress.org/Klemens#content-plugins
Text Domain: aventurien-solo
*/ 

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 'aventurien-solo' Installtion
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once('inc/aventurien-solo-database.php'); 

register_activation_hook(__FILE__, 'aventurien_solo_install');

function aventurien_solo_install() {
    aventurien_solo_create_tables();
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 'aventurien-solo' Shortcode
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

add_shortcode ('aventurien-solo', 'aventurien_solo_shortcode');

function aventurien_solo_shortcode($atts, $content) {

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

	if (!isset($content) || !$content) {
		$content = get_the_title();
	}

	extract(shortcode_atts(array(
		'module' => 'sample',
        'style' => 'default'
	), $atts));

	return aventurien_solo_html($module, $content);
}

function aventurien_solo_html($module, $title) {

    $path_local = plugin_dir_path(__FILE__);
    $path_url = plugins_url() . "/aventurien-solo";

    $output = '<iframe src="' . $path_url . '/Solo.php?module=' . $module . '&title=' . $title. '" style="height: 620px; -webkit-filter: none; filter: none; border: 0px;"></iframe>';

	return $output;
}


?>