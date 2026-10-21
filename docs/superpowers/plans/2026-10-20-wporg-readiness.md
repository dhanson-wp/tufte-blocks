# WordPress.org readiness implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Get Tufte Blocks to a state the WordPress.org theme review would accept: post formats handled the core-standard way, the Projects feature moved into its own plugin, the notes query logic gone from the theme, and every Required item in the review handbook met.

**Architecture:** Three repos, released in order so the live site never loses projects. The new **Tufte Projects** plugin takes the Projects feature with every stored identifier unchanged. Tufte Blocks 1.10.0 is a bridge release: it skips its own Projects code when the plugin is active and drops two leftover init routines. Tufte Blocks 2.0.0 then deletes Projects and the notes query logic, adds core-standard post format presentation, and clears the .org audit list.

**Tech stack:** PHP 7.4+, block theme (theme.json v3, HTML templates, PHP patterns), WordPress 6.4+ for the theme and 6.7+ for the plugin (`register_block_template()`), the theme's own test runner (`tests/run.php` through WP-CLI `eval-file`).

**Spec:** No separate spec. The decisions below come from Derek on 2026-10-19 and 2026-10-20, and the .org rules are from https://make.wordpress.org/themes/handbook/review/required/ (read 2026-10-20).

## Decisions this plan carries out

- **Post formats are core-standard in the theme.** The theme declares support for every format and styles them through core's classes (`format-<slug>` on each post, `single-format-<slug>` on the body). It must look right with no plugin, with Tufte Formats, and with Courtney Robertson's Post Formats for Block Themes. It depends on neither plugin.
- **Tufte Formats keeps the opinionated notes model** (notes stay out of the loop, feed, and search; archive views). Nothing in this plan changes that plugin except one compatibility check.
- **Projects becomes its own plugin, Tufte Projects**, in its own repo, set up like Tufte Formats (Derek, 2026-10-20).
- **Prove it on the child first, then the parent** ([[feedback-upstream-to-tufte-blocks]]). The derekhanson.blog Studio site is the test bed, and every live step waits for Derek's explicit go.

## Global constraints

- Theme slug and text domain: `tufte-blocks`. Plugin slug and text domain: `tufte-projects`.
- New plugin PHP prefix: `tufte_projects_`; constants `TUFTE_PROJECTS_`; handles `tufte-projects-`.
- **Stored identifiers never change**, because live content and the database already hold them: post type `project`; taxonomies `project_type` and `project_tool`; every post meta key in `inc/projects/fields.php` (including `project_github_url`, `project_demo_url`, `_project_source_cache`); term meta `tool_icon`; block bindings source `tufte-blocks/project-field`; icon names `tufte-blocks/<slug>`; cron hook `tufte_blocks_projects_sync`.
- Theme requirements stay `Requires at least: 6.4`, `Requires PHP: 7.4`. The plugin header sets `Requires at least: 6.7`, `Requires PHP: 7.4`.
- Run WP-CLI with Studio's arm64 PHP: `"$STUDIO_PHP" /usr/local/bin/wp --path=<site> ...`, where `STUDIO_PHP` is the newest `/Applications/Studio.app/Contents/Resources/php-bin/*-studio-*/php`. Outbound fetches need `--require=tests/allow-hosts.php`.
- After adding or removing pattern files, clear the pattern cache: `wp eval 'wp_get_theme()->delete_pattern_cache();'`.
- Version bumps touch both `style.css` (header) and `functions.php` (`TUFTE_BLOCKS_VERSION`), plus `readme.txt` (`Stable tag` and changelog). Check `origin/main` right before merging a version-bumped PR.
- Merging the theme to `main` deploys to live by SFTP. Creating a GitHub repo, connecting a WordPress.com deployment, merging to `main`, and activating a plugin on live each need Derek's explicit go.
- Prose (readme, changelogs, PR descriptions) follows Derek's voice: no em dashes, contractions, Oxford comma, sentence case.

## Review focus

