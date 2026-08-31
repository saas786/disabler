# Disabler test suite

Its own Composer project, deliberately.

The plugin supports PHP 8.2 and pins `config.platform.php` to that floor, so
its dependencies always resolve against the version it ships against. The test
tooling requires 8.4. Keeping the two manifests apart is what lets both be true
at once.

Pest decides where its project root is from where its own autoloader sits:

```php
// vendor/pestphp/pest/bin/pest
$vendorPath = dirname(__DIR__, 4).'/vendor/autoload.php';
$rootPath   = dirname($autoloadPath, 2);
```

The directory name `vendor` is hardcoded, so a custom `vendor-dir` cannot work,
and Pest then looks for a `tests` directory beside it. That is the whole reason
this suite is a project directory of its own containing `vendor/` and `tests/`,
rather than living directly under the repo's `tests/`.

## Running locally

You need PHP 8.4 on your PATH for the suite. The plugin itself still targets
8.2 -- the `Plugin` job in CI is what proves that, and it deliberately does not
install any of this.

```sh
composer install            # from the repo root: the plugin, at its 8.2 floor
composer install -d qa      # this suite, into qa/vendor
qa/vendor/bin/pest -c qa/phpunit.xml
```

The last line is also `composer test -d qa`.

First run downloads WordPress and the SQLite integration into `qa/.pest/`,
which takes a minute. After that a full run is a couple of seconds. To start
over, `rm -rf qa/.pest`.

### Running less than everything

```sh
qa/vendor/bin/pest -c qa/phpunit.xml --testsuite Unit          # no WordPress needed
qa/vendor/bin/pest -c qa/phpunit.xml --testsuite Integration
qa/vendor/bin/pest -c qa/phpunit.xml --filter "run twice"      # by test name
qa/vendor/bin/pest -c qa/phpunit.xml qa/tests/Unit             # by path
qa/vendor/bin/pest -c qa/phpunit.xml --bail                    # stop at the first failure
```

`Unit` needs nothing but PHP, so it is the fast loop while changing config or
the migration. `Integration` boots WordPress on SQLite and is where the option
reads, the settings screen and the update routine are covered.

### If a failure is unreadable

Pest renders exceptions through whoops, which can itself blow up and hide the
real error. PHPUnit prints it raw:

```sh
qa/vendor/phpunit/phpunit/phpunit -c qa/phpunit.xml --testsuite Integration
```

## Writing tests here

### The harness is single process

`--parallel` does not work and should not be added back. Every worker shares
one SQLite file, and the suite wraps each test in a transaction it rolls back
afterwards, so one worker's rollback discards writes another is still reading.
It surfaces as a dozen unrelated "the control did nothing" failures, because
stored settings vanish mid-test and features fall back to their defaults.

Fixing it properly means a database per worker keyed on paratest's
`TEST_TOKEN`, which is a change to pest-wp rather than to this repo.

### Global state the teardown restores

`Pest.php` snapshots and restores three things the database rollback cannot
reach, because they are PHP globals rather than rows:

- `$wp_filter`, via `snapshot_hooks()` / `restore_hooks()`
- `$wp->public_query_vars`, via `snapshot_query_vars()` / `restore_query_vars()`
  -- `Performance`'s embeds control edits it in place at boot
- `$pagenow`, `$current_screen`, `$typenow` and `$taxnow`, via `reset_screen()`

`WP_Scripts` and `WP_Styles` are thrown away and rebuilt rather than restored.

If a feature writes any other global, restore it in `Pest.php` rather than in
the test. A `try`/`finally` inside one test only protects that test, and the
leak will already have happened by the time it runs.

`$_SERVER` is the exception: `IPManagerTest` restores it per file, because it
is request state that only that one feature reads.

### Asserting on hooks

`has_filter( $hook )` with no callback asks "is anything at all hooked here",
which is almost never the question. Core hooks `wp_headers`, `xmlrpc_methods`
and others itself, so the answer is already true before the plugin does
anything.

Use `hook_callback_count( $hook )` before and after a boot to prove a feature
registered nothing, and `has_action( $hook, [ $obj, $method ] )` to prove it
registered something. `has_action()` returns the *priority*, and priority `0`
is falsy -- assert `toBe( 0 )`, never a truthiness check.

