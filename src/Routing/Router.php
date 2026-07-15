<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Routing;

use Closure;
use WPEmerge\Exceptions\ConfigurationException;
use WPEmerge\Helpers\Handler;
use WPEmerge\Helpers\HandlerFactory;
use WPEmerge\Requests\RequestInterface;
use WPEmerge\Routing\Conditions\ConditionFactory;
use WPEmerge\Routing\Conditions\ConditionInterface;
use WPEmerge\Routing\Conditions\UrlableInterface;
use WPEmerge\Support\Arr;

/**
 * Provide routing for site requests (i.e. all non-api requests).
 */
class Router implements HasRoutesInterface {
	use HasRoutesTrait;

	/**
	 * Condition factory.
	 */
	protected ConditionFactory $condition_factory;

	/**
	 * Handler factory.
	 */
	protected HandlerFactory $handler_factory;

	/**
	 * Group stack.
	 *
	 * @var array<array<string, mixed>>
	 */
	protected array $group_stack = [];

	/**
	 * Current active route.
	 */
	protected ?RouteInterface $current_route = null;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 * @param ConditionFactory $condition_factory
	 * @param HandlerFactory   $handler_factory
	 */
	public function __construct( ConditionFactory $condition_factory, HandlerFactory $handler_factory ) {
		$this->condition_factory = $condition_factory;
		$this->handler_factory = $handler_factory;
	}

	/**
	 * Get the current route.
	 *
	 * @return RouteInterface
	 */
	public function getCurrentRoute(): ?RouteInterface {
		return $this->current_route;
	}

	/**
	 * Set the current route.
	 *
	 * @param  RouteInterface $current_route
	 * @return void
	 */
	public function setCurrentRoute( RouteInterface $current_route ): void {
		$this->current_route = $current_route;
	}

	/**
	 * Merge the methods attribute combining values.
	 *
	 * @param  string[] $old
	 * @param  string[] $new
	 * @return string[]
	 */
	public function mergeMethodsAttribute( array $old, array $new ): array {
		return [...$old, ...$new];
	}

	/**
	 * Merge the condition attribute.
	 *
	 * @param  string|array|Closure|ConditionInterface|null $old
	 * @param  string|array|Closure|ConditionInterface|null $new
	 * @return ConditionInterface|string
	 */
	public function mergeConditionAttribute( mixed $old, mixed $new ): ConditionInterface|string|null {
		try {
			$condition = $this->condition_factory->merge( $old, $new );
		} catch ( ConfigurationException $e ) {
			throw new ConfigurationException( 'Route condition is not a valid route string or condition.' );
		}

		return $condition;
	}

	/**
	 * Merge the middleware attribute combining values.
	 *
	 * @param  string[] $old
	 * @param  string[] $new
	 * @return string[]
	 */
	public function mergeMiddlewareAttribute( array $old, array $new ): array {
		return [...$old, ...$new];
	}

	/**
	 * Merge the namespace attribute taking the latest value.
	 *
	 * @param  string $old
	 * @param  string $new
	 * @return string
	 */
	public function mergeNamespaceAttribute( string $old, string $new ): string {
		return ! empty( $new ) ? $new : $old;
	}

	/**
	 * Merge the handler attribute taking the latest value.
	 *
	 * @param  string|Closure $old
	 * @param  string|Closure $new
	 * @return string|Closure
	 */
	public function mergeHandlerAttribute( string|Closure $old, string|Closure $new ): string|Closure {
		return ! empty( $new ) ? $new : $old;
	}

	/**
	 * Merge the handler attribute taking the latest value.
	 *
	 * @param  callable|null $old
	 * @param  callable|null $new
	 * @return string|Closure
	 */
	public function mergeQueryAttribute( ?callable $old, ?callable $new ): ?callable {
		if ( $new === null ) {
			return $old;
		}

		if ( $old === null ) {
			return $new;
		}

		return function ( $query_vars ) use ( $old, $new ) {
			return call_user_func( $new, call_user_func( $old, $query_vars ) );
		};
	}

	/**
	 * Merge the name attribute combining values with a dot.
	 *
	 * @param  string $old
	 * @param  string $new
	 * @return string
	 */
	public function mergeNameAttribute( string $old, string $new ): string {
		$name = implode( '.', array_filter( [$old, $new] ) );

		// Trim dots.
		$name = preg_replace( '/^\.+|\.+$/', '', $name );

		// Reduce multiple dots to a single one.
		$name = preg_replace( '/\.{2,}/', '.', $name );

		return $name;
	}

