<?php
namespace SmartAdInserter\Injection;

/**
 * Interface AdInjectorInterface
 *
 * Defines the contract that all concrete ad injection classes must implement.
 *
 * @since      1.0.0
 * @package    Smart_Ad_Inserter
 * @subpackage Smart_Ad_Inserter/includes/injection
 * @author     Carmine Muollo
 */
interface AdInjectorInterface {

	/**
	 * Perform the ad injection logic on the provided content or HTML.
	 *
	 * @since    1.0.0
	 * @param    string    $subject    The content or page HTML to manipulate.
	 * @return   string                The modified subject.
	 */
	public function inject( string $subject ): string;
}
