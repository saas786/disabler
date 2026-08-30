<?php

/**
 * Every migration in sequence, on one option row.
 *
 * UpdateMigrationsTest checks each routine against the input it expects.
 * That is not the same as checking they compose: a real site upgrading from
 * an old version runs them one after another, each reading what the last
 * one wrote, and a rename that both sides get individually right can still
 * lose a value in the handover.
 *
 * The order and grouping are taken from PluginInstall::$db_updates, which is
 * the schedule the plugin actually runs. If a routine is added there and not
 * here, the last test in this file is what notices.
 *
 * This is also where the 4.0.2 overwrite shows its real cost. Run in
 * isolation it discards settings the routine does not know about; run in the
 * chain, those settings were put there by the routine immediately before it.
 */

declare(strict_types = 1);

use HBP\Disabler\Tools\Update\PluginInstall;
use function HBP\Disabler\setting;
use function HBP\Disabler\Tools\Update\update_3_0_0_options;
use function HBP\Disabler\Tools\Update\update_3_0_3_options;
use function HBP\Disabler\Tools\Update\update_4_0_0_RC_2_options;
use function HBP\Disabler\Tools\Update\update_4_0_2_options;
use function HBP\Disabler\Tools\Update\update_4_0_5_options;

require_once plugin_path( 'inc/Tools/Update/bootstrap-autoload.php' );

/**
 * A site as it looked before 3.0.0: one option row per control.
 *
 * @param bool $reached_3_0_3 Whether the site ever ran a 3.0.3-era version.
 */
function legacy_2x_site( bool $reached_3_0_3 = true ): void {
    // The marker row 3.0.0 checks for. Its contents are discarded
    // immediately -- only its existence matters.
    update_option( 'disabler_options', [ 'legacy' => true ] );

    // update_3_0_3_options() bails unless disabler_settings already exists,
    // and nothing earlier in the chain creates it -- see the halts-early
    // test below. A site that ran a 3.0.3-era version has it; that is the
    // state the rest of these tests describe.
    if ( $reached_3_0_3 ) {
        update_option( 'disabler_settings', [ 'marker' => 1 ] );
    }

    update_option( 'disabler_autop', '1' );
    update_option( 'disabler_xmlrpc', '1' );
    update_option( 'disabler_norss', '1' );
    update_option( 'disabler_revisions', '1' );
    update_option( 'disabler_capitalp', '1' );
    update_option( 'disabler_selfping', '1' );
    update_option( 'disabler_autosave', '1' );
    update_option( 'disabler_version', '1' );
    update_option( 'disabler_smartquotes', '1' );
}

/**
 * Run the chain in the order PluginInstall schedules it.
 */
function run_upgrade_chain(): void {
    update_3_0_0_options();
    update_3_0_3_options();
    update_4_0_0_RC_2_options();
    update_4_0_2_options();
    update_4_0_5_options();
}

it( 'halts after 3.0.0 on a site that never ran 3.0.3', function (): void {
    // Documenting real behaviour, and the reason every other test here seeds
    // a disabler_settings row.
    //
    // update_3_0_3_options() returns early unless disabler_settings exists.
    // update_3_0_0_options() writes disabler_options and never creates
    // disabler_settings, so a site coming straight from 2.x stops there: the
    // 3.0.0 routine normalises the old rows and nothing downstream ever
    // reads them. The settings screen then shows defaults.
    //
    // Whether such a site can still exist is a judgement call -- 3.0.3 is
    // old -- but the gap is in the code rather than in this file, and if it
    // is ever closed this test is what fails.
    legacy_2x_site( false );

    run_upgrade_chain();

    // Asserted on the two legacy rows rather than on the final settings.
    // update_4_0_5_options() runs regardless and writes a full nested row
    // built from resolved defaults, so its contents depend on process state
    // this test does not control -- a poor thing to pin, and not what this
    // test is about.
    //
    // These two say it precisely: 3.0.0 ran and normalised the old rows,
    // and disabler_settings was never created, which is exactly the
    // condition update_3_0_3_options() bails on. Everything after it reads
    // from rows that no longer exist.
    expect( get_option( 'disabler_options' ) )->toHaveKey( 'disabler_autop' )
        ->and( get_option( 'disabler_settings', 'absent' ) )->toBe( 'absent' );
} );

it( 'carries a disabled feed setting all the way through', function (): void {
    // One old switch, four renames and a fan-out later. RC.2 turns the old
    // rss switch into ten feed controls, and 4.0.2 now merges rather than
    // replaces, so all ten survive to the end of the chain.
    legacy_2x_site();

    run_upgrade_chain();

    expect( setting( 'feeds.disable_feed_global' ) )->toBe( 1 );
} );

