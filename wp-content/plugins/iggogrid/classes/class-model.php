<?php
/**
 * IggoGrid Base Model with members and methods for all models
 *
 * @package IggoGrid
 * @subpackage Models
 * @author Iggo
 * @since 1.0.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * IggoGrid Base Model class
 * @package IggoGrid
 * @subpackage Models
 * @author Iggo
 * @since 1.0.0
 */
abstract class IggoGrid_Model {

	/**
	 * Initialize all models
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// intentionally left blank
	}

} // class IggoGrid_Model
