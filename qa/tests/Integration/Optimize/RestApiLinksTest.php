<?php

/**
 * The restapi controls that only remove core callbacks.
 *
 * Four independent switches that each unhook one core function. Nothing
 * combines them, so the risk here is not interaction -- it is that a removal
 * silently stops matching, which is what happens when core changes a
 * priority. remove_action() with the wrong priority removes nothing and
 * reports nothing, so the control goes quiet rather than loud.
 *
 * disable_rest_api_links is asserted through the head, where the effect is
 * visible. The other two have no in-process output -- one writes an HTTP
 * header, one runs inside the XML-RPC RSD document -- so those assert on
 * registration, which is second best and marked as such.
 */

declare(strict_types = 1);

use HBP\Disabler\Optimize\RestAPI;

// Tells the mutation runner which class this file is responsible for, so a
// mutant in it reruns these tests rather than all 175. Without a mutates()
// or covers() somewhere, Pest has no map from test to source and refuses to
// start.
mutates( RestAPI::class );

/**
 * @param array<string, mixed> $values
 */
function boot_restapi( array $values ): void {
    $defaults = require plugin_path( 'config/restapi.php' );

    store_settings( 'restapi', array_merge( $defaults, $values ) );

    boot_feature( RestAPI::class );
}

it( 'prints the rest api link when the control is off', function (): void {
    boot_restapi( [ 'disable_rest_api_links' => 0 ] );

    expect( rendered_head() )->toContain( 'rel="https://api.w.org/"' );
} );

it( 'removes the rest api link from the head', function (): void {
    boot_restapi( [ 'disable_rest_api_links' => 1 ] );

    $head = rendered_head();

    // The RSD and generator links come off other controls in other sections
    // and must survive, or this passes just as well for a feature that
    // emptied the head.
    expect( $head )->not->toContain( 'rel="https://api.w.org/"' )
        ->and( $head )->toContain( 'rel="EditURI"' );
} );

it( 'leaves the rest link header in place when the control is off', function (): void {
    // template_redirect at priority 11. PHP cannot read back a header this
    // process never sent, so registration is the seam -- and the priority is
    // the part worth pinning, since remove_action() with the wrong one is a
    // silent no-op.
    boot_restapi( [ 'disable_rest_api_link_in_headers' => 0 ] );

    expect( has_action( 'template_redirect', 'rest_output_link_header' ) )->toBe( 11 );
} );

it( 'removes the rest link header', function (): void {
    boot_restapi( [ 'disable_rest_api_link_in_headers' => 1 ] );

    expect( has_action( 'template_redirect', 'rest_output_link_header' ) )->toBeFalse();
} );

