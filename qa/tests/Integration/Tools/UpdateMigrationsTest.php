<?php

/**
 * The migration routines older than 4.0.5.
 *
 * UpdateRoutineTest covers 4.0.5 end to end. These four run before it, once
 * per site, during an upgrade nobody is watching -- and when one of them is
 * wrong the evidence is a settings row that has already been overwritten.
 * There is no second chance to observe the bug, which is the whole argument
 * for pinning them here.
 *
 * Each routine is called directly against a real option row rather than
 * through the scheduler, so what is being checked is the transformation, not
 * ActionScheduler.
 */

declare(strict_types = 1);

use function HBP\Disabler\Tools\Update\update_3_0_0_options;
use function HBP\Disabler\Tools\Update\update_3_0_3_options;
use function HBP\Disabler\Tools\Update\update_4_0_0_RC_2_options;
use function HBP\Disabler\Tools\Update\update_4_0_2_options;

// Not autoloaded: PluginInstall includes this file only when a scheduled
// callback fires, so calling one directly means loading them first. Same
// reason UpdateRoutineTest does it.
require_once plugin_path( 'inc/Tools/Update/bootstrap-autoload.php' );

it( 'does nothing when there is no legacy 3.0.0 option', function (): void {
    // The bail-early branch. On a fresh install this is the only path taken,
    // so it needs to leave everything alone rather than write a default.
    delete_option( 'disabler_options' );
    delete_option( 'disabler_autop' );

    update_3_0_0_options();

    expect( get_option( 'disabler_options', 'absent' ) )->toBe( 'absent' );
} );

it( 'collects the scattered 3.0.0 options into one row', function (): void {
    // Before 3.0.0 each control was its own option row. The routine gathers
    // the ones that exist into a single array.
    update_option( 'disabler_options', [ 'legacy' => true ] );
    update_option( 'disabler_autop', '1' );
    update_option( 'disabler_xmlrpc', '1' );

    update_3_0_0_options();

    $options = get_option( 'disabler_options' );

    expect( $options )->toBeArray()
        ->and( $options['disabler_autop'] )->toBe( 1 )
        ->and( $options['disabler_xmlrpc'] )->toBe( 1 );
} );

it( 'normalises every key it knows about, set or not', function (): void {
    // The routine gathers whichever legacy rows exist, then writes all ten
    // keys unconditionally -- absent ones become 0 rather than being left
    // out. So the output shape is fixed regardless of what the site had,
    // which is what makes the settings screen safe to read afterwards.
    update_option( 'disabler_options', [ 'legacy' => true ] );
    update_option( 'disabler_autop', '1' );

    delete_option( 'disabler_revisions' );

    update_3_0_0_options();

    $options = get_option( 'disabler_options' );

    expect( $options['disabler_autop'] )->toBe( 1 )
        ->and( $options['disabler_revisions'] )->toBe( 0 )
        ->and( $options['disabler_smartquotes'] )->toBe( 0 );
} );

it( 'coerces a truthy legacy value to exactly 1', function (): void {
    // absint( $v ) === 1, so '1' becomes 1 and anything else becomes 0 --
    // including '2', which a hand-edited row could hold.
    update_option( 'disabler_options', [ 'legacy' => true ] );
    update_option( 'disabler_xmlrpc', '1' );
    update_option( 'disabler_norss', '2' );

    update_3_0_0_options();

    $options = get_option( 'disabler_options' );

    expect( $options['disabler_xmlrpc'] )->toBe( 1 )
        ->and( $options['disabler_norss'] )->toBe( 0 );
} );

it( 'removes the legacy option rows it consumed', function (): void {
    // The cleanup at the end. Leaving them behind would mean a later run
    // reads stale values that the new row has already superseded.
    update_option( 'disabler_options', [ 'legacy' => true ] );
    update_option( 'disabler_autop', '1' );
    update_option( 'disabler_xmlrpc', '1' );

    update_3_0_0_options();

    expect( get_option( 'disabler_autop', 'gone' ) )->toBe( 'gone' )
        ->and( get_option( 'disabler_xmlrpc', 'gone' ) )->toBe( 'gone' );
} );

it( 'does nothing when there is no legacy 4.0.2 option', function (): void {
    delete_option( 'hbp_disabler_settings' );

    update_4_0_2_options();

    expect( get_option( 'hbp_disabler_settings', 'absent' ) )->toBe( 'absent' );
} );

it( 'moves the autosave setting from backend to editor', function (): void {
    // The rename this routine exists for, and the one value conversion in
    // it: an integer 1 becomes the string 'yes'.
    update_option( 'hbp_disabler_settings', [ 'backend_disable_autosave' => 1 ] );

    update_4_0_2_options();

    expect( get_option( 'hbp_disabler_settings' )['editor_disable_autosave'] )->toBe( 'yes' );
} );