Prefer asserting on the effect over the registration. Features remove core
callbacks by calling `remove_action()` with an explicit priority, and a
priority that has drifted removes nothing and reports nothing. A registration
assertion cannot see that, because it checks the same number the production
code just used.

### Booting a feature

`boot_feature( Feature::class )` invokes the private init method by
reflection. `Editor` names its method `initializeHooks` where everything else
uses `initHooks`, so it needs `boot_feature( Editor::class, 'initializeHooks' )`.

That reflection route deliberately skips `boot()`, so `BootWiringTest` covers
the `add_action( 'init', ... )` line separately. Without it, a feature could
stop being attached to WordPress at all and nothing would fail.

### Reaching the admin

`is_admin()` reads `$GLOBALS['current_screen']` before it reads `WP_ADMIN`.
`set_admin_screen()` uses that: it loads `class-wp-screen.php` *and*
`screen.php` -- two files, the class is not in the function file -- then calls
`set_current_screen()`.

Do not define `WP_ADMIN` instead. It is process-wide and irreversible, so one
test doing it puts every later test in a fake admin.

### Code that exits

`capture_exit()` runs a callable and reports whether it tried to redirect (an
array), tried to `wp_die()` (a string), or did neither (null). It works by
throwing from inside the `wp_redirect` and `wp_die` handler filters, which
unwinds past the `exit()` before it runs. No process isolation needed.

It filters all six `wp_die` handler branches, not just `wp_die_handler`.
`wp_die()` picks one from the shape of the request -- a feed request takes the
xml branch -- and missing a branch kills the runner with "premature end of PHP
process" and no failing assertion to point at.

### Namespaced helpers

`container()` and `setting()` live in `HBP\Disabler`, and test files are in the
global namespace. Import them (`use function HBP\Disabler\setting;`) or call
them fully qualified. An unqualified call resolves to a global function that
does not exist.

### What pest-wp does not give you

`self::factory()` is not available -- pest-wp does not extend the test case
with WordPress' own factories. Use `wp_insert_post()`, `wp_insert_user()` and
`wp_insert_term()` directly.

### Keeping the network out

Nothing in this suite may make an HTTP request. Answer through the
`pre_http_request` filter instead, which short circuits `WP_Http` before a
socket is opened -- see `IPManagerTest`.

Note that `safeWPRemoteRequest()`'s `WP_Error` branch calls `trigger_error()`
at `E_USER_WARNING`, and this suite runs `failOnWarning="true"`, so that path
cannot be exercised without defining
`WPCOM_VIP_DISABLE_REMOTE_REQUEST_ERROR_REPORTING` -- process-wide and
irreversible. It is left uncovered on purpose.

### Assert on what the test controls

`update_4_0_5_options()` writes a full nested row built from resolved
defaults, so its contents depend on process state a single test does not own.
Pin the rows the test itself wrote, not the settings that fall out of the end.

## Mutation testing

```sh
cd qa && composer mutate          # what CI runs
cd qa && composer mutate:fresh    # the same, plus --clear-cache
```

The command lives in `qa/composer.json` and CI calls that same script, so the
two cannot drift apart.

Some hard-won details:

- It needs a coverage driver. The script sets `XDEBUG_MODE=coverage` itself
  via `@putenv`.
- `--path=../inc/Optimize` is passed explicitly rather than relying on the
  `<source>` block. The plugin resolves a relative path against the working
  directory and silently drops anything that is not a directory, so a path
  that does not resolve reports `0 Mutations for 0 Files` with no error. Run
  from `qa/`.
- CI uses xdebug, not pcov. pcov instruments only files beneath one inferred
  root -- `qa/`, where this suite's `vendor/` lives -- and the plugin source
  at `../inc` falls outside it, which produces the same silent zero.
- Verdicts are cached in the system temp directory, outside the repo, so
  `git clean` will not clear it. If a local score looks wrong, use
  `mutate:fresh`.
- `mutates( Feature::class )` at the top of each test file maps tests to
  source. Without it Pest refuses to start; with it, a mutant reruns one file
  instead of the whole suite.

A score around 95% is the expected baseline. Some surviving mutants are
equivalent -- removing an integer cast from a value that is already an integer
changes nothing observable -- so 100% is not the target.