it( 'leaves the rsd api entry in place when the control is off', function (): void {
    boot_restapi( [ 'disable_rest_api_rsd_link' => 0 ] );

    expect( has_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' ) )->not->toBeFalse();
} );

it( 'removes the rest api entry from the rsd document', function (): void {
    boot_restapi( [ 'disable_rest_api_rsd_link' => 1 ] );

    expect( has_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' ) )->toBeFalse();
} );

it( 'lets requests through when the visitor control is off', function (): void {
    boot_restapi( [ 'disable_rest_api_for_visitors' => 0 ] );

    // Not toBeNull(). Core's own rest_cookie_check_errors() sits on this
    // filter and answers true once it is satisfied there is no cookie or
    // nonce problem, so null never reaches the end of the chain -- asserting
    // on it would be pinning core's internal bookkeeping rather than the
    // plugin's behaviour. What this control promises is that it does not
    // refuse the request, and a WP_Error is the only refusal core reads.
    expect( apply_filters( 'rest_authentication_errors', null ) )
        ->not->toBeInstanceOf( WP_Error::class );
} );

it( 'blocks logged out visitors from the rest api', function (): void {
    wp_set_current_user( 0 );

    boot_restapi( [ 'disable_rest_api_for_visitors' => 1 ] );

    expect( apply_filters( 'rest_authentication_errors', null ) )
        ->toBeInstanceOf( WP_Error::class );
} );

it( 'leaves logged in users alone', function (): void {
    // The half that proves the control is about visitors rather than about
    // the REST API. Without it, a callback that refused everyone would pass.
    $user_id = wp_insert_user( [
        'user_login' => 'rest_' . wp_generate_password( 6, false ),
        'user_email' => 'rest_' . wp_generate_password( 6, false ) . '@example.org',
        'user_pass'  => wp_generate_password(),
    ] );

    expect( $user_id )->toBeInt();

    wp_set_current_user( $user_id );

    boot_restapi( [ 'disable_rest_api_for_visitors' => 1 ] );

    expect( apply_filters( 'rest_authentication_errors', null ) )
        ->not->toBeInstanceOf( WP_Error::class );
} );

/**
 * A user with one role, returned as a WP_User.
 *
 * wp_insert_user() rather than a factory: pest-wp does not extend the test
 * case with WordPress' own factories.
 */
function user_with_role( string $role ): WP_User {
    $suffix = wp_generate_password( 6, false );

    $user_id = wp_insert_user( [
        'user_login' => 'apw_' . $suffix,
        'user_email' => 'apw_' . $suffix . '@example.org',
        'user_pass'  => wp_generate_password(),
        'role'       => $role,
    ] );

    expect( $user_id )->toBeInt();

    return get_userdata( $user_id );
}

it( 'leaves application passwords available by default', function (): void {
    boot_restapi( [ 'disable_application_passwords' => 'no' ] );

    expect( apply_filters( 'wp_is_application_passwords_available', true ) )->toBeTrue();
} );

it( 'turns application passwords off for everyone', function (): void {
    boot_restapi( [ 'disable_application_passwords' => 'all' ] );

    expect( apply_filters( 'wp_is_application_passwords_available', true ) )->toBeFalse();
} );

it( 'leaves the global switch alone in selective mode', function (): void {
    // 'selective' works per user, so the global filter must stay untouched.
    // Registering both would make every selective site behave like 'all'.
    boot_restapi( [
        'disable_application_passwords' => 'selective',
        'application_passwords_roles'   => [ 'editor' ],
    ] );

    expect( apply_filters( 'wp_is_application_passwords_available', true ) )->toBeTrue();
} );

it( 'turns application passwords off for a listed role', function (): void {
    boot_restapi( [
        'disable_application_passwords' => 'selective',
        'application_passwords_roles'   => [ 'editor' ],
    ] );

    expect( apply_filters(
        'wp_is_application_passwords_available_for_user',
        true,
        user_with_role( 'editor' )
    ) )->toBeFalse();
} );

it( 'leaves an unlisted role alone', function (): void {
    // The half that proves the role list is read rather than ignored. Without
    // it, a callback refusing every user would pass just as well.
    boot_restapi( [
        'disable_application_passwords' => 'selective',
        'application_passwords_roles'   => [ 'editor' ],
    ] );

    expect( apply_filters(
        'wp_is_application_passwords_available_for_user',
        true,
        user_with_role( 'author' )
    ) )->toBeTrue();
} );

it( 'changes nothing when no roles are selected', function (): void {
    // An empty list is the guard clause: selective mode with nothing chosen
    // must not become 'all'.
    boot_restapi( [
        'disable_application_passwords' => 'selective',
        'application_passwords_roles'   => [],
    ] );

    expect( apply_filters(
        'wp_is_application_passwords_available_for_user',
        true,
        user_with_role( 'editor' )
    ) )->toBeTrue();
} );

it( 'never re-enables application passwords core already refused', function (): void {
    // The first guard: if something upstream already said no, this callback
    // hands that no straight back. Dropping the guard would have the plugin
    // granting access that core had denied -- the one way a control that
    // only ever restricts could widen access instead.
    boot_restapi( [
        'disable_application_passwords' => 'selective',
        'application_passwords_roles'   => [ 'subscriber' ],
    ] );

    expect( apply_filters(
        'wp_is_application_passwords_available_for_user',
        false,
        user_with_role( 'editor' )
    ) )->toBeFalse();
} );
