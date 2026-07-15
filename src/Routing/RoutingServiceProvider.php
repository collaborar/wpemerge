<?php
/**
 * @package   WPEmerge
 * @author    Atanas Angelov <hi@atanas.dev>
 * @copyright 2017-2019 Atanas Angelov
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0
 * @link      https://wpemerge.com/
 */

namespace WPEmerge\Routing;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use WPEmerge\Application\Application;
use WPEmerge\Application\Configuration;
use WPEmerge\Helpers\HandlerFactory;
use WPEmerge\Routing\Conditions\ConditionFactory;
use WPEmerge\ServiceProviders\ExtendsConfigTrait;
use WPEmerge\View\ViewService;

/**
 * Provide routing dependencies
 *
 * @codeCoverageIgnore
 */
class RoutingServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface {
	use ExtendsConfigTrait;

	/**
	 * Key=>Class dictionary of condition types
	 *
	 * @var array<string, string>
	 */
	protected static array $condition_types = [
		'url'           => Conditions\UrlCondition::class,
		'custom'        => Conditions\CustomCondition::class,
		'multiple'      => Conditions\MultipleCondition::class,
		'negate'        => Conditions\NegateCondition::class,
		'post_id'       => Conditions\PostIdCondition::class,
		'post_slug'     => Conditions\PostSlugCondition::class,
		'post_status'   => Conditions\PostStatusCondition::class,
		'post_template' => Conditions\PostTemplateCondition::class,
		'post_type'     => Conditions\PostTypeCondition::class,
		'query_var'     => Conditions\QueryVarCondition::class,
		'ajax'          => Conditions\AjaxCondition::class,
		'admin'         => Conditions\AdminCondition::class,
	];

	public function provides( string $id ): bool {
		return in_array( $id, [
			Router::class,
			ConditionFactory::class,
			RouteBlueprint::class,
		], true );
	}

	public function boot(): void {
		$config    = $this->getContainer()->get( Configuration::class );
		$namespace = $config->get( 'namespace', 'App\\' );

		$this->extendConfig( 'routes', [
			'web'   => [
				'definitions' => '',
				'attributes'  => [
					'middleware' => ['web'],
					'namespace'  => $namespace . 'Controllers\\Web\\',
					'handler'    => 'WPEmerge\\Controllers\\WordPressController@handle',
				],
			],
			'admin' => [
				'definitions' => '',
				'attributes'  => [
					'middleware' => ['admin'],
					'namespace'  => $namespace . 'Controllers\\Admin\\',
				],
			],
			'ajax'  => [
				'definitions' => '',
				'attributes'  => [
					'middleware' => ['ajax'],
					'namespace'  => $namespace . 'Controllers\\Ajax\\',
				],
			],
		] );

		$app = $this->getContainer()->get( Application::class );
		$app->alias( 'router', Router::class );
		$app->alias( 'route', RouteBlueprint::class );
		$app->alias( 'routeUrl', Router::class, 'getRouteUrl' );
	}

	public function register(): void {
		$c = $this->getContainer();

		$c->addShared( ConditionFactory::class, function () use ( $c ) {
			$conditionTypes = [
				...static::$condition_types,
				...$c->get( Configuration::class )->get( 'condition_types', [] ),
			];
			return new ConditionFactory( $conditionTypes );
		} );

		// Router and RouteBlueprint need explicit constructor arguments; League definitions do not autowire.
		$c->addShared( Router::class )->addArguments( [ ConditionFactory::class, HandlerFactory::class ] );

		// RouteBlueprint is a factory: new instance per resolve (like the old $container->factory())
		$c->add( RouteBlueprint::class )->addArguments( [ Router::class, ViewService::class ] );
	}
}
