<?php

namespace WPEmergeTests\ServiceProviders;

use WPEmerge\Application\Configuration;
use WPEmergeTestTools\TestCase;

/**
 * @coversDefaultClass \WPEmerge\Application\Configuration
 */
class ExtendsConfigTraitTest extends TestCase {
	/**
	 * @covers ::extend
	 */
	public function testExtendConfig_ConfigNotSet_Default() {
		$config = new Configuration( [] );
		$key = 'foo';
		$default = 'bar';
		$expected = $default;

		$config->extend( $key, $default );

		$this->assertEquals( $expected, $config->get( $key ) );
	}

	/**
	 * @covers ::extend
	 */
	public function testExtendConfig_NotArrays_Replace() {
		$config = new Configuration( [
			'foo' => 'foo',
		] );
		$key = 'foo';
		$default = 'bar';
		$expected = 'foo';

		$config->extend( $key, $default );

		$this->assertEquals( $expected, $config->get( $key ) );
	}

	/**
	 * @covers ::extend
	 */
	public function testExtendConfig_Arrays_RecursiveReplace() {
		$config = new Configuration( [
			'foo' => [
				'foo' => 'foo',
				'bar' => 'bar',
				'baz' => [
					'foo' => 'foo',
				],
			],
		] );
		$key = 'foo';
		$default = [
			'bar' => 'foobarbaz',
			'baz' => [
				'bar' => 'bar',
			],
			'foobarbaz' => 'foobarbaz',
		];
		$expected = [
			'foo' => 'foo',
			'bar' => 'bar',
			'baz' => [
				'foo' => 'foo',
				'bar' => 'bar',
			],
			'foobarbaz' => 'foobarbaz',
		];

		$config->extend( $key, $default );

		$this->assertEquals( $expected, $config->get( $key ) );
	}

	/**
	 * @covers ::extend
	 */
	public function testExtendConfig_IndexedArray_Replace() {
		$config = new Configuration( [
			'first' => [
				'bar',
			],
			'second' => [
				'foobar' => [
					'barfoo',
					'barfoo',
				],
			],
			'third' => [],
		] );

		$key = 'first';
		$default = [
			'foo',
			'foo',
		];
		$expected = [
			'bar',
		];

		$config->extend( $key, $default );

		$this->assertEquals( $expected, $config->get( $key ) );

		$key = 'second';
		$default = [
			'foobar' => [
				'foobar',
			],
		];
		$expected = [
			'foobar' => [
				'barfoo',
				'barfoo',
			],
		];

		$config->extend( $key, $default );

		$this->assertEquals( $expected, $config->get( $key ) );
	}
}
