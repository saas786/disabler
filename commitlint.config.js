/**
 * Commit message rules.
 *
 * Modelled on conversionxl/aybolit, which runs Conventional Commits with a
 * deliberately short type list and carries the real detail in the scope.
 * Across its last 300 commits it uses exactly four types, and scopes about
 * four commits in five.
 *
 * The type says what kind of change it is. The scope says where. Four types is
 * enough to answer "is this behaviour I need to test?" -- which is the only
 * question the type has to answer. Everything finer belongs in the scope,
 * where it can grow without a config change.
 *
 * Aybolit derives its scopes from lerna package names. This is a single
 * plugin, so scopes are its parts instead. They are not enumerated on purpose:
 * an enum here would need editing every time a section is added, and a stale
 * enum rejects honest commits. Lowercase is enforced, spelling is not.
 *
 * Scopes in use, roughly most to least common:
 *
 *   updates, xmlrpc, feeds, editor, media, performance, privacy, restapi,
 *   revisions, admin-bar, backend, frontend   - the optimizers
 *   optimize          - a change across several of them
 *   settings          - config, controls, the setting() helper
 *   admin             - the settings screen
 *   migration         - the update routines and stored option shape
 *   container         - App, service providers, bootstrapping
 *   tracking, jetpack - the Tools/ integrations
 *   build             - composer scripts, php-scoper, the asset pipeline
 *   qa                - the Pest suite
 *   ci                - workflows and git hooks
 *   deps              - dependency bumps and lockfiles
 *   i18n              - pot regeneration, textdomain
 *   release           - version bumps, stable tag, changelog
 */
module.exports = {
    extends: [ '@commitlint/config-conventional' ],

    rules: {
        // feat     - behaviour a site owner could notice appearing
        // fix      - behaviour that was wrong and now is not
        // refactor - same behaviour, different shape
        // chore    - everything else: build, ci, deps, tests, docs, release
        'type-enum'  : [ 2, 'always', [ 'feat', 'fix', 'refactor', 'chore' ] ],
        'type-empty' : [ 2, 'never' ],
        'type-case'  : [ 2, 'always', 'lower-case' ],

        // Optional, but write one unless the change is genuinely repo-wide.
        // Aybolit leaves about a fifth unscoped and those are the releases and
        // the sweeping changes, which is the right ratio to aim for.
        'scope-case': [ 2, 'always', 'lower-case' ],

        // Read as: "if applied, this commit will <subject>".
        //
        // Phrased as "never sentence-case" rather than "always lower-case":
        // subjects here name classes, and lower-case would reject
        // "extract UpdatePolicy and Verdict" for the capitals that carry the
        // meaning. A capitalised first word is still rejected.
        'subject-case': [
            2,
            'never',
            [ 'sentence-case', 'start-case', 'pascal-case', 'upper-case' ],
        ],
        'subject-empty'     : [ 2, 'never' ],
        'subject-full-stop' : [ 2, 'never', '.' ],

        // Warning at 80, so a long-but-clear subject is not blocked. Aybolit's
        // median is 62 and its p90 is 76; a hard 72 would have rejected 31 of
        // its last 300, most of them fine.
        'header-max-length': [ 1, 'always', 80 ],

        // Blank line before body and footer, so `git log` renders them as
        // such rather than as a run-on subject.
        'body-leading-blank'   : [ 2, 'always' ],
        'footer-leading-blank' : [ 2, 'always' ],

        // Warning: worth wrapping, not worth blocking a body that pastes a
        // stack trace or a Windows path.
        'body-max-line-length': [ 1, 'always', 100 ],
    },
};