	/**
	 * Merge attributes into route.
	 *
	 * @param  array<string, mixed> $old
	 * @param  array<string, mixed> $new
	 * @return array<string, mixed>
	 */
	public function mergeAttributes( array $old, array $new ): array {
		return [
			'methods' => $this->mergeMethodsAttribute(
				(array) Arr::get( $old, 'methods', [] ),
				(array) Arr::get( $new, 'methods', [] )
			),

			'condition' => $this->mergeConditionAttribute(
				Arr::get( $old, 'condition', null ),
				Arr::get( $new, 'condition', null )
			),

			'middleware' => $this->mergeMiddlewareAttribute(
				(array) Arr::get( $old, 'middleware', [] ),
				(array) Arr::get( $new, 'middleware', [] )
			),

			'namespace' => $this->mergeNamespaceAttribute(
				Arr::get( $old, 'namespace', '' ),
				Arr::get( $new, 'namespace', '' )
			),

			'handler' => $this->mergeHandlerAttribute(
				Arr::get( $old, 'handler', '' ),
				Arr::get( $new, 'handler', '' )
			),

			'query' => $this->mergeQueryAttribute(
				Arr::get( $old, 'query', null ),
				Arr::get( $new, 'query', null )
			),

			'name' => $this->mergeNameAttribute(
				Arr::get( $old, 'name', '' ),
				Arr::get( $new, 'name', '' )
			),
		];
	}

	/**
	 * Get the top group from the stack.
	 *
	 * @codeCoverageIgnore
	 * @return array<string, mixed>
	 */
	protected function getGroup(): array {
		return Arr::last( $this->group_stack, null, [] );
	}

	/**
	 * Add a group to the group stack, merging all previous attributes.
	 *
	 * @codeCoverageIgnore
	 * @param array<string, mixed> $group
	 * @return void
	 */
	protected function pushGroup( array $group ): void {
		$this->group_stack[] = $this->mergeAttributes( $this->getGroup(), $group );
	}

	/**
	 * Remove last group from the group stack.
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	protected function popGroup(): void {
		array_pop( $this->group_stack );
	}

	/**
	 * Create a route group.
	 *
	 * @codeCoverageIgnore
	 * @param  array<string, mixed> $attributes
	 * @param  Closure|string      $routes Closure or path to file.
	 * @return void
	 */
	public function group( array $attributes, Closure|string $routes ): void {
		$this->pushGroup( $attributes );

		if ( is_string( $routes ) ) {
			/** @noinspection PhpIncludeInspection */
			/** @codeCoverageIgnore */
			require_once $routes;
		} else {
			$routes();
		}

		$this->popGroup();
	}

	/**
	 * Make a route condition.
	 *
	 * @param  mixed              $condition
	 * @return ConditionInterface
	 */
	protected function routeCondition( mixed $condition ): ConditionInterface {
		if ( $condition === null ) {
			throw new ConfigurationException( 'No route condition specified. Did you miss to call url() or where()?' );
		}

		if ( ! $condition instanceof ConditionInterface ) {
			$condition = $this->condition_factory->make( $condition );
		}

		return $condition;
	}

	/**
	 * Make a route handler.
	 *
	 * @codeCoverageIgnore
	 * @param  string|Closure|null $handler
	 * @param  string              $namespace
	 * @return Handler
	 */
	protected function routeHandler( string|Closure|null $handler, string $namespace ): Handler {
		if ( $handler === null ) {
			throw new ConfigurationException( 'No route handler specified. Did you miss to call handle()?' );
		}

		return $this->handler_factory->make( $handler, '', $namespace );
	}

	/**
	 * Make a route.
	 *
	 * @param  array<string, mixed> $attributes
	 * @return RouteInterface
	 */
	public function route( array $attributes ): RouteInterface {
		$attributes = $this->mergeAttributes( $this->getGroup(), $attributes );
		$attributes = [
			...$attributes,
			'condition' => $this->routeCondition( $attributes['condition'] ),
			'handler' => $this->routeHandler( $attributes['handler'], $attributes['namespace'] ),
		];

		if ( empty( $attributes['methods'] ) ) {
			throw new ConfigurationException(
				'Route does not have any assigned request methods. ' .
				'Did you miss to call get() or post() on your route definition, for example?'
			);
		}

		return (new Route())->attributes( $attributes );
	}

	/**
	 * Assign and return the first satisfied route (if any) as the current one for the given request.
	 *
	 * @param  RequestInterface    $request
	 * @return RouteInterface|null
	 */
	public function execute( RequestInterface $request ): ?RouteInterface {
		$routes = $this->getRoutes();

		foreach ( $routes as $route ) {
			if ( $route->isSatisfied( $request ) ) {
				$this->setCurrentRoute( $route );
				return $route;
			}
		}

		return null;
	}

	/**
	 * Get the url for a named route.
	 *
	 * @param  string $name
	 * @param  array  $arguments
	 * @return string
	 */
	public function getRouteUrl( string $name, array $arguments = [] ): string {
		$routes = $this->getRoutes();

		foreach ( $routes as $route ) {
			if ( $route->getAttribute( 'name' ) !== $name ) {
				continue;
			}

			$condition = $route->getAttribute( 'condition' );

			if ( ! $condition instanceof UrlableInterface ) {
				throw new ConfigurationException(
					'Route condition is not resolvable to a URL.'
				);
			}

			return $condition->toUrl( $arguments );
		}

		throw new ConfigurationException( "No route registered with the name \"$name\"." );
	}
}
