<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Routing\Conditions;

use WPEmerge\Requests\RequestInterface;

/**
 * Check against the current post's status.
 *
 * @codeCoverageIgnore
 */
class PostStatusCondition implements ConditionInterface {
	/**
	 * Post status to check against.
	 */
	protected string $post_status = '';

	/**
	 * Constructor
	 *
	 * @codeCoverageIgnore
	 * @param string $post_status
	 */
	public function __construct( string $post_status ) {
		$this->post_status = $post_status;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isSatisfied( RequestInterface $request ): bool {
		$post = get_post();
		return ( is_singular() && $post && $this->post_status === $post->post_status );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments( RequestInterface $request ): array {
		return ['post_status' => $this->post_status];
	}
}
