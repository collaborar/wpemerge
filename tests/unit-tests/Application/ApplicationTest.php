<?php

namespace WPEmergeTests\Application;

use Exception;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Mockery;
use stdClass;
use WPEmerge\Application\Application;
use WPEmerge\Kernels\HttpKernelInterface;
use WPEmergeTestTools\TestCase;

/**
 * @coversDefaultClass \WPEmerge\Application\Application
 */
class ApplicationTest extends TestCase {
	public $container;

	public $subject;

	public function set_up() {
		$this->container = new Container();
		$this->subject = new Application( $this->container, false );
	}

	public function tear_down() {
		Mockery::close();

		unset( $this->container );
		unset( $this->subject );
	}

	/**
	 * @covers ::__construct
	 */
	public function testConstruct() {
		$container = new Container();
		$subject = new Application( $container );
		$this->assertSame( $container, $subject->container() );
	}

	/**
	 * @covers ::isBootstrapped
	 * @covers ::bootstrap
	 */
	public function testIsBootstrapped() {
		$this->assertEquals( false, $this->subject->isBootstrapped() );
		$this->subject->bootstrap( [], false );
		$this->assertEquals( true, $this->subject->isBootstrapped() );
	}

	/**
	 * @covers ::bootstrap
	 */
	public function testBootstrap_CalledMultipleTimes_ThrowException() {
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'already bootstrapped' );
		$this->subject->bootstrap( [], false );
		$this->subject->bootstrap( [], false );
	}

	/**
	 * @covers ::bootstrap
	 * @covers ::loadServiceProviders
	 */
	public function testBootstrap_RegisterServiceProviders() {
		$this->subject->bootstrap( [
			'providers' => [
				ApplicationTestServiceProviderMock::class,
			],
		], false );

		$this->subject->resolve( 'test.service' );

		$this->assertTrue( true );
	}

	/**
	 * @covers ::bootstrap
	 */
	public function testBootstrap_RunKernel() {
		$this->subject->bootstrap( [
			'providers' => [
				ApplicationTestKernelServiceProviderMock::class,
			],
		], true );

		$this->assertTrue( true );
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve_NonexistentKey_ReturnNull() {
		$expected = null;
		$container_key = 'nonexistentcontainerkey';

		$this->subject->bootstrap( [], false );
		$this->assertSame( $expected, $this->subject->resolve( $container_key ) );
	}

	/**
	 * @covers ::resolve
	 */
	public function testResolve_ExistingKey_IsResolved() {
		$expected = 'foobar';
		$container_key = 'test';

		$this->subject->bootstrap( [], false );
		$this->subject->container()->addShared( $container_key, fn () => $expected );

		$this->assertSame( $expected, $this->subject->resolve( $container_key ) );
	}
}

#[\AllowDynamicProperties]
class ApplicationTestServiceProviderMock extends AbstractServiceProvider implements BootableServiceProviderInterface {
	public function __construct() {
		$this->mock = Mockery::mock();
		$this->mock->shouldReceive( 'boot' )
			->once();
		$this->mock->shouldReceive( 'register' )
			->once();
	}

	public function provides( string $id ): bool {
		return $id === 'test.service';
	}

	public function boot(): void {
		$this->mock->boot();
	}

	public function register(): void {
		$this->mock->register();
		$this->getContainer()->addShared( 'test.service', fn () => new stdClass() );
	}
}

#[\AllowDynamicProperties]
class ApplicationTestKernelServiceProviderMock extends AbstractServiceProvider {
	public function provides( string $id ): bool {
		return $id === HttpKernelInterface::class;
	}

	public function register(): void {
		$mock = Mockery::mock( HttpKernelInterface::class );

		$mock->shouldReceive( 'bootstrap' )
			->once();

		$this->getContainer()->addShared( HttpKernelInterface::class, fn () => $mock, true );
	}
}
