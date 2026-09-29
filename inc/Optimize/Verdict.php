<?php

namespace HBP\Disabler\Optimize;

/**
 * What this plugin has decided about one kind of update.
 *
 * Off      -- no updates, and hide the UI that offers them.
 * Manual   -- a human may update; nothing happens on its own.
 * Auto     -- update without asking.
 * Inherit  -- this plugin has no opinion; whatever WordPress passed in stands.
 */
enum Verdict {
    case Off;
    case Manual;
    case Auto;
    case Inherit;

    /**
     * The answer for an `auto_update_*` filter.
     *
     * Inherit returns the value WordPress passed in, which is the whole
     * reason this takes a fallback rather than returning a bool.
     */
    public function autoUpdate( bool $inherited ): bool {
        return match ( $this ) {
            self::Off, self::Manual => false,
            self::Auto => true,
            self::Inherit => $inherited,
        };
    }

    /**
     * Whether this verdict forbids automatic updating.
     *
     * Distinct from `autoUpdate()` because `auto_update_core` only ever
     * vetoes: the release-level filters decide whether core updates itself,
     * so returning true here would force an update the level filters had
     * already refused.
     */
    public function blocksAuto(): bool {
        return self::Off === $this || self::Manual === $this;
    }

    public function isOff(): bool {
        return self::Off === $this;
    }
}
