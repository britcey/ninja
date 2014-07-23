<?php

require_once( dirname(__FILE__).'/base/basesavedreportpool.php' );

/**
 * Autogenerated class SavedReportPool_Model
 *
 * @todo: documentation
 */
class SavedReportPool_Model extends BaseSavedReportPool_Model {
	/**
	 * List which places this object is available for.
	 * @var array
	 */
	protected static $available_for = array(
		// We can't guarantee the integrety of the api for this table in upcoming versions at the moment. Don't expose it to the API
		'api' => false
	);
}