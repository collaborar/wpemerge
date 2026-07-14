<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\View;

trait HasNameTrait {
	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param  string $name
	 * @return static $this
	 */
	public function setName( string $name ): static {
		$this->name = $name;
		return $this;
	}
}
