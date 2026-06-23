<?php
/**
 * Eseguito al momento della disinstallazione del plugin.
 *
 * @link              https://github.com/cmuollo/smart-ad-inserter
 * @since             1.0.0
 * @package           Smart_Ad_Inserter
 */

// Se la disinstallazione non è invocata da WordPress, esci.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Elimina l'opzione contenente le impostazioni salvate nel database
delete_option( 'smart_ad_inserter_settings' );

// Elimina i dati temporanei della cache delle posizioni strutturali (Transients)
delete_transient( 'sai_structural_ads_locations' );