it( 'converts a disabled autosave setting to no', function (): void {
    update_option( 'hbp_disabler_settings', [ 'backend_disable_autosave' => 0 ] );

    update_4_0_2_options();

    expect( get_option( 'hbp_disabler_settings' )['editor_disable_autosave'] )->toBe( 'no' );
} );

it( 'moves the three frontend editor settings across', function (): void {
    // These keep integers rather than becoming yes/no, so the two conversion
    // styles in one routine are both pinned.
    update_option( 'hbp_disabler_settings', [
        'frontend_disable_texturization' => 1,
        'frontend_disable_capital_p'     => 1,
        'frontend_disable_autop'         => 0,
    ] );

    update_4_0_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    expect( $options['editor_disable_texturization'] )->toBe( 1 )
        ->and( $options['editor_disable_capital_p'] )->toBe( 1 )
        ->and( $options['editor_disable_autop'] )->toBe( 0 );
} );

it( 'keeps every setting it does not migrate', function (): void {
    // The routine used to build its row from scratch and write it over the
    // whole option, discarding anything it did not rename. It merges now.
    update_option( 'hbp_disabler_settings', [
        'backend_disable_autosave'  => 1,
        'xmlrpc_disable_xmlrpc'     => 'completely',
        'feeds_disable_feed_global' => 1,
    ] );

    update_4_0_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    expect( $options['editor_disable_autosave'] )->toBe( 'yes' )
        ->and( $options['xmlrpc_disable_xmlrpc'] )->toBe( 'completely' )
        ->and( $options['feeds_disable_feed_global'] )->toBe( 1 );
} );

it( 'removes the legacy spellings it consumed', function (): void {
    // The merge would otherwise leave both names in the row, and a later
    // routine reading the old one would see a value that is no longer
    // authoritative.
    update_option( 'hbp_disabler_settings', [
        'backend_disable_autosave'       => 1,
        'frontend_disable_texturization' => 1,
        'frontend_disable_capital_p'     => 1,
        'frontend_disable_autop'         => 1,
    ] );

    update_4_0_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    expect( $options )->not->toHaveKey( 'backend_disable_autosave' )
        ->and( $options )->not->toHaveKey( 'frontend_disable_texturization' )
        ->and( $options )->not->toHaveKey( 'frontend_disable_capital_p' )
        ->and( $options )->not->toHaveKey( 'frontend_disable_autop' );
} );

it( 'leaves an unrecognised row untouched', function (): void {
    // Nothing to rename, so nothing changes -- where this used to empty the
    // row completely.
    update_option( 'hbp_disabler_settings', [ 'something_else' => 1 ] );

    update_4_0_2_options();

    expect( get_option( 'hbp_disabler_settings' ) )->toBe( [ 'something_else' => 1 ] );
} );

it( 'bails from 3.0.3 when the newer row is absent', function (): void {
    // The guard is unusual and worth pinning: the routine reads
    // disabler_settings first and returns when it is missing, even though
    // disabler_settings is what it writes. Its presence is being used as a
    // marker that the site reached this version at all.
    delete_option( 'disabler_settings' );
    update_option( 'disabler_options', [ 'disabler_autop' => 1 ] );

    update_3_0_3_options();

    expect( get_option( 'disabler_settings', 'absent' ) )->toBe( 'absent' );
} );

it( 'bails from 3.0.3 when there is nothing to migrate', function (): void {
    update_option( 'disabler_settings', [ 'marker' => 1 ] );
    delete_option( 'disabler_options' );

    update_3_0_3_options();

    // Left exactly as it was, rather than replaced with a normalised row.
    expect( get_option( 'disabler_settings' ) )->toBe( [ 'marker' => 1 ] );
} );

it( 'renames the 3.0.3 keys to their descriptive names', function (): void {
    // disabler_autop becomes autop_disabled, and so on for all ten. The old
    // names said which plugin wrote them; the new ones say what they do.
    update_option( 'disabler_settings', [ 'marker' => 1 ] );
    update_option( 'disabler_options', [
        'disabler_autop'       => 1,
        'disabler_xmlrpc'      => 1,
        'disabler_smartquotes' => 1,
        'disabler_norss'       => 0,
    ] );

    update_3_0_3_options();

    $options = get_option( 'disabler_settings' );

    expect( $options['autop_disabled'] )->toBe( 1 )
        ->and( $options['xmlrpc_disabled'] )->toBe( 1 )
        ->and( $options['texturization_disabled'] )->toBe( 1 )
        ->and( $options['rss_feed_disabled'] )->toBe( 0 )
        ->and( $options )->not->toHaveKey( 'disabler_autop' );
} );

it( 'cleans up after the 3.0.3 migration', function (): void {
    update_option( 'disabler_settings', [ 'marker' => 1 ] );
    update_option( 'disabler_options', [ 'disabler_autop' => 1 ] );
    update_option( 'disabler_plugin_version', '3.0.2' );

    update_3_0_3_options();

    expect( get_option( 'disabler_options', 'gone' ) )->toBe( 'gone' )
        ->and( get_option( 'disabler_plugin_version', 'gone' ) )->toBe( 'gone' );
} );

