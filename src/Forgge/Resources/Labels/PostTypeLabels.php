<?php

namespace WPEmerge\Forgge\Resources\Labels;

trait PostTypeLabels {
    use CommonLabels;

    /**
	 * Get the post type registration labels.
     *
     * @param string $singular Singular label.
     * @param string $plural   Plural label.
     * @param array $defaults  Defaults labels
     * @return array<string, string>
     */
    protected static function getLabels(string $singular, string $plural, bool $is_female = false, array $defaults = []): array {
        $singular_lower = strtolower($singular);
        $plural_lower = strtolower($plural);
        $is_female = (int) $is_female;

        $labels = [
            /* translators: %s label singular. */
            'add_new' => sprintf( __( 'Add %s', 'forgge' ), $singular ),
            'new_item' => sprintf(
                /* translators: %s label singular. */
                _n( 'New %s', 'New %s', $is_female, 'forgge' ),
                $singular
            ),
            /* translators: %s label singular. */
            'view_items' => sprintf( __( 'View %s', 'forgge' ), $singular ),
            'not_found_in_trash' => sprintf(
                /* translators: %s label plural lower. */
                _n( 'No %s found in Trash.', 'No %s found in Trash.', $is_female, 'forgge' ),
                $plural_lower
            ),
            /* translators: %s label plural. */
            'archives' => sprintf( __( '%s Archives', 'forgge' ), $plural) ,
            'attributes' => sprintf(
                /* translators: %s label singular. */
                _n( '%s Attributes', '%s Attributes', $is_female, 'forgge' ),
                $singular
            ),
            'insert_into_item' => sprintf(
                /* translators: %s label singular lower. */
                _n( 'Insert into %s', 'Insert into %s', $is_female, 'forgge' ),
                $singular_lower
            ),
            'uploaded_to_this_item' => sprintf(
                /* translators: %s label singular lower. */
                _n( 'Uploaded to this %s', 'Uploaded to this %s', $is_female, 'forgge' ),
                $singular_lower
            ),
            /* translators: %s label plural lower. */
            'filter_items_list' => sprintf( __( 'Filter %s list', 'forgge'), $plural_lower ),
            'item_published' => sprintf(
                /* translators: %s label singular. */
                _n( '%s published.', '%s published.', $is_female, 'forgge' ),
                $singular
            ),
            'item_published_privately' => sprintf(
                /* translators: %s label singular. */
                _n( '%s published privately.', '%s published privately.', $is_female, 'forgge' ),
                $singular
            ),
            'item_reverted_to_draft' => sprintf(
                /* translators: %s label singular. */
                _n( '%s reverted to draft.', '%s reverted to draft.', $is_female, 'forgge' ),
                $singular
            ),
            'item_trashed' => sprintf(
                /* translators: %s label singular. */
                _n( '%s trashed.', '%s trashed.', $is_female, 'forgge' ),
                $singular
            ),
            'item_scheduled' => sprintf(
                /* translators: %s label singular. */
                _n( '%s scheduled.', '%s scheduled.', $is_female, 'forgge' ),
                $singular
            ),
            'item_updated' => sprintf(
                /* translators: %s label singular. */
                _n( '%s updated.', '%s updated.', $is_female, 'forgge' ),
                $singular
            ),
        ];
        $labels = array_merge(
            $labels,
            self::getCommonLabels(
                $singular,
                $singular_lower,
                $plural,
                $plural_lower,
                (int) $is_female
            )
        );

        return array_merge($labels, $defaults);
    }
}
