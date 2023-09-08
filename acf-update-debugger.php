<?php
/*
Plugin Name: ACF Update Debugger
Description: A test plugin to output various states and transients to try and debug ACF issues on production hosts.
Version: 1.0.1
Author: Liam Gladdy
*/


add_filter( 'site_health_navigation_tabs', 'acf_site_health_navigation_tabs' );
function acf_site_health_navigation_tabs( $tabs ) {
	$tabs['acf-update'] = 'ACF Update Debugging';
	return $tabs;
}

add_action( 'site_health_tab_content', 'acf_site_health_tab' );
function acf_site_health_tab( $tab ) {

	if ( 'acf-update' !== $tab ) {
		return;
	}
	echo '<div class="health-check-body health-check-status-tab hide-if-no-js">';

	echo '<h1>ACF Update Debug Information</h1>';
	echo '<h3>Site Options</h3>';
	acf_debug_output_options( array( 'siteurl', 'home' ) );
	echo '<h3>ACF Options</h3>';
	acf_debug_output_options( array( 'acf_pro_license' ) );
	echo '<h3>Update Transients</h3>';
	acf_debug_output_transients( array( 'acf_plugin_updates', 'acf_plugin_info_pro' ) );
	echo '<h3>Update Site Transients</h3>';
	acf_debug_output_site_transients( array( 'update_plugins' ) );
	echo '<h3>Rewrite Rules</h3>';
	acf_debug_output_options( array( 'rewrite_rules' ) );

	echo '</div>';
}

function acf_debug_output_options( array $options ) {
	foreach ( $options as $option ) {
		$option_value      = get_option( $option );
		$raw_option_value  = acf_debug_raw_query( $option );
		$outputs           = array();
		if ( $raw_option_value != $option_value ) {
			echo '<strong style="color:red">' . $option . ' [cache does not match database]</strong>: ';
			$outputs['cache'] = $option_value;
			$outputs['db']    = $raw_option_value;
		} else {
			echo '<strong>' . $option . '</strong>: ';
			$outputs['cache-and-db'] = $option_value;
		}
		foreach ( $outputs as $type => $option_value ) {
			echo '[' . $type . ']' . PHP_EOL;
			if ( empty( $option_value ) ) {
				echo '[EMPTY]';
			} elseif ( is_string( $option_value ) ) {
				echo $option_value;
			} else {
				echo acf_debug_guesstimate_size( $option_value ) . '<br />' . PHP_EOL;
				echo '<pre>';
				var_dump( $option_value );
				echo '</pre>';
			}
			echo '<br />' . PHP_EOL;
		}
	}
}

function acf_debug_output_site_transients( array $options ) {
	foreach ( $options as $option ) {
		$option_value     = get_site_transient( $option );
		$raw_option_value = acf_debug_raw_query( '_site_transient_' . $option );
		$outputs          = array();
		if ( $raw_option_value != $option_value ) {
			echo '<strong style="color:red">' . $option . ' [cache does not match database]</strong>: ';
			$outputs['cache'] = $option_value;
			$outputs['db']    = $raw_option_value;
		} else {
			echo '<strong>' . $option . '</strong>: ';
			$outputs['cache-and-db'] = $option_value;
		}
		foreach ( $outputs as $type => $option_value ) {
			echo '[' . $type . ']' . PHP_EOL;
			if ( empty( $option_value ) ) {
				echo '[EMPTY]';
			} elseif ( is_string( $option_value ) ) {
				echo $option_value;
			} else {
				echo acf_debug_guesstimate_size( $option_value ) . '<br />' . PHP_EOL;
				echo '<pre>';
				var_dump( $option_value );
				echo '</pre>';
			}
			echo '<br />' . PHP_EOL;
		}
	}
}

function acf_debug_output_transients( array $options ) {
	foreach ( $options as $option ) {
		$option_value     = get_transient( $option );
		$raw_option_value = acf_debug_raw_query( '_transient_' . $option );
		$outputs          = array();
		if ( $raw_option_value != $option_value ) {
			echo '<strong style="color:red">' . $option . ' [cache does not match database]</strong>: ';
			$outputs['cache'] = $option_value;
			$outputs['db']    = $raw_option_value;
		} else {
			echo '<strong>' . $option . '</strong>: ';
			$outputs['cache-and-db'] = $option_value;
		}
		foreach ( $outputs as $type => $option_value ) {
			echo '[' . $type . ']' . PHP_EOL;
			if ( empty( $option_value ) ) {
				echo '[EMPTY]';
			} elseif ( is_string( $option_value ) ) {
				echo $option_value;
			} else {
				echo acf_debug_guesstimate_size( $option_value ) . '<br />' . PHP_EOL;
				echo '<pre>';
				var_dump( $option_value );
				echo '</pre>';
			}
			echo '<br />' . PHP_EOL;
		}
	}
}

function acf_debug_raw_query( $option_value ) {
	global $wpdb;
	$result = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s", $option_value ) );
	if ( is_serialized( $result ) ) {
		$result = unserialize( $result );
	}
	return $result;
}

function acf_debug_guesstimate_size( $var ) {
	return acf_format_bytes( strlen( serialize( $var ) ) );
}

function acf_format_bytes( $bytes, $precision = 2 ) {
	$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );

	$bytes = max( $bytes, 0 );
	$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
	$pow   = min( $pow, count( $units ) - 1 );

	return number_format( round( $bytes, $precision ) ) . $units[ $pow ];
}
