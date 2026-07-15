<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

/**
 * Current version.
 */
if ( ! defined( 'WPEMERGE_VERSION' ) ) {
	define( 'WPEMERGE_VERSION', '0.18.0' );
}

/**
 * Absolute path to application's directory.
 */
if ( ! defined( 'WPEMERGE_DIR' ) ) {
	define( 'WPEMERGE_DIR', __DIR__ );
}

/**
 * Optional session service container key.
 * Users may bind a session array/ArrayAccess under this key to enable Flash support.
 */
if ( ! defined( 'WPEMERGE_SESSION_KEY' ) ) {
	define( 'WPEMERGE_SESSION_KEY', 'wpemerge.session' );
}
