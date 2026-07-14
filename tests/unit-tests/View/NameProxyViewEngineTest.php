<?php

namespace WPEmergeTests\View;

use League\Container\Container;
use Mockery;
use WPEmerge\Application\Application;
use WPEmerge\View\NameProxyViewEngine;
use WPEmerge\View\PhpViewEngine;
use WPEmerge\View\ViewNotFoundException;
use WPEmergeTestTools\TestCase;

/**
 * @coversDefaultClass \WPEmerge\View\NameProxyViewEngine
 */
class NameProxyViewEngineTest extends TestCase {
	public $container;

	public $app;

	public function set_up() {
		$this->container = new Container();
		$this->app = new Application( $this->container );
		$this->app->bootstrap( [], false );
	}

	public function tear_down() {
		unset( $this->container );
		unset( $this->app );
	}

	/**
	 * @covers ::__construct
	 * @covers ::getBindings
	 */
	public function testConstruct_Bindings_Accepted() {
		$expected = ['.foo' => 'foo', '.bar' => 'bar'];

		$subject = new NameProxyViewEngine( $this->app, $expected );

		$this->assertEquals( $expected, $subject->getBindings() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::getDefaultBinding
	 */
	public function testConstruct_Default_Accepted() {
		$expected = 'foo';

		$subject = new NameProxyViewEngine( $this->app, [], $expected );

		$this->assertEquals( $expected, $subject->getDefaultBinding() );
	}

	/**
	 * @covers ::__construct
	 * @covers ::getDefaultBinding
	 */
	public function testConstruct_EmptyDefault_Ignored() {
		$subject = new NameProxyViewEngine( $this->app, [], '' );

		$this->assertEquals( PhpViewEngine::class, $subject->getDefaultBinding() );
	}

	/**
	 * @covers ::getBindingForFile
	 */
	public function testGetBindingForFile() {
		$subject = new NameProxyViewEngine(
			$this->app,
			[
				'.blade.php' => 'blade',
				'.twig.php' => 'twig',
			],
			'default'
		);

		$this->assertEquals( 'blade', $subject->getBindingForFile( 'test.blade.php' ) );
		$this->assertEquals( 'twig', $subject->getBindingForFile( 'test.twig.php' ) );
		$this->assertEquals( 'default', $subject->getBindingForFile( 'test.php' ) );
	}

	/**
	 * @covers ::exists
	 */
	public function testExists() {
		$view = 'foo';
		$this->container->addShared( 'engine_mockup', function () use ( $view ) {
			$mock = Mockery::mock();

			$mock->shouldReceive( 'exists' )
				->with( $view )
				->andReturn( true )
				->ordered();

			return $mock;
		} );

		$subject = new NameProxyViewEngine( $this->app, [], 'engine_mockup' );

		$this->assertTrue( $subject->exists( $view ) );
	}

	/**
	 * @covers ::canonical
	 */
	public function testCanonical() {
		$view = 'foo';
		$expected = 'foo.php';

		$this->container->addShared( 'engine_mockup', function () use ( $view, $expected ) {
			$mock = Mockery::mock();

			$mock->shouldReceive( 'canonical' )
				->with( $view )
				->andReturn( $expected )
				->ordered();

			return $mock;
		} );

		$subject = new NameProxyViewEngine( $this->app, [], 'engine_mockup' );

		$this->assertEquals( $expected, $subject->canonical( $view ) );
	}

	/**
	 * @covers ::make
	 */
	public function testMake() {
		$view = 'file.php';
		$result = 'foobar';

		$this->container->addShared( 'engine_mockup', function () use ( $view, $result ) {
			$mock = Mockery::mock();

			$mock->shouldReceive( 'exists' )
				->with( $view )
				->andReturn( true );

			$mock->shouldReceive( 'make' )
				->with( [$view] )
				->andReturn( $result );

			return $mock;
		} );

		$subject = new NameProxyViewEngine( $this->app, [], 'engine_mockup' );

		$this->assertEquals( $result, $subject->make( [$view] ) );
	}

	/**
	 * @covers ::make
	 */
	public function testMake_NoView_EmptyString() {
		$view = '';

		$this->container->addShared( 'engine_mockup', function () use ( $view ) {
			$mock = Mockery::mock();

			$mock->shouldReceive( 'exists' )
				->with( $view )
				->andReturn( false );

			return $mock;
		} );

		$subject = new NameProxyViewEngine( $this->app, [], 'engine_mockup' );

		$this->expectException( ViewNotFoundException::class );
		$this->expectExceptionMessage( 'View not found' );
		$subject->make( [$view] );
	}
}
