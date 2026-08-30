<?php

namespace HBP\Disabler\Optimize;

use function HBP\Disabler\setting;

/**
 * The plugin's update settings, resolved once, as answers rather than strings.
 *
 * Every `updates.*` callback used to re-read `disable_updates` and re-derive
 * the same verdict from it. There are about thirty of them, so the same three
 * branches were written thirty times and could drift thirty ways. They now
 * ask this instead.
 *
 * Built once per request and handed to the callbacks, so the settings are
 * read six times rather than roughly eighty.
 */
final class UpdatePolicy {
    private function __construct(
        public readonly bool $enabled,
        public readonly bool $everything,
        public readonly Verdict $core,
        public readonly Verdict $plugins,
        public readonly Verdict $themes,
        public readonly Verdict $translations,
        public readonly ?bool $vcs,
        public readonly bool $nagsOnlyForAdmin,
        private readonly ?string $coreAutoLevel,
    ) {}

    /**
     * The six settings this plugin stores, read from the option store.
     *
     * Everything that needs a container lives here and nowhere else, so the
     * resolution below can be exercised without booting one.
     */
    public static function fromSettings(): self {
        return self::fromArray( [
            'disable_updates'             => setting( 'updates.disable_updates' ),
            'core_updates'                => setting( 'updates.core_updates' ),
            'plugin_updates'              => setting( 'updates.plugin_updates' ),
            'theme_updates'               => setting( 'updates.theme_updates' ),
            'translation_updates'         => setting( 'updates.translation_updates' ),
            'enable_update_vcs'           => setting( 'updates.enable_update_vcs' ),
            'updates_nags_only_for_admin' => setting( 'updates.updates_nags_only_for_admin' ),
        ] );
    }

    /**
     * Resolve already-gathered settings into verdicts.
     *
     * Pure: no container, no WordPress, no option store. That is the whole
     * reason it is separate -- the branching that used to be spread across
     * thirty callbacks is now one function that a unit test can drive over
     * every combination.
     *
     * @param array<string, mixed> $stored
     */
    public static function fromArray( array $stored ): self {
        $mode = $stored['disable_updates'] ?? 'no';
        $core = $stored['core_updates'] ?? null;

        // 'all' overrides every per-kind setting, so resolve it first and
        // let the per-kind branches assume 'selective'.
        if ( 'all' === $mode ) {
            return new self(
                enabled: true,
                everything: true,
                core: Verdict::Off,
                plugins: Verdict::Off,
                themes: Verdict::Off,
                translations: Verdict::Off,
                vcs: true,
                nagsOnlyForAdmin: true,
                coreAutoLevel: null
            );
        }

        $selective = 'selective' === $mode;

        return new self(
            enabled: 'no' !== $mode,
            everything: false,
            core: $selective ? self::coreVerdict( $core ) : Verdict::Inherit,
            plugins: $selective ? self::kindVerdict( $stored['plugin_updates'] ?? null ) : Verdict::Inherit,
            themes: $selective ? self::kindVerdict( $stored['theme_updates'] ?? null ) : Verdict::Inherit,
            translations: $selective ? self::kindVerdict( $stored['translation_updates'] ?? null ) : Verdict::Inherit,
            vcs: $selective ? self::vcs( $core, $stored['enable_update_vcs'] ?? null ) : null,
            nagsOnlyForAdmin: $selective && (bool) ( $stored['updates_nags_only_for_admin'] ?? false ),
            coreAutoLevel: $selective ? self::autoLevel( $core ) : null
        );
    }

    /**
     * Whether core may auto-update at this release level.
     *
     * `allow_{minor,major,dev}_auto_core_updates` each ask about one level,
     * and the stored setting names exactly one. Off and Manual never allow.
     *
     * Inherit arrives here meaning two different things, which is why it is
     * not simply passed through. With updates unmanaged it means this plugin
     * has no opinion, and the value WordPress passed in stands. Under
     * `selective` it can only mean the stored setting matched none of the
     * five choices -- a preset or a hand-edited row, since the screen cannot
     * produce it. Deferring to WordPress there would let an unreadable
     * setting turn core auto-updates on for a site that installed this plugin
     * to keep them off, so an unrecognised value refuses instead.
     */
    public function allowsCoreAuto( string $level, bool $inherited ): bool {
        return match ( $this->core ) {
            Verdict::Off, Verdict::Manual => false,
            Verdict::Auto => $level === $this->coreAutoLevel,
            Verdict::Inherit => $this->enabled ? false : $inherited,
        };
    }

    /**
     * The bulk actions to strip from a plugins or themes list table.
     *
     * Off loses the update actions as well as the auto-update toggles;
     * Manual loses only the toggles, since manual updating is the point.
     */
    public function strippedBulkActions( Verdict $verdict ): array {
        return match ( $verdict ) {
            Verdict::Off => [
                'update-selected',
                'update',
                'upgrade',
                'enable-auto-update-selected',
                'disable-auto-update-selected',
            ],
            Verdict::Manual => [
                'enable-auto-update-selected',
                'disable-auto-update-selected',
            ],
            default => [],
        };
    }

    /**
     * `manual` means a human updates; `disable` means nobody does. Both stop
     * automatic updates, which is why the old code kept conflating them --
     * they differ in whether the update button still exists.
     */
    private static function kindVerdict( ?string $stored ): Verdict {
        return match ( $stored ) {
            'disable' => Verdict::Off,
            'manual' => Verdict::Manual,
            'auto' => Verdict::Auto,
            default => Verdict::Inherit,
        };
    }

    private static function coreVerdict( ?string $stored ): Verdict {
        return match ( $stored ) {
            'disable_core_updates' => Verdict::Off,
            'disable_core_auto_updates' => Verdict::Manual,
            'allow_minor_core_auto_updates',
            'allow_major_core_auto_updates',
            'allow_dev_core_auto_updates' => Verdict::Auto,
            default => Verdict::Inherit,
        };
    }

    private static function autoLevel( ?string $stored ): ?string {
        return match ( $stored ) {
            'allow_minor_core_auto_updates' => 'minor',
            'allow_major_core_auto_updates' => 'major',
            'allow_dev_core_auto_updates' => 'dev',
            default => null,
        };
    }

    /**
     * Whether to tell WordPress this is a version-controlled checkout.
     *
     * Claiming one is how core is told to keep its hands off. `enable` is the
     * user saying so directly, and disabling core updates implies it.
     *
     * Null, not false, when neither applies: `automatic_updates_is_vcs_checkout`
     * is also answered by hosts and other plugins, and a site really under
     * version control must keep saying so. Only an explicit `disable` overrides
     * them.
     */
    private static function vcs( ?string $core, ?string $stored ): ?bool {
        if ( 'disable_core_updates' === $core || 'enable' === $stored ) {
            return true;
        }

        return 'disable' === $stored ? false : null;
    }
}
