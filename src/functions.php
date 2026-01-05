<?php

if ( ! function_exists('join_paths') ) {
    /**
     * Join the given paths.
     *
     * @param string $base_path
     * @param string ...$paths
     */
    function join_paths( string $base_path, ...$paths ): string {
        foreach ( $paths as $index => $path ) {
            if ( empty( $path ) && $path !== '0' ) {
                unset( $paths[ $index ] );
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR . ltrim( $path, DIRECTORY_SEPARATOR );
            }
        }

        return $base_path . implode( '', $paths );
    }
}
