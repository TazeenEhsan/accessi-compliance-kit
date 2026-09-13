# Coding Guidelines — Accessibility Compliance Kit for WooCommerce

Conventions for all PHP/JS/CSS in this project. Binding rules live in `AI_RULES.md`; this document adds the day-to-day detail. Stack per proposal §5.1: PHP 7.4+ (targeting 8.0+), `@wordpress/element` React, `@wordpress/scripts` build, PHPUnit + Jest.

---

## 1. PHP

### Style & Standard
- **WordPress Coding Standards (WPCS)** via PHPCS with the `WordPress` ruleset; add `PHPCompatibilityWP` pinned to 7.4+. Run in CI/pre-release (TASKS 4.2).
- Tabs for indentation, Yoda conditions, snake_case functions/variables, spaces inside parens — the WPCS defaults. Don't mix PSR-12 style in.
- No PHP 8-only syntax (must parse on 7.4): no constructor promotion, `match`, enums, readonly, nullsafe operator. Type hints and return types (7.4-compatible) are encouraged.

### Structure
- PSR-4: `AccessibilityComplianceKitForWooCommerce\` → `src/`; one class per file; class name = file name; namespace mirrors folder (`AccessibilityComplianceKitForWooCommerce\Scanner\ScanStorage`).
- Classes are small and single-purpose per the §5.2 layout. `Plugin.php` wires; services do.
- Constructor injection for dependencies where practical (e.g. `ScanController` receives `ScanStorage`); no service locators, no globals beyond WordPress's own.
- Visibility explicit on everything; `private` by default, widen only when needed.
- File header: `<?php`, ABSPATH guard, namespace, `use` statements — in that order.

### WordPress Idioms
- Hook registration inside each service's `register()`/constructor boot method, not at file scope.
- Prefix everything `accessibility_compliance_kit_for_woocommerce_` (options, hooks, transients, cron events, handles).
- Fire plugin hooks at meaningful moments (`accessibility_compliance_kit_for_woocommerce_after_scan_saved`, etc.) with documented params.
- Use WP APIs over raw PHP: `wp_json_encode`, `wp_remote_*` (Pro only), `wp_mail`, `current_time`/`gmdate`, `wp_insert_post`.
- i18n: every user-facing string in `__()`/`esc_html__()`/etc. with literal text-domain `accessibility-compliance-kit-for-woocommerce`; translator comments for placeholders.

### Documentation
- PHPDoc on every class and public method: one-line summary, `@param`/`@return` with types, `@since` with the plugin version.
- Inline comments only for non-obvious constraints (why, not what).

## 2. JavaScript / React

- Build with `@wordpress/scripts`; extend its webpack config, never eject/replace (proposal §5.1).
- ESLint with `@wordpress/eslint-plugin` recommended preset; Prettier via the WP config.
- React via `@wordpress/element`; UI from `@wordpress/components`; i18n via `@wordpress/i18n` (`__( 'Text', 'accessibility-compliance-kit-for-woocommerce' )`). These resolve to WP-core externals — never bundle React itself.
- Functional components + hooks only; one component per file, PascalCase filenames matching proposal §5.2 (`ScanResults.jsx`, `Settings.jsx`, `Dashboard.jsx`, `App.jsx`).
- State: local `useState`/`useReducer` is enough for this app's size; no Redux/external state libs.
- Never `dangerouslySetInnerHTML` with scan data (docs/security.md §3).
- The scanner bundle (`runScan.js`) stays framework-free — axe-core + plain DOM only, small.
- **The admin UI itself must be accessible** — an accessibility plugin with an inaccessible admin is a credibility bug: keyboard operability, labels on all controls, `Notice`/`Spinner` components for status, no color-only meaning.

## 3. CSS

- Files per §5.2: `admin.css`, `frontend-fixes.css`. All selectors prefixed `.accessibility-compliance-kit-for-woocommerce-`.
- `frontend-fixes.css`: minimal, defensive specificity, scoped under the `body_class` flags (`.accessibility-compliance-kit-for-woocommerce-fixes-active`); must never restyle theme elements beyond the fix's stated purpose.
- Focus styles use `:focus-visible` with `:focus` fallback; respect `prefers-reduced-motion` for anything animated.

## 4. Testing (proposal §5.1: PHPUnit + Jest)

- PHPUnit in `tests/phpunit/`: unit tests for `ViolationParser`, `ScanStorage`, `StatementGenerator`, every Fix class, and the `FixManager` "only enabled fixes register" behavior (proposal §10 prompt 4). **Decision (task 0.2): Brain Monkey** stubs WP functions — no WP core test-suite checkout required, keeps the suite fast and dependency-light. `tests/phpunit/bootstrap.php` calls `Brain\Monkey\setUp()`/`tearDown()` per test case; stay consistent, don't mix in the WP core test suite later.
- Jest in `tests/js/`: `@wordpress/scripts test-unit-js`; cover result formatting in `runScan.js` and non-trivial component logic (severity grouping, settings save states).
- Test naming: `test_<method>_<scenario>_<expectation>` (PHP), `describe/it` prose (JS).
- Manual test matrix (Storefront/Astra/Kadence) is part of Definition of Done for every fix (proposal §6 Phase 2, §11).

## 5. Git & Process

- Small commits, one TASKS.md task per commit where feasible; message references the task (e.g. `Phase 1.2: ScanStorage create/complete/fail`).
- Branches: `main` (releasable), `dev` (integration) — both already exist in the repo.
- Never commit `node_modules/`, `build/`; `vendor/` excluded from git, produced by `composer install --no-dev` in the release build (proposal §5.2 gitignore notes).
- Version bumps: plugin header + `ACCESSIBILITY_COMPLIANCE_KIT_FOR_WOOCOMMERCE_VERSION` + `readme.txt` stable tag + changelog together.

## 6. Definition of Done (per task)

1. Code follows this document and `AI_RULES.md` (prefixing, escaping, i18n, standards).
2. PHPCS/ESLint clean for touched files.
3. Tests written/updated where the task specifies; suite passes.
4. Behavior manually verified on the dev site when the task touches runtime behavior.
5. Checkbox ticked in `TASKS.md`.
6. No requirement drift from `PLUGIN_PROPOSAL.md` — deviations get flagged, not silently coded.
