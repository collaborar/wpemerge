<?php

namespace WPEmerge\Forgge\Resources;

abstract class PostType {
	use Labels\PostTypeLabels;

	/**
	 * The model the post type corresponds to.
	 *
	 * @var class-string
	 */
	protected static string $model;

	/**
	 * The post type name.
	 *
	 * @var string
	 */
	protected static string $name;

	/**
	 * The capability type.
	 *
	 * @var string|array
	 */
	protected static string|array $capability_type;

	/**
	 * The built in post types.
	 *
	 * @var array
	 */
	protected const BUILT_POST_TYPES = [
		'post',
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'changeset',
		'wp_template',
		'wp_template_part'
	];

	/**
	 * Bootstrap the post type.
	 *
	 * @return void
	 */
	public static function bootstrap(): void {
		static::maybeRegister();
	}

	/**
	 * Maybe register the post type.
	 *
	 * @return void
	 */
	protected static function maybeRegister(): void {
		if ( ! static::canRegister() ) {
			return;
		}

		// Ensure capability type have singular and plural values.
		if ( is_string( static::$capability_type ) ) {
			static::$capability_type = [
				static::$capability_type,
				static::$capability_type.'s'
			];
		}

		add_action( 'init', [static::class, 'register'] );
		add_filter( 'bulk_post_updated_messages', [static::class, 'getUpdatedMessages'], 10, 2 );
	}

	/**
	 * Determine if the post type can be registered.
	 *
	 * @return bool
	 */
	protected static function canRegister(): bool {
		return ! in_array( static::$name, self::BUILT_POST_TYPES );
	}

	/**
	 * Register the post type.
	 *
	 * @return void
	 */
	final public static function register(): void {
		$defaults = [
			'public'       => true,
			'show_in_rest' => true,
			'capabilities' => static::capabilities(),
			'labels'       => self::getLabels( ...static::getModelLabels() )
		];
		$config = static::configure();

		// Maybe developer wants to fix some labels string, this is a hot-fix
		// to allow him to override default generated labels.
		//
		// e.g.:
		// By default the `item_link_description` label have the value "A link to a %s",
		// and depending of model label, gramatically it should be "A link to an %s".
		if ( isset( $config['labels'] ) && is_array( $config['labels'] ) ) {
			$config['labels'] = array_merge( $defaults['labels'], $config['labels'] );
		}

		$config = wp_parse_args( $config, $defaults );

		register_post_type( static::$name, $config );
	}

	/**
	 * Get the list of capabilities assossiated to the post type.
	 *
	 * @param array<string, string> $capabilities
	 * @return array
	 */
	public static function capabilities( array $capabilities = [] ): array {
		[ $singular, $plural ] = static::$capability_type;

		$defaults = [
			'edit_post'              => "edit_{$singular}",
			'read_post'              => "read_{$singular}",
			'delete_post'            => "delete_{$singular}",
			'edit_posts'             => "edit_{$plural}",
			'edit_others_posts'      => "edit_others_{$plural}",
			'publish_posts'          => "publish_{$plural}",
			'read_private_posts'     => "read_private_{$plural}",
			'delete_posts'           => "delete_{$plural}",
			'delete_private_posts'   => "delete_private_{$plural}",
			'delete_published_posts' => "delete_published_{$plural}",
			'delete_others_posts'    => "delete_others_{$plural}",
			'edit_private_posts'     => "edit_private_{$plural}",
			'edit_published_posts'   => "edit_published_{$plural}",
			'create_posts'           => "edit_{$plural}",
		];

		return wp_parse_args( $capabilities, $defaults );
	}

	final public static function getUpdatedMessages( array $messages, array $counts ): array {
		[
			'singular' => $singular,
			'plural'   => $plural,
		] = static::getModelLabels();
		$singular = strtolower( $singular );
		$plural = strtolower( $plural );

		$messages[ static::$name ] = [
			'updated'   => _n(
				'%s ' . $singular . ' updated.',
				'%s ' . $plural . ' updated.',
				$counts['updated']
			),
			'locked'    => _n(
				'%s ' . $singular . ' not updated, somebody is editing it.',
				'%s ' . $plural . ' not updated, somebody is editing them.',
				$counts['locked']
			),
			'deleted'   => _n(
				'%s ' . $singular . ' permanently deleted.',
				'%s ' . $plural . ' permanently deleted.',
				$counts['deleted']
			),
			'trashed'   => _n(
				'%s ' . $singular . ' moved to the Trash.',
				'%s ' . $plural . ' moved to the Trash.',
				$counts['trashed']
			),
			'untrashed' => _n(
				'%s ' . $singular . ' restored from the Trash.',
				'%s ' . $plural . ' restored from the Trash.',
				$counts['untrashed']
			),
		];

		return $messages;
	}

	/**
	 * Configure the post type register args.
	 *
	 * @return array
	 */
	abstract protected static function configure(): array;

	/**
	 * Get the model labels to build the post type labels.
	 *
	 * @return array<string, string>
	 */
	abstract protected static function getModelLabels(): array;
}
