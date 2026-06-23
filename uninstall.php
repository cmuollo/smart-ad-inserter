<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link              https://github.com/cmuollo/smart-ad-inserter
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete settings option
delete_option( 'smart_ad_inserter_settings' );

// Delete any transients
delete_transient( 'sai_structural_ads_locations' );
