<?php
namespace SmartAdInserter\Injection;

/**
 * Interfaccia comune per tutte le classi concrete di iniezione.
 *
 * Rappresenta l'interfaccia nel pattern Strategy. Specifica il contratto comune
 * che tutti i moduli di iniezione (legati al contenuto o strutturali) devono soddisfare.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
interface AdInjectorInterface {

	/**
	 * Esegue l'analisi e la manipolazione dell'HTML per inserire i banner pubblicitari.
	 *
	 * @since    1.0.0
	 * @param    string    $subject    L'HTML originario da analizzare e modificare.
	 * @return   string                L'HTML risultante contenente i tag dei banner.
	 */
	public function inject( string $subject ): string;
}
