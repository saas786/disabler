<?php

namespace HBP\Disabler;

use Hybrid\Tools\Config\PackageConfig;

/**
 * Reads this plugin's namespaced config.
 *
 *     Config::get( 'controls.editor', [] );  // → disabler.controls.editor
 *
 * Note this plugin stores Closures in config for two different reasons, and the
 * distinction matters:
 *
 *   'label'    => static fn() => esc_html__( 'Backend', 'hbp-disabler' ),  // defer __()
 *   'callback' => static fn() => '',                                       // a real callable
 *
 * `get()` returns both as-is. Use `value()` — as Definitions does at the leaf —
 * only on the first kind.
 */
class Config extends PackageConfig {
    protected static string $namespace = 'disabler';
}
