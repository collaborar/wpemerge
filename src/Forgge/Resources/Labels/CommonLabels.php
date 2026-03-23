<?php

namespace WPEmerge\Forgge\Resources\Labels;

trait CommonLabels {
	/**
	 * Get common labels shared between post type and taxonomy.
	 *
	 * @return array<string, string>
	 */
	protected static function getCommonLabels(
		string $singular,
		string $singular_lower,
		string $plural,
		string $plural_lower,
		int $is_female = 0
	): array {
		return [
			'name'               => $plural,
			'singular_name'      => $singular,
			/* translators: %s label plural. */
			'search_items'       => sprintf( __( 'Search %s', 'forgge' ), $plural ),
			/* translators: %s label plural. */
			'all_items'          => sprintf( _n( 'All %s', 'All %s', $is_female, 'forgge' ), $plural ),
			/* translators: %s label singular. */
			'parent_item_colon'  => sprintf( __( 'Parent %s:', 'forgge' ), $singular ),
			/* translators: %s label singular. */
			'edit_item'          => sprintf( __( 'Edit %s', 'forgge' ), $singular ),
			/* translators: %s label singular. */
			'view_item'          => sprintf( __( 'View %s', 'forgge' ), $singular ),
			/* translators: %s label singular. */
			'update_item'        => sprintf( __( 'Update %s', 'forgge' ), $singular ),
			'add_new_item'       => sprintf(
				/* translators: %s label singular. */
				_n( 'Add %s', 'Add %s', $is_female, 'forgge' ),
				$singular
			),
			'not_found'          => sprintf(
				/* translators: %s label plural lower. */
				_n( 'No %s found.', 'No %s found.', $is_female, 'forgge' ),
				$plural_lower
			),
			/* translators: %s label plural. */
			'items_list'         => sprintf( __( '%s list', 'forgge' ), $plural ),
			/* translators: %s label plural. */
			'items_list_navigation' => sprintf( __( '%s list navigation', 'forgge' ), $plural ),
			/* translators: %s label singular. */
			'item_link'          => sprintf( __( '%s Link', 'forgge' ), $singular ),
			// @todo Set right article `a` or `an` depending of the $singular.
			// for now we can allow developers to override labels on ::configure() method
			'item_link_description' => sprintf(
				/* translators: %s label singular lower. */
				_n( 'A link to a %s', 'A link to a %s', $is_female, 'forgge' ),
				$singular_lower
			),
		];
	}
}
