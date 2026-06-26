<?php
/**
 * Bootstrap per la suite di test PHPUnit di Smart Ad Inserter.
 * Carica l'autoloader di Composer ed inizializza WP_Mock, fornendo
 * stubs per funzioni core di WordPress non incluse nativamente.
 */
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Definizione stubs globali per evitare errori durante l'esecuzione isolata
if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		$val = intval( $maybeint );
		return $val < 0 ? 0 : $val;
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return dirname( $file ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

WP_Mock::bootstrap();