1. **The overlap window.** For as long as the plugin and the 1.10.0 theme are both active, `project` and the bindings source must register exactly once. Expect no `_doing_it_wrong` notice and no duplicate sidebar panel. Task 2 owns the test.
2. **Customized project templates.** If someone saved `single-project` or `archive-project` in the Site Editor, that database copy belongs to the active theme and must keep rendering after the theme's files are gone. Task 1 owns the check (plugin templates register with the same slugs).
3. **The already-scheduled cron event.** Live has `tufte_blocks_projects_sync` scheduled by the theme. After the switch, that event must still have a callback (the plugin's) and be scheduled exactly once. Task 1 owns the test.
4. **A site without Tufte Formats.** Once the theme drops the notes query, Status posts show in the blog loop with no title. They still need a readable row: date link, content, and no empty heading gap. Task 4 owns the test.
5. **Courtney's plugin active.** Its per-format single templates must render with theme styling and no layout breakage. Task 6 owns the check.

---

### Task 1: Tufte Projects plugin

Move the Projects feature out of the theme into a new plugin. Behavior stays identical. Only PHP names change; stored identifiers stay the same.

**Files:**
- Create repo: `~/Projects/tufte-projects/` (plugin at the repo root, fresh history). The GitHub repo `dhanson-wp/tufte-projects` is private and needs Derek's go before it's created.
- Create: `tufte-projects.php` (header, constants, loader in the same dependency order as the theme's loop in `functions.php:28-38`)
- Create: `includes/{post-type,tool-icons,fields,header-parser,sync,bindings,editor}.php`, `includes/sources/{wporg,github}.php`, copied from `tufte-blocks/inc/projects/` with names changed
- Create: `includes/templates.php`, which registers `templates/single-project.html` and `templates/archive-project.html` with `register_block_template()`
- Create: `includes/patterns.php`, which registers the five project patterns (`project-card`, `project-faq`, `project-features`, `project-install-steps`, `syllabus`) and the `tufte-projects` pattern category
- Move: `assets/js/project-fields.js`, `assets/images/tools/*.svg`, and `tests/` (runner, fixtures, the four `test-*.php` files)
- Create: `uninstall.php` (clears the cron hook only; it never deletes content)
- Create: `readme.txt`, `.gitattributes` (`export-ignore` for `tests/`, `docs/`, `.github/`)

**Interfaces:**
- Produces: constant `TUFTE_PROJECTS_VERSION` = `'0.1.0'`, defined at the top of `tufte-projects.php` before any `require`. Tasks 2 and 3 test for it.
- Produces: REST namespace `tufte-projects/v1` (this rename is safe because only the bundled JS reads it; update `editor.php`'s `restBase`).
- Produces: the sync user agent `tufte-projects/<TUFTE_PROJECTS_VERSION> (+<home_url()>; project sync)`. It no longer hardcodes derekhanson.blog.
- Consumes: nothing from the theme. Use `plugin_dir_path( TUFTE_PROJECTS_PLUGIN_FILE )` and `plugins_url()` wherever the theme used `get_template_directory()` or `get_template_directory_uri()`.

- [ ] **Step 1: Copy the tests and point the runner at the plugin.** In `tests/run.php`, change the usage comment to `wp --path=/Users/derekhanson/Studio/derekhansonblog eval-file wp-content/plugins/tufte-projects/tests/run.php`, and rename the helpers' `tufte_` prefix to `tufte_projects_`.
- [ ] **Step 2: Add `tests/test-plugin.php` with these assertions:**
  - `tufte_projects_assert_same( true, post_type_exists( 'project' ), 'project post type registered' )`
  - `taxonomy_exists( 'project_type' )` and `taxonomy_exists( 'project_tool' )` are true
  - `null !== get_block_bindings_source( 'tufte-blocks/project-field' )`
  - `false !== wp_next_scheduled( 'tufte_blocks_projects_sync' )`, and `_get_cron_array()` holds exactly one event for that hook
  - `has_action( 'tufte_blocks_projects_sync', 'tufte_projects_sync_all' )` is not false
  - `WP_Block_Templates_Registry::get_instance()->is_registered( 'tufte-projects//single-project' )` and the same for `archive-project` are true, so a theme without those files still gets them (Review focus 2; a template someone saved in the Site Editor lives in the database under the active theme and keeps winning either way)
- [ ] **Step 3: Symlink the plugin into the Studio site and run the tests.** Symlink `~/Studio/derekhansonblog/wp-content/plugins/tufte-projects` to the repo, keep it inactive, and run the runner. Expected: FAIL on the new assertions (the plugin isn't loaded).
- [ ] **Step 4: Port the code.** Rename every `tufte_blocks_project*` function to `tufte_projects_*`, `TUFTE_BLOCKS_PROJECT_SYNC_HOOK` to `TUFTE_PROJECTS_SYNC_HOOK` (value unchanged: `'tufte_blocks_projects_sync'`), and the text domain to `tufte-projects`. Replace the theme's "flush once" option (`tufte_blocks_projects_rewrite_flushed`) with `register_activation_hook()` that registers the types and then calls `flush_rewrite_rules()`. Keep the init-time scheduler, because that's what keeps the live event alive.
- [ ] **Step 5: Activate on Studio and run the tests.** Run `wp plugin activate tufte-projects`, then the runner. Expected: all PASS, including the four ported suites.
- [ ] **Step 6: Commit.** `git init`, add everything, and commit "Tufte Projects 0.1.0: Projects feature moved out of Tufte Blocks". Stop here for Derek's go before creating the GitHub repo and pushing.

### Task 2: Tufte Blocks 1.10.0, the bridge release

**Files:**
- Modify: `functions.php:28-41` (wrap the Projects loader), `functions.php:208-235` (delete the temporary page-template reset), `functions.php:290-311` (delete the one-time Additional CSS migration)
- Modify: `style.css`, `readme.txt` (version 1.10.0, plus the duplicate `= 1.9.3 =` heading fixed)
- Create: `tests/test-bridge.php`

**Interfaces:**
- Consumes: `TUFTE_PROJECTS_VERSION` from Task 1.

- [ ] **Step 1: Write `tests/test-bridge.php` with these assertions:**
  - When `defined( 'TUFTE_PROJECTS_VERSION' )`: `false === function_exists( 'tufte_blocks_project_sync_all' )` (the theme's copy didn't load) and `true === post_type_exists( 'project' )` (the plugin's did).
  - A saved `page` template survives init: create a `wp_template` post named `page` for `get_stylesheet()`, call `do_action( 'init' )`, assert the post still exists, then delete it.
- [ ] **Step 2: Run with the plugin active.** Expected: FAIL on both, because the theme still loads its Projects code and the reset deletes the template.
- [ ] **Step 3: Implement.** Wrap the Projects `foreach` in `if ( ! defined( 'TUFTE_PROJECTS_VERSION' ) ) { ... }` with a comment pointing at the plugin. Delete both leftover init routines. Plugins load before the theme's `functions.php`, so the constant is always set by then.
- [ ] **Step 4: Run both runners (theme and plugin).** Expected: all PASS. Then load `/projects/` and one project on the Studio site and compare against the live page. The bound fields, tool icons, and sidebar panel should be the same.
- [ ] **Step 5: Commit and open a PR.** Use the `wp-ship` skill, with commit message "Release 1.10.0: step aside for Tufte Projects". Merging deploys to live, so the order in Task 3 matters.

### Task 3: Live rollout of the plugin and 1.10.0

This task is operations, not code, and every numbered step needs Derek's go.

- [ ] **Step 1:** Export the live database, or confirm a fresh WordPress.com backup.
- [ ] **Step 2:** Connect `dhanson-wp/tufte-projects` to WordPress.com GitHub Deployments (Advanced, Manual) with the destination `/wp-content/plugins/tufte-projects`, the same way Tufte Formats is connected. Deploy, then activate the plugin on live.
- [ ] **Step 3:** Merge the 1.10.0 PR (this deploys the theme).
- [ ] **Step 4: Verify on live.** `/projects/` and every project page render with their bound fields. `wp cron event list` shows one `tufte_blocks_projects_sync`. The sync button in the editor's "Project details" panel works (it hits `tufte-projects/v1`). The PHP error log has no new notices.

### Task 4: Tufte Blocks 2.0.0, removing what isn't theme work

**Files:**
- Delete: `inc/projects/`, `assets/js/project-fields.js`, `assets/images/tools/`, `templates/single-project.html`, `templates/archive-project.html`, the five project patterns, the `tufte-projects` pattern category in `functions.php`, the four project test files and their fixtures, `tests/allow-hosts.php`
- Delete: the query parts of `inc/notes.php` (`tufte_blocks_notes_clause`, `tufte_blocks_notes_and`, `tufte_blocks_notes_exclude_main`, `tufte_blocks_notes_query_block`)
- Create: `inc/formats.php`, which replaces what's left of `inc/notes.php`
- Modify: `functions.php` (drop the Projects loader and the bridge; require `inc/formats.php`)
- Modify: `inc/microformats.php:25-27` (drop the dead `jetpack-social-note` type)
- Create: `tests/test-formats.php`

**Interfaces:**
- Produces: `tufte_blocks_post_formats(): array`, which returns `array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' )` and is used by `add_theme_support( 'post-formats', ... )` on `after_setup_theme`.
- Produces: `tufte_blocks_format_hide_title( string $block_content ): string`, hooked to `render_block_core/post-title`, which returns `''` for `status` and `aside` posts. (Tufte Formats' compat file unhooks the old name `tufte_blocks_notes_hide_title`, so the rename means both filters can run. The theme's is a subset of the plugin's, so they don't conflict.)

- [ ] **Step 1: Write `tests/test-formats.php`.**
  - `current_theme_supports( 'post-formats' )` is true, and `get_theme_support( 'post-formats' )[0]` equals `tufte_blocks_post_formats()`.
  - A Status post appears in a `new WP_Query( array( 'post_type' => 'post' ) )` that runs through `pre_get_posts` as the main home query (the theme no longer excludes it). Run this check only when Tufte Formats is inactive; otherwise mark it skipped.
  - `tufte_blocks_format_hide_title( '<h2>x</h2>' )` returns `''` inside the loop of a Status post, and returns its input for a Standard post.
  - `in_array( 'format-status', get_post_class( '', $status_id ), true )` (Review focus 4: the class the CSS keys on exists).
  - `false === function_exists( 'tufte_blocks_notes_exclude_main' )` and `false === post_type_exists( 'project' )` when Tufte Projects is inactive.
- [ ] **Step 2: Run on the theme-only Studio site** (`~/Studio/tufte-blocks`, after pulling this branch). Expected: FAIL.
- [ ] **Step 3: Implement the deletions and `inc/formats.php`.**
- [ ] **Step 4: Run on both Studio sites.** Expected: on the theme-only site, all PASS. On derekhansonblog (Tufte Formats and Tufte Projects active), all PASS with the loop check skipped. Then compare the home page, `/archive/`, a note, `/projects/`, and a project against live. They should look the same.
- [ ] **Step 5: Commit** "Move Projects and the notes query out of the theme".

### Task 5: Post format presentation

**Files:**
- Create: `assets/css/formats.css`, and add it to `tufte_blocks_stylesheets()` as `'tufte-blocks-formats' => 'formats.css'`
- Modify: `templates/taxonomy-post_format.html` (replace the hardcoded "Notes" heading with `<!-- wp:query-title {"type":"archive"} /-->`)

Style only through `.format-<slug>` on post-template items and `.single-format-<slug>` on the body. Keep it to theme tokens (`var(--wp--preset--...)`), and use no new templates. The minimum per format:
- **status and aside:** no title gap, date in the theme's monospace small style, content at body size
- **quote:** the first `core/quote` gets the epigraph treatment
- **link:** the first link in the content reads as the entry's headline
- **image and gallery:** the first image goes full content width with its caption as a sidenote-style margin note on wide screens
- **audio and video:** the first embed goes full content width
- **chat:** paragraphs starting with `Name:` get the speaker in small caps (CSS `::first-line` won't work for this, so style `strong` at the start of each paragraph and document that convention in the readme)

- [ ] **Step 1:** Seed one post per format on the theme-only Studio site with `wp post create` and `wp post term set <id> post_format post-format-<slug>`.
- [ ] **Step 2:** Write `formats.css`, then check each post at 1280px and 390px wide, in dark and light, in the browser. Expected: nothing overflows at 390px, and no format shows an empty title gap.
- [ ] **Step 3: Commit** "Style every post format through core's format classes".

### Task 6: Compatibility check with Post Formats for Block Themes

- [ ] **Step 1:** On the theme-only Studio site, install and activate `post-formats-for-block-themes` from WordPress.org (local only).
- [ ] **Step 2:** Visit each seeded post from Task 5, plus a new Chat post made with its Chat Log block. Expected: its format templates render inside the theme's header, footer, and left margin, and the content column stays at 650px. Fix any clash in `formats.css` only, never with selectors that name her plugin.
- [ ] **Step 3:** Deactivate it and confirm Task 5's screenshots are unchanged. Commit any CSS fixes as "Keep formats tidy alongside Post Formats for Block Themes".

### Task 7: .org required items

Each item below fails the review as the theme stands today.

**Files:** `parts/footer.html`, `patterns/footer-default.php`, `functions.php`, `inc/performance.php`, `patterns/about-card.php`, `assets/images/`, `screenshot.png`, `readme.txt`, `style.css`, `.gitattributes`

- [ ] **Step 1: Remove the shortcode.** Delete `tufte_current_year_shortcode` and its `add_shortcode()` (themes can't ship shortcodes). In `patterns/footer-default.php`, print the year with `esc_html( gmdate( 'Y' ) )` beside `<!-- wp:site-title {"level":0,"isLink":false} /-->`, and make `parts/footer.html` a single `<!-- wp:pattern {"slug":"tufte-blocks/footer-default"} /-->` so the PHP runs.
- [ ] **Step 2: Fix the footer links.** The RSS link's `href="#"` becomes `esc_url( get_feed_link() )` in the pattern. Add the one required credit link: "Tufte Blocks theme" pointing at the Theme URI.
- [ ] **Step 3: Move the site-tuning out of `inc/performance.php`.** The prerender-on-hover speculation rules and the Threads `defer` change site behavior rather than presentation, so they move to the child theme (`derekhanson-blog`, a new `inc/performance.php` there, in its own small PR on the site repo). The font preload stays in the parent.
- [ ] **Step 4: Replace `assets/images/placeholder-portrait.jpg`** with a CC0 image or a plain SVG silhouette, unless Derek can show its license. Record its source in the readme.
- [ ] **Step 5: Make the screenshot 1200 × 900 (4:3).** Use the theme-only site's front page with neutral demo content and no promotional copy.
- [ ] **Step 6: Rewrite `readme.txt`.** Write a description without the Projects line. Add `== Copyright ==` (Tufte Blocks © 2024-2026 Derek Hanson, GPLv2 or later). Add `== Resources ==` with ET Book (MIT, © Dmitry Krasny, Bonnie Scranton, Edward Tufte, https://github.com/edwardtufte/et-book) and the portrait replacement. Add an upgrade notice for 2.0.0: "Projects moved to the Tufte Projects plugin; install it before updating if you use Projects." Confirm `Contributors:` is Derek's WordPress.org username.
- [ ] **Step 7: Check `style.css` tags against the allowed list** at https://make.wordpress.org/themes/handbook/review/required/theme-tags/ and add `post-formats`. Drop any tag the theme doesn't actually deliver.
- [ ] **Step 8: Keep repo-only files out of the zip.** Add `.gitattributes` `export-ignore` lines for `.github/`, `docs/`, `tests/`, `TODOS.md`, `README.md`, and `.gitignore`. Then build the zip with `git archive --format=zip --prefix=tufte-blocks/ -o ../tufte-blocks.zip HEAD` and list it with `unzip -l`. Expected: no hidden files, no `tests/`, no `docs/`.
- [ ] **Step 9: Run Theme Check.** Install the `theme-check` plugin on the theme-only Studio site, then run `wp theme-check run tufte-blocks` (or the Appearance screen if the CLI command isn't there). Expected: no REQUIRED or WARNING lines. Fix whatever it finds in this task.
- [ ] **Step 10: Turn on `WP_DEBUG` and `WP_DEBUG_LOG`** on the theme-only site, click through every template, and check the log. Expected: empty.
- [ ] **Step 11: Commit** "Meet the WordPress.org required items".

### Task 8: Ship 2.0.0

- [ ] **Step 1:** Bump to 2.0.0 (`style.css`, `TUFTE_BLOCKS_VERSION`, `readme.txt` stable tag and changelog). The changelog should cover: Projects moved to Tufte Projects; Status posts now appear in the blog loop unless a plugin like Tufte Formats keeps them out; every post format is supported and styled; the footer year no longer uses a shortcode.
- [ ] **Step 2:** On the derekhansonblog Studio site, run the theme, Tufte Projects, and child theme runners. Then do a visual pass of the home page, `/archive/` (all views), a note of each kind, `/projects/`, a project, an essay with footnotes, and `/follow/` against live. Expected: no visible difference from live except the footer credit link.
- [ ] **Step 3:** Open the PR with `wp-ship`. Merge only with Derek's go, and only after the child theme PR from Task 7 step 3 is live, so the prerender behavior doesn't drop out for a deploy cycle.
- [ ] **Step 4: Check the theme slug.** Confirm `tufte-blocks` is free on WordPress.org (`https://wordpress.org/themes/tufte-blocks/` returns 404), and record the result in the Workshop inbox. Submitting the theme is Derek's call and isn't part of this plan.