it( 'does nothing in RC2 when there is no previous row', function (): void {
    delete_option( 'disabler_settings' );
    delete_option( 'hbp_disabler_settings' );

    update_4_0_0_RC_2_options();

    expect( get_option( 'hbp_disabler_settings', 'absent' ) )->toBe( 'absent' );
} );

it( 'moves settings into their section prefixed names', function (): void {
    // RC2 is where the flat namespace gained sections, so this is the rename
    // that every later routine and the settings screen depend on.
    update_option( 'disabler_settings', [
        'texturization_disabled' => 1,
        'capital_p_disabled'     => 0,
        'selfping_disabled'      => 1,
        'autosave_disabled'      => 1,
        'hide_wp_version'        => 1,
    ] );

    update_4_0_0_RC_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    expect( $options['frontend_disable_texturization'] )->toBe( 1 )
        ->and( $options['frontend_disable_capital_p'] )->toBe( 0 )
        ->and( $options['backend_disable_self_ping'] )->toBe( 1 )
        ->and( $options['backend_disable_autosave'] )->toBe( 1 )
        ->and( $options['privacy_disable_wp_generator'] )->toBe( 1 );
} );

it( 'fans one rss setting out across all ten feed controls', function (): void {
    // The single old switch became ten. Anything short of all ten means a
    // site that had feeds off silently gets some of them back.
    update_option( 'disabler_settings', [ 'rss_feed_disabled' => 1 ] );

    update_4_0_0_RC_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    $feeds = [
        'feeds_disable_feed_global',
        'feeds_disable_feed_global_comments',
        'feeds_disable_feed_post_comments',
        'feeds_disable_feed_authors',
        'feeds_disable_feed_post_types',
        'feeds_disable_feed_categories',
        'feeds_disable_feed_tags',
        'feeds_disable_feed_custom_taxonomies',
        'feeds_disable_feed_search',
        'feeds_disable_atom_rdf_feeds',
    ];

    foreach ( $feeds as $key ) {
        expect( $options[ $key ] )->toBe( 1 );
    }
} );

it( 'fans a disabled rss setting out as zeroes', function (): void {
    // The other direction, which the ternary makes easy to get wrong: a site
    // that had feeds on must not end up with ten controls turned off.
    update_option( 'disabler_settings', [ 'rss_feed_disabled' => 0 ] );

    update_4_0_0_RC_2_options();

    expect( get_option( 'hbp_disabler_settings' )['feeds_disable_feed_global'] )->toBe( 0 );
} );

it( 'turns the old xmlrpc switch into completely plus the jetpack allowlist', function (): void {
    // Two keys from one, and the allowlist is the interesting half: without
    // it, a site that disabled xmlrpc would also cut off Jetpack, which the
    // old single switch did not do.
    update_option( 'disabler_settings', [ 'xmlrpc_disabled' => 1 ] );

    update_4_0_0_RC_2_options();

    $options = get_option( 'hbp_disabler_settings' );

    expect( $options['xmlrpc_disable_xmlrpc'] )->toBe( 'completely' )
        ->and( $options['xmlrpc_xmlrpc_whitelist_jetpack_ips'] )->toBe( 1 );
} );

it( 'leaves xmlrpc alone when the old switch was off', function (): void {
    // Guarded by === 1 rather than by array_key_exists, so an off value
    // writes nothing at all and the new default applies.
    update_option( 'disabler_settings', [ 'xmlrpc_disabled' => 0 ] );

    update_4_0_0_RC_2_options();

    expect( get_option( 'hbp_disabler_settings' ) )->not->toHaveKey( 'xmlrpc_disable_xmlrpc' );
} );

it( 'turns the old revisions switch into an all selection', function (): void {
    // A boolean became a multi-select, so the value shape changes as well as
    // the name.
    update_option( 'disabler_settings', [ 'revisions_disabled' => 1 ] );

    update_4_0_0_RC_2_options();

    expect( get_option( 'hbp_disabler_settings' )['revisions_disable_revisions'] )->toBe( [ 'all' ] );
} );

it( 'cleans up after the RC2 migration', function (): void {
    update_option( 'disabler_settings', [ 'autop_disabled' => 1 ] );
    update_option( 'disabler_db_version', '3.0.3' );
    update_option( 'disabler_admin_notices', [ 'x' ] );

    update_4_0_0_RC_2_options();

    expect( get_option( 'disabler_settings', 'gone' ) )->toBe( 'gone' )
        ->and( get_option( 'disabler_db_version', 'gone' ) )->toBe( 'gone' )
        ->and( get_option( 'disabler_admin_notices', 'gone' ) )->toBe( 'gone' );
} );