it( 'carries the autop setting through every rename', function (): void {
    // disabler_autop, autop_disabled, frontend_disable_autop,
    // editor_disable_autop, then the 4.0.5 flattening. Four names for one
    // control, and every hop is a chance to drop it.
    legacy_2x_site();

    run_upgrade_chain();

    expect( setting( 'editor.disable_autop' ) )->toBe( 1 );
} );

it( 'keeps the settings 4.0.2 does not know about', function (): void {
    // This was the regression test for a real defect: update_4_0_2_options()
    // used to assign its four renamed editor keys over the whole row,
    // discarding the sixteen keys update_4_0_0_RC_2_options() had written
    // moments earlier in the same upgrade. A site upgrading from an older
    // version arrived at 4.0.5 with feeds on and xmlrpc enabled.
    //
    // It now merges. The three settings below are the ones an administrator
    // would notice first, and none of them is a key 4.0.2 knows about --
    // which is exactly why they are the ones to assert.
    legacy_2x_site();

    run_upgrade_chain();

    // Set by RC.2 and untouched by 4.0.2, which is the whole point of the
    // merge: this key is not one 4.0.2 knows about, and it still arrives.
    expect( setting( 'xmlrpc.disable_xmlrpc' ) )->toBe( 'completely' )
        ->and( setting( 'revisions.disable_revisions' ) )->toBe( [ 'all' ] )
        ->and( setting( 'privacy.disable_wp_generator' ) )->toBe( 1 );
} );

it( 'keeps the editor settings 4.0.2 does migrate', function (): void {
    // The other side of the same overwrite: these four are exactly what
    // survives, which is why the loss above went unnoticed.
    legacy_2x_site();

    run_upgrade_chain();

    expect( setting( 'editor.disable_texturization' ) )->toBe( 1 )
        ->and( setting( 'editor.disable_capital_p' ) )->toBe( 1 );
} );

it( 'leaves a fresh install alone', function (): void {
    // No legacy rows at all. Every routine should bail, and the settings
    // screen should read defaults rather than a row of zeroes written by a
    // migration that ran when it should not have.
    delete_option( 'disabler_options' );
    delete_option( 'disabler_settings' );
    delete_option( 'hbp_disabler_settings' );

    run_upgrade_chain();

    expect( get_option( 'hbp_disabler_settings', 'absent' ) )->toBe( 'absent' );
} );

it( 'is safe to run twice', function (): void {
    // Update callbacks are scheduled through ActionScheduler, and a
    // scheduler can retry. This used to be destructive: 3.0.0, 3.0.3 and
    // RC.2 all bail correctly on a second pass, because the rows they look
    // for were deleted by the first -- but update_4_0_2_options() read the
    // already-migrated row, found none of its four legacy keys, built an
    // empty array and wrote that over the completed migration. A retried
    // upgrade erased the settings the first pass produced.
    //
    // With the merge, a second pass finds no legacy keys, adds nothing, and
    // writes the row back unchanged.
    legacy_2x_site();

    run_upgrade_chain();

    $after_first = get_option( 'hbp_disabler_settings' );

    expect( $after_first )->not->toBe( [] );

    run_upgrade_chain();

    expect( get_option( 'hbp_disabler_settings' ) )->toBe( $after_first );
} );

it( 'runs every routine the plugin schedules', function (): void {
    // The guard on this file itself. PluginInstall::$db_updates is the real
    // schedule; run_upgrade_chain() above is a copy of it, and a copy drifts.
    // Comparing the two means a routine added to the plugin without being
    // added here fails rather than going quietly untested.
    $scheduled = ( new ReflectionClass( PluginInstall::class ) )
        ->getProperty( 'db_updates' )
        ->getValue();

    $options_routines = [];

    foreach ( $scheduled as $callbacks ) {
        foreach ( $callbacks as $callback ) {
            // db_version callbacks only bump a stored version number; the
            // options ones are the transformations this file covers.
            if ( str_ends_with( $callback, '_options' ) ) {
                $options_routines[] = $callback;
            }
        }
    }

    expect( $options_routines )->toBe( [
        'HBP\Disabler\Tools\Update\update_3_0_0_options',
        'HBP\Disabler\Tools\Update\update_3_0_3_options',
        'HBP\Disabler\Tools\Update\update_4_0_0_RC_2_options',
        'HBP\Disabler\Tools\Update\update_4_0_2_options',
        'HBP\Disabler\Tools\Update\update_4_0_5_options',
    ] );
} );
