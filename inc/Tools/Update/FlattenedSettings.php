<?php

namespace HBP\Disabler\Tools\Update;

/**
 * Rewrites the old flat option into the dotted, nested shape that
 * HBP\Settings reads.
 *
 * The old screen stored one flat key per control, prefixed with its section:
 * `backend_disable_self_ping`. HBP\Settings addresses settings by dotted key
 * and stores them nested, so the same setting now lives at
 * `backend.disable_self_ping` -- `[ 'backend' => [ 'disable_self_ping' => … ] ]`
 * on disk.
 *
 * This is a rule, not a table. Some key families are unbounded: revisions
 * writes `revisions_revisions_limit_{$post_type}` for every registered post
 * type, so no fixed list of 59 keys could cover a real install. Splitting on
 * the known section prefix handles those the same as the rest.
 *
 * This only converts. Running it once is the update chain's job, which already
 * tracks a db version and already skips fresh installs -- a second flag option
 * here would be a second mechanism answering the same question.
 *
 * It lives beside the update routine rather than in a general namespace
 * because the 4.0.5 routine is its only caller and nothing at runtime has any
 * business converting an option shape that no longer ships.
 */
final class FlattenedSettings {
    /**
     * The section prefixes as of 4.0.4, the version this converts from.
     *
     * Frozen on purpose, and deliberately not derived from `config/tabs.php`.
     * That file lists the sections this plugin ships *now*; this one lists the
     * sections a user's stored option may contain. Drop a tab in some later
     * version and a derived list would quietly stop converting its keys,
     * stranding them flat forever on anyone upgrading from an older install.
     *
     * Order is irrelevant. No entry is a prefix of another, so the first match
     * is the only match -- alphabetical here for reading, and
     * FlattenedSettingsTest asserts the invariant so that editing this list
     * cannot silently reintroduce an ordering dependency.
     */
    private const SECTIONS = [
        'admin_bar',
        'backend',
        'editor',
        'feeds',
        'frontend',
        'media',
        'performance',
        'privacy',
        'restapi',
        'revisions',
        'tracking',
        'updates',
        'xmlrpc',
    ];

    /**
     * The flat array as its nested equivalent.
     *
     * Idempotent: a key already nested under its section is left alone, so a
     * half-finished run can be repeated without corrupting what it converted
     * the first time.
     *
     * Unrecognised keys are carried across untouched rather than dropped. A
     * key this does not understand is more likely a setting from a version
     * this code has not seen than junk, and silently deleting a user's data
     * is the one failure that cannot be undone.
     *
     * The single exception is a scalar stored under a bare section name,
     * which collides with the section the prefixed keys nest into. There the
     * settings win; see convert().
     *
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    public function convert( array $stored ): array {
        $nested = [];
        $strays = [];

        foreach ( $stored as $key => $value ) {
            $section = $this->section( (string) $key );

            if ( null === $section ) {
                $nested[ $key ] = $value;

                continue;
            }

            // A key that IS a section name, rather than one prefixed with it.
            // An array is a section already nested by an earlier partial run,
            // so merge it.
            //
            // A scalar is malformed: no version of this plugin ever wrote a
            // bare section name, and the name is already spoken for by the
            // section the prefixed keys nest into. Both cannot occupy the
            // slot.
            //
            // Set aside rather than written, so that the decision happens
            // below against finished sections. Writing it here would depend
            // on which keys the option happened to list first: ahead of the
            // prefixed ones it leaves a string where the line beneath expects
            // an array, behind them it overwrites a section already built.
            if ( $key === $section ) {
                if ( is_array( $value ) ) {
                    $nested[ $section ] = array_merge( $nested[ $section ] ?? [], $value );
                } else {
                    $strays[ $section ] = $value;
                }

                continue;
            }

            $nested[ $section ][ substr( (string) $key, strlen( $section ) + 1 ) ] = $value;
        }

        // Real settings win the slot. A stray only lands where its section is
        // otherwise unused, which is the case the old code did handle and the
        // one where keeping it costs nothing.
        //
        // Dropping the rest is the one deletion this class makes, and it is
        // narrower than it looks: an unrecognised key is still carried across
        // untouched above. This discards only a value whose own name says it
        // is a section while its type says it is not.
        foreach ( $strays as $section => $value ) {
            if ( ! array_key_exists( $section, $nested ) ) {
                $nested[ $section ] = $value;
            }
        }

        return $nested;
    }

    /**
     * The section a flat key belongs to, or null when it names none.
     */
    private function section( string $key ): ?string {
        foreach ( self::SECTIONS as $section ) {
            if ( $key === $section || str_starts_with( $key, $section . '_' ) ) {
                return $section;
            }
        }

        return null;
    }
}
