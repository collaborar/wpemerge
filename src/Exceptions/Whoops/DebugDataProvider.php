<?php

namespace WPEmerge\Exceptions\Whoops;

use Psr\Container\ContainerInterface;
use WPEmerge\Routing\Router;

/**
 * Provide debug data for usage with \Whoops\Handler\PrettyPageHandler.
 *
 * @codeCoverageIgnore
 */
class DebugDataProvider {
	/**
	 * Container.
	 */
	protected ContainerInterface $container;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * Convert a value to a scalar representation.
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function toScalar( mixed $value ): mixed {
		$type = gettype( $value );

		if ( ! is_scalar( $value ) ) {
			$value = '(' . $type . ')' . ( $type === 'object' ? ' ' . get_class( $value ) : '' );
		}

		return $value;
	}

	/**
	 * Return printable data about the current route.
	 *
	 * @param \Whoops\Exception\Inspector $inspector
	 * @return array<string, mixed>
	 */
	public function route( \Whoops\Exception\Inspector $inspector ): array {
		/** @var \WPEmerge\Routing\RouteInterface|null $route */
		$route = $this->container->get( Router::class )->getCurrentRoute();

		if ( ! $route ) {
			return [];
		}

		$attributes = [];

		foreach ( $route->getAttributes() as $attribute => $value ) {
			// Only convert the first level of an array to scalar for simplicity.
			if ( is_array( $value ) ) {
				$value = '[' . implode( ', ', array_map( [$this, 'toScalar'], $value ) ) . ']';
			} else {
				$value = $this->toScalar( $value );
			}

			$attributes[ $attribute ] = $value;
		}

		return $attributes;
	}
}
