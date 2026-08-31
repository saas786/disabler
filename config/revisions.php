<?php

/**
 * Revisions defaults.
 *
 * The config tier of the resolution order: what a setting resolves to when
 * nothing is stored and no preset overrides it.
 *
 * The per-post-type `revisions_limit_{$type}` keys are deliberately absent.
 * They were once defined here as closures calling wp_revisions_to_keep(), to
 * report what WordPress would keep on its own. That was both recursive and
 * redundant:
 *
 *   - Recursive, because the only reader of this tier is the
 *     'wp_revisions_to_keep' filter in Optimize\Revisions, and calling
 *     wp_revisions_to_keep() from inside it re-fires that same filter. The two
 *     called each other until the stack was exhausted.
 *
 *   - Redundant, because the $num the filter receives already IS
 *     wp_revisions_to_keep()'s value. The closure recomputed its own argument,
 *     and the filter returns $num unchanged whenever no limit is stored.
 *
 * With the keys gone, setting() falls back to the '' passed at the call site,
 * which the filter reads as "not overridden" -- the same outcome, minus the
 * recursion. The text control renders an empty box, matching its own
 * description: leave the field empty for default behavior.
 */

return [ 'disable_revisions' => [ 'no' ] ];
