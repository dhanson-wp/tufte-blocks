# Projects CPT Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the `project` CPT archive and single templates from the design handoff on derekhanson.blog, with every derived field (version, dates, requirements, links) synced from wordpress.org and GitHub into a local cache, and the theme bumped to 1.5.0.

**Architecture:** The Studio site's theme folder becomes the git working copy (the repo's `.git` is copied in). Project code moves out of `functions.php` into `inc/projects/`, organised as a field registry, two network adapters plus a shared header parser, a cron/REST/CLI sync job that writes one hidden meta key, a block-bindings source that resolves override → .org → GitHub → empty, and a `render_block` filter that drops any bound block whose value is empty. Templates are pure core blocks bound to `tufte-blocks/project-field`; prose lives in post content via three insertable patterns.

**Tech Stack:** WordPress 6.9 block theme (theme.json v3, block templates, Block Bindings API, `register_block_bindings_source`, WP-Cron, REST API, WP-CLI), PHP 8 (`declare(strict_types=1)`, Studio runs 8.5), vanilla `wp.element.createElement` JS with no build step, plain CSS. Tests are plain PHP files run with `wp eval-file` against the Studio site.

**Spec:** `docs/superpowers/specs/2026-09-14-projects-cpt-design.md`. Treat it as settled.

---

## Ground truth gathered before planning

These facts were verified on 2026-09-14 and the tasks below depend on them.

| Fact | Value |
|---|---|
| Site theme (runs in Studio, no git) | `/Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks/`, v1.3.0 |
| Repo theme (git, `main`, v1.4.4) | `/Users/derekhanson/Studio/tufte-blocks/wp-content/themes/tufte-blocks/` |
| Repo status | clean except untracked `docs/` (the spec and this plan) |
| Studio URL | `http://127.0.0.1:60438` (port changes on restart; see below) |
| Studio WordPress | 6.9.1, PHP 8.5.6 via WP-CLI, `DISABLE_WP_CRON` is true, timezone America/Chicago |
| WP-CLI | `/usr/local/bin/wp` 2.12.0. `wp --path=/Users/derekhanson/Studio/derekhansonblog option get siteurl` works while Studio runs. Prints a harmless `Deprecated` line from the phar; pipe through `grep -v Deprecated`. |
| `register_block_bindings_source` exists | yes |
| `safecss_filter_attr('aspect-ratio:1544/500')` | passes unchanged |
| Projects | Scroll Indicator 5285 (type term 1518 Plugin), Tufte Blocks 5284 (type term 1517 Theme); tool terms 1519 Claude, 1520 Cursor; both `featured_media: 0` |
| Existing meta | 5285 `project_github_url=https://github.com/dhanson-wp/scroll-indicator`, `project_demo_url=""`; 5284 `project_github_url=https://github.com/dhanson-wp/tufte-blocks`, `project_demo_url=https://derekhanson.blog` |
| `functions.php` diff (repo → site) | hunks `18c18` (version), `74a75,403` (project code), `259a589,596` (`tufte-projects` pattern category). Pure additions apart from the version line. |
| `patterns.css` diff | site appends a `PROJECT CARD` section after line 413 |
| Site-only files | `templates/archive-project.html`, `templates/single-project.html`, `patterns/project-card.php`, `assets/images/tools/*.svg` |
| Repo-only files | `.github/`, `.gitignore`, `docs/`, `parts/comments.html`, `patterns/newsletter-callout.php` |
| Repo wins on | `theme.json`, `assets/css/comments.css`, `templates/single.html`, `style.css` |
| .org API, `scroll-indicator` | version `1.0.2`, requires `6.4`, tested `7.0.4`, requires_php `7.4`, last_updated `2026-09-14 7:08pm GMT`, download `https://downloads.wordpress.org/plugin/scroll-indicator.1.0.2.zip`, support `https://wordpress.org/support/plugin/scroll-indicator/`, banners.high `https://ps.w.org/scroll-indicator/assets/banner-1544x500.png?rev=3695697`. No `license` key in the response. |
| GitHub `dhanson-wp/scroll-indicator` | default branch `trunk`, license spdx `NOASSERTION`, latest release `v1.0.1` published `2026-05-21T22:08:14Z`, asset `.../releases/download/v1.0.1/scroll-indicator-1.0.1.zip`. No `style.css` (404); header lives in `scroll-indicator.php` with `Version: 1.0.1`, `License: GPLv2 or later`, no `Tested up to`. |
| GitHub `dhanson-wp/tufte-blocks` | default branch `main`, license spdx `GPL-3.0`, latest release `v1.3.0` published `2026-02-27T18:49:41Z`, asset `.../releases/download/v1.3.0/tufte-blocks-1.3.0.zip`. `style.css` header: `Version: 1.4.4`, `Requires at least: 6.4`, `Tested up to: 6.9`, `Requires PHP: 7.4`, `License: GNU General Public License v2 or later`. |
| GitHub rate limit | 60/hour unauthenticated. The sync makes at most 4 requests per project (repo, raw header, optional second raw header, releases/latest). |

**Finding the Studio port** whenever a task says `$PORT`:

```bash
ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1
```

**PHP and WP-CLI.** Rosetta is gone from this Mac (macOS 27), so the Intel Homebrew `php` at `/usr/local/bin/php` no longer runs and neither does the bare `wp` command. Use the arm64 PHP that Studio ships, which has pdo_sqlite and curl and runs the existing `wp` phar against the site. Define both once per shell:

```bash
export STUDIO_PHP=/Applications/Studio.app/Contents/Resources/php-bin/8.4.25-studio-3/php
phplint() { "$STUDIO_PHP" -l "$@"; }
wpcli() { "$STUDIO_PHP" /usr/local/bin/wp --path=/Users/derekhanson/Studio/derekhansonblog --require=/Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks/tests/allow-hosts.php "$@" 2>&1 | grep -v Deprecated; }
```

**Outbound HTTP.** Studio's `wp-config.php` defines `WP_HTTP_BLOCK_EXTERNAL`, for the web context and WP-CLI alike. `tests/allow-hosts.php` (loaded by the `wpcli` helper above via `--require`, which runs before wp-config) defines `WP_ACCESSIBLE_HOSTS` for the .org and GitHub hosts, so CLI sync runs fetch for real. The running Studio site stays blocked, so the sidebar's "Refresh now" button shows the recorded fetch errors locally instead of new values; that is the error path working, not a bug. Production on WordPress.com has no such block.

Wherever a task says `php -l`, use `phplint`. The Studio version folder name may change after a Studio update; `ls /Applications/Studio.app/Contents/Resources/php-bin/` shows the current one.

**Host header.** WordPress redirects `127.0.0.1:PORT` to `derekhansonblog.wp.local`, which curl cannot follow, so every curl against the site must send `-H "Host: derekhansonblog.wp.local"`. The commands below include it.

**Working directory for every task from Task 1.4 onward:** `/Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks` (call it `$THEME`). Tasks 1.1 to 1.3 run in the repo checkout.

**Voice note.** The handoff copy uses em dashes in the archive intro and several feature cards. Derek's global voice rule forbids em dashes, so every piece of copy in this plan has been rewritten with commas, semicolons, or a full stop. Wording is otherwise unchanged. Raise this at the milestone 1 check-in.

---

## File structure

Files created or rewritten by this plan, with the one job each has:

```
functions.php                         requires inc/projects/*.php in order; version constant
inc/projects/post-type.php            CPT, taxonomies, one-time rewrite flush (moved verbatim)
inc/projects/tool-icons.php           tool_icon term meta, admin UI, term-link icon filter (moved verbatim)
inc/projects/fields.php               THE registry array + register_post_meta for every key + formatters
inc/projects/header-parser.php        tufte_blocks_parse_file_header(): string -> array
inc/projects/sources/wporg.php        tufte_blocks_project_fetch_wporg(slug) and _map_wporg(array)
inc/projects/sources/github.php       tufte_blocks_project_fetch_github(url) and _map_github(...)
inc/projects/sync.php                 cron event, sync one/all, banner sideload, REST route, WP-CLI
inc/projects/bindings.php             resolver, tufte-blocks/project-field source, empty-value filter
inc/projects/render.php               featured-image caption filter for projects
inc/projects/editor.php               enqueue sidebar script for project screens only + inline config
assets/js/project-fields.js           PluginDocumentSettingPanel, createElement, no JSX
templates/archive-project.html        query grid of cards, bound version and date
templates/single-project.html         back link, hero, figure, content, details panel, project nav
patterns/project-card.php             card matching the archive template
patterns/project-features.php         eyebrow + H2 + six-card grid
patterns/project-install-steps.php    H2 + numbered steps
patterns/project-faq.php              H2 + four details blocks
assets/css/patterns.css               PROJECT section replaces PROJECT CARD section
tests/run.php                         runner: loads the three test files, prints PASS/FAIL, exits 1 on failure
tests/test-header-parser.php
tests/test-adapters.php
tests/test-resolver.php
tests/fixtures/wporg-scroll-indicator.json
tests/fixtures/github-repo-tufte-blocks.json
tests/fixtures/github-release-tufte-blocks.json
tests/fixtures/github-repo-scroll-indicator.json
tests/fixtures/header-style-css.txt
tests/fixtures/header-plugin-php.txt
.github/workflows/deploy.yml          exclude docs, tests
```

Function naming: everything is prefixed `tufte_blocks_project_` (fields, sync, resolver) except the two moved files, which keep their existing `tufte_blocks_` names, and the header parser `tufte_blocks_parse_file_header`.

Meta key convention: every registry key `k` stores at `project_k`. The cache is `_project_source_cache`.

---

## Milestone 1: Reconcile the two copies

No visible change. Ends with the site theme folder as a git checkout on branch `projects-cpt`, the existing project work committed, and the site still rendering.

### Task 1.1: Back up the site theme folder

**Files:** none in the repo. Creates `/Users/derekhanson/Studio/derekhansonblog/backups/tufte-blocks-pre-git-2026-09-14/`.

The backup must live outside `wp-content/themes/`, otherwise WordPress registers it as a second theme.

- [ ] **Step 1: Copy the folder**

```bash
mkdir -p /Users/derekhanson/Studio/derekhansonblog/backups
cp -R /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks \
      /Users/derekhanson/Studio/derekhansonblog/backups/tufte-blocks-pre-git-2026-09-14
```

- [ ] **Step 2: Verify the copy is byte-identical**

```bash
diff -rq /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks \
         /Users/derekhanson/Studio/derekhansonblog/backups/tufte-blocks-pre-git-2026-09-14 && echo IDENTICAL
```

Expected: `IDENTICAL`.

### Task 1.2: Create the branch and commit the docs in the repo checkout

**Files:**
- Commit: `docs/superpowers/specs/2026-09-14-projects-cpt-design.md`, `docs/superpowers/plans/2026-09-14-projects-cpt.md`

Run in `/Users/derekhanson/Studio/tufte-blocks/wp-content/themes/tufte-blocks`.

- [ ] **Step 1: Confirm the repo is clean apart from docs**

```bash
cd /Users/derekhanson/Studio/tufte-blocks/wp-content/themes/tufte-blocks
git status --short
```

Expected: exactly `?? docs/`. If anything else shows, stop and ask Derek.

- [ ] **Step 2: Create the branch**

```bash
git checkout -b projects-cpt
```

- [ ] **Step 3: Commit the spec and plan**

```bash
git add docs
git commit -m "Add Projects CPT design spec and implementation plan

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 1.3: Exclude docs and tests from the deploy mirror

**Files:**
- Modify: `.github/workflows/deploy.yml`

Still in the repo checkout. Doing this before the `.git` move keeps the deploy fix as its own commit on the branch.

- [ ] **Step 1: Add four exclude globs after the README.md line**

```bash
python3 - <<'EOF'
p='.github/workflows/deploy.yml'
s=open(p).read()
old="              --exclude-glob README.md \\\n"
new=old+"              --exclude-glob docs \\\n              --exclude-glob docs/* \\\n              --exclude-glob tests \\\n              --exclude-glob tests/* \\\n"
assert old in s
open(p,'w').write(s.replace(old,new,1))
EOF
grep -n "exclude-glob" .github/workflows/deploy.yml
```

Expected: the list now ends `README.md`, `docs`, `docs/*`, `tests`, `tests/*`, `'*.swp'`.

- [ ] **Step 2: Commit**

```bash
git add .github/workflows/deploy.yml
git commit -m "Exclude docs and tests from the SFTP deploy mirror

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 1.4: Move `.git` into the site theme folder

**Files:** copies `.git/` from the repo checkout into `$THEME`.

- [ ] **Step 1: Copy the git directory**

```bash
cp -R /Users/derekhanson/Studio/tufte-blocks/wp-content/themes/tufte-blocks/.git \
      /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks/.git
cd /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks
git branch --show-current
```

Expected: `projects-cpt`.

- [ ] **Step 2: Look at the status before touching anything**

```bash
git status --short
```

Expected, in some order:

```
 D .github/workflows/deploy.yml
 D .gitignore
 M assets/css/comments.css
 M assets/css/patterns.css
 D docs/superpowers/plans/2026-09-14-projects-cpt.md
 D docs/superpowers/specs/2026-09-14-projects-cpt-design.md
 M functions.php
 D parts/comments.html
 D patterns/newsletter-callout.php
 M style.css
 M templates/single.html
 M theme.json
?? assets/images/tools/
?? patterns/project-card.php
?? templates/archive-project.html
?? templates/single-project.html
```

`.DS_Store` files are ignored by `.gitignore` once it is restored. If anything else is listed, stop and ask Derek.

### Task 1.5: Restore everything the repo is ahead on

**Files:**
- Restore from HEAD: `.github/`, `.gitignore`, `docs/`, `parts/comments.html`, `patterns/newsletter-callout.php`, `theme.json`, `assets/css/comments.css`, `templates/single.html`, `style.css`

- [ ] **Step 1: Restore deleted and repo-wins files**

```bash
git checkout -- .github .gitignore docs parts/comments.html patterns/newsletter-callout.php \
                theme.json assets/css/comments.css templates/single.html style.css
git status --short
```

Expected:

```
 M assets/css/patterns.css
 M functions.php
?? assets/images/tools/
?? patterns/project-card.php
?? templates/archive-project.html
?? templates/single-project.html
```

- [ ] **Step 2: Make the functions.php diff a pure addition**

The site copy still says `1.3.0`. Set it to the repo's version so the only remaining hunks are the project code.

```bash
sed -i '' "s/define( 'TUFTE_BLOCKS_VERSION', '1.3.0' );/define( 'TUFTE_BLOCKS_VERSION', '1.4.4' );/" functions.php
git diff --stat functions.php assets/css/patterns.css
git diff functions.php | grep -E "^-[^-]" || echo "NO REMOVED LINES"
git diff assets/css/patterns.css | grep -E "^-[^-]" || echo "NO REMOVED LINES"
```

Expected: both greps print `NO REMOVED LINES`. `git diff --stat` shows roughly `functions.php | 337 +` and `patterns.css | 98 +`.

- [ ] **Step 3: Verify the site still renders**

```bash
PORT=$(ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1)
curl -s -H "Host: derekhansonblog.wp.local" -o /dev/null -w "home %{http_code}\n" "http://$PORT/"
curl -s -H "Host: derekhansonblog.wp.local" -o /dev/null -w "archive %{http_code}\n" -L "http://$PORT/projects/"
curl -s -H "Host: derekhansonblog.wp.local" -o /dev/null -w "single %{http_code}\n" -L "http://$PORT/projects/scroll-indicator/"
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/" | grep -o 'tufte-blocks-patterns-css[^>]*ver=[0-9.]*' | head -1
```

Expected: three `200` lines and a stylesheet tag carrying `ver=1.4.4`.

### Task 1.6: Commit the existing project work

- [ ] **Step 1: Stage and commit**

```bash
git add functions.php assets/css/patterns.css assets/images/tools patterns/project-card.php \
        templates/archive-project.html templates/single-project.html
git commit -m "Bring the Projects CPT work from the Studio site copy into the repo

CPT, project_type and project_tool taxonomies, project_github_url and
project_demo_url meta, tool_icon term meta with its media uploader,
archive and single templates, project card pattern, and tool SVGs.
No behavior change on the live site until templates are finished.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
git status --short
git log --oneline -4
```

Expected: clean status; log shows this commit, the deploy exclude, the docs commit, then `85f48b0 Add newsletter-callout pattern (v1.4.4)`.

- [ ] **Step 2: Bring the other clone up to date so it is not misleading**

```bash
cd /Users/derekhanson/Studio/tufte-blocks/wp-content/themes/tufte-blocks
git checkout main
git status --short
```

Expected: on `main`, clean (the `docs/` folder disappears there because it was committed on `projects-cpt`). This clone now lags the site checkout and is only for theme-only testing.

### CHECKPOINT: stop and check in with Derek

Report: backup location, the three commits on `projects-cpt`, that the site renders at 1.4.4 with comments restored, and the em-dash copy note. Do not start Milestone 2 until Derek says go.

---

## Milestone 2: Split project code out of `functions.php`

No behavior change. Three files move verbatim; `functions.php` gains a loader.

### Task 2.1: Create `inc/projects/post-type.php`

**Files:**
- Create: `inc/projects/post-type.php`
- Modify: `functions.php` (remove lines 75 to 166 and 389 to 401 of the current file: CPT, taxonomies, rewrite flush)

- [ ] **Step 1: Create the file with the CPT, taxonomies, and rewrite flush moved verbatim**

Write `inc/projects/post-type.php` with this header, then paste the bodies of `tufte_blocks_register_project_post_type()`, `tufte_blocks_register_project_taxonomies()`, and the anonymous `flush_rewrite_rules` init callback exactly as they appear in `functions.php` today (including their `add_action` lines and docblocks):

```php
<?php
/**
 * Project post type and taxonomies.
 *
 * @package Tufte_Blocks
 * @since 1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ... tufte_blocks_register_project_post_type() + add_action( 'init', ... )
// ... tufte_blocks_register_project_taxonomies() + add_action( 'init', ... )
// ... add_action( 'init', function (): void { ...rewrite flush... }, 99 );
```

Use this to do the move mechanically rather than retyping:

```bash
cd /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks
mkdir -p inc/projects
{
  printf '%s\n' '<?php' '/**' ' * Project post type and taxonomies.' ' *' ' * @package Tufte_Blocks' ' * @since 1.3.0' ' */' '' 'declare(strict_types=1);' '' "if ( ! defined( 'ABSPATH' ) ) {" '	exit;' '}' ''
  sed -n '74,166p' functions.php
  echo
  sed -n '389,401p' functions.php
} > inc/projects/post-type.php
phplint inc/projects/post-type.php
```

Expected: `No syntax errors detected`. Open the file and confirm it starts with the `Register the Projects custom post type` docblock and ends with the `}, 99 );` of the rewrite flush.

- [ ] **Step 2: Remove those ranges from functions.php (highest range first so line numbers hold)**

```bash
sed -i '' '389,402d' functions.php
sed -i '' '74,167d' functions.php
phplint functions.php
grep -n "tufte_blocks_register_project_post_type\|flush_rewrite_rules" functions.php || echo REMOVED
```

Expected: `No syntax errors detected` and `REMOVED`.

### Task 2.2: Create `inc/projects/tool-icons.php`

**Files:**
- Create: `inc/projects/tool-icons.php`
- Modify: `functions.php` (remove the term-meta block: from the `Register tool icon term meta` docblock through `add_filter( 'render_block', 'tufte_blocks_render_tool_icons', 10, 2 );`)

- [ ] **Step 1: Find the current line range**

```bash
grep -n "Register tool icon term meta\|add_filter( 'render_block', 'tufte_blocks_render_tool_icons'" functions.php
```

Note the two line numbers; call them `START` (the `/**` line is one above the grep hit) and `END`.

- [ ] **Step 2: Move the block**

```bash
START=$(( $(grep -n "Register tool icon term meta" functions.php | cut -d: -f1) - 1 ))
END=$(grep -n "add_filter( 'render_block', 'tufte_blocks_render_tool_icons'" functions.php | cut -d: -f1)
{
  printf '%s\n' '<?php' '/**' ' * Tool icons for the project_tool taxonomy.' ' *' ' * Term meta, admin media uploader, and the frontend filter that prepends' ' * an icon to each project_tool term link.' ' *' ' * @package Tufte_Blocks' ' * @since 1.3.0' ' */' '' 'declare(strict_types=1);' '' "if ( ! defined( 'ABSPATH' ) ) {" '	exit;' '}' ''
  sed -n "${START},${END}p" functions.php
} > inc/projects/tool-icons.php
sed -i '' "${START},$((END+1))d" functions.php
phplint inc/projects/tool-icons.php && phplint functions.php
grep -c "tool_icon" functions.php || echo "0 left in functions.php"
```

Expected: both lint clean; `0 left in functions.php`.

### Task 2.3: Create `inc/projects/fields.php` with the existing meta registration

**Files:**
- Create: `inc/projects/fields.php` (temporary content, rewritten in Milestone 3)
- Modify: `functions.php` (remove `tufte_blocks_register_project_meta`)

- [ ] **Step 1: Move the meta registration**

```bash
START=$(( $(grep -n "Register project meta fields for Block Bindings API" functions.php | cut -d: -f1) - 1 ))
END=$(grep -n "add_action( 'init', 'tufte_blocks_register_project_meta' );" functions.php | cut -d: -f1)
{
  printf '%s\n' '<?php' '/**' ' * Project fields.' ' *' ' * @package Tufte_Blocks' ' * @since 1.3.0' ' */' '' 'declare(strict_types=1);' '' "if ( ! defined( 'ABSPATH' ) ) {" '	exit;' '}' ''
  sed -n "${START},${END}p" functions.php
} > inc/projects/fields.php
sed -i '' "${START},$((END+1))d" functions.php
phplint inc/projects/fields.php && phplint functions.php
```

- [ ] **Step 2: Confirm functions.php now matches the repo's 1.4.4 file plus only the pattern category**

```bash
git diff main -- functions.php
```

Expected: the only hunk is the eight-line `tufte-projects` pattern category addition inside `tufte_blocks_register_pattern_categories()`. If any project function body remains, move it.

### Task 2.4: Add the loader to functions.php

**Files:**
- Modify: `functions.php` (after the `TUFTE_BLOCKS_VERSION` define)

- [ ] **Step 1: Insert the loader**

```bash
python3 - <<'EOF'
p='functions.php'
s=open(p).read()
anchor="define( 'TUFTE_BLOCKS_VERSION', '1.4.4' );\n"
loader=anchor+"""
/**
 * Projects feature: post type, taxonomies, fields, sync, bindings, editor panel.
 *
 * Files are loaded in dependency order. The registry (fields.php) must load
 * before anything that reads it.
 *
 * @since 1.5.0
 */
foreach ( array(
	'post-type',
	'tool-icons',
	'fields',
) as $tufte_blocks_projects_file ) {
	require_once get_template_directory() . '/inc/projects/' . $tufte_blocks_projects_file . '.php';
}
unset( $tufte_blocks_projects_file );
"""
assert anchor in s
open(p,'w').write(s.replace(anchor,loader,1))
EOF
phplint functions.php
```

- [ ] **Step 2: Verify nothing changed on the site**

```bash
PORT=$(ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1)
curl -s -H "Host: derekhansonblog.wp.local" -L "http://$PORT/projects/" | grep -c "tufte-project-card"
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project/5285?_fields=meta" | python3 -c "import json,sys;print(json.load(sys.stdin)['meta']['project_github_url'])"
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project_tool/1519?_fields=meta" | python3 -c "import json,sys;print(json.load(sys.stdin)['meta'])"
```

Expected: `2`, `https://github.com/dhanson-wp/scroll-indicator`, `{'tool_icon': 5287}`.

- [ ] **Step 3: Commit**

```bash
git add functions.php inc/projects
git commit -m "Split project code out of functions.php into inc/projects/

post-type.php, tool-icons.php, and fields.php are moved verbatim. No
behavior change.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 3: Field registry, manual meta keys, override keys

### Task 3.1: Write the registry and register every meta key

**Files:**
- Rewrite: `inc/projects/fields.php`

- [ ] **Step 1: Replace fields.php entirely**

```php
<?php
/**
 * Project field registry.
 *
 * One array is the source of truth for every project field. It drives
 * register_post_meta(), the editor sidebar, the source adapters' output
 * mapping, and the field resolver. Adding a field is a one-line change here.
 *
 * Every field `k` stores at meta key `project_k`. For manual fields that is
 * the value; for derived fields it is an optional override that wins when
 * non-empty.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The registry.
 *
 * Each entry: label, type ('text'|'url'), group ('source'|'editorial'|
 * 'release'|'requirements'|'links'), manual (bool), and an optional
 * 'format' callback applied to the resolved value for display.
 *
 * @return array<string, array{label:string,type:string,group:string,manual:bool,format?:string}>
 */
function tufte_blocks_project_fields(): array {
	return array(
		// Manual: identify the sources the sync job runs on.
		'wporg_slug'      => array(
			'label'  => __( 'WordPress.org slug', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'source',
			'manual' => true,
		),
		'github_url'      => array(
			'label'  => __( 'GitHub repository URL', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'source',
			'manual' => true,
		),
		// Manual: editorial.
		'tagline'         => array(
			'label'  => __( 'Tagline', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'editorial',
			'manual' => true,
		),
		'demo_url'        => array(
			'label'  => __( 'Demo URL', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'editorial',
			'manual' => true,
		),
		// Derived: release.
		'version'         => array(
			'label'  => __( 'Version', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
		),
		'release_date'    => array(
			'label'  => __( 'Released', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_month_year',
		),
		'license'         => array(
			'label'  => __( 'License', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'release',
			'manual' => false,
		),
		// Derived: requirements.
		'requires_wp'     => array(
			'label'  => __( 'WordPress', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_or_higher',
		),
		'tested_up_to'    => array(
			'label'  => __( 'Tested to', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
		),
		'requires_php'    => array(
			'label'  => __( 'PHP', 'tufte-blocks' ),
			'type'   => 'text',
			'group'  => 'requirements',
			'manual' => false,
			'format' => 'tufte_blocks_project_format_or_higher',
		),
		// Derived: links.
		'link_download'   => array(
			'label'  => __( 'Download', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_directory'  => array(
			'label'  => __( 'Plugin directory', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_support'    => array(
			'label'  => __( 'Support', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
		'link_translate'  => array(
			'label'  => __( 'Translate', 'tufte-blocks' ),
			'type'   => 'url',
			'group'  => 'links',
			'manual' => false,
		),
	);
}

/**
 * Meta key for a registry key.
 *
 * @param string $key Registry key.
 * @return string
 */
function tufte_blocks_project_meta_key( string $key ): string {
	return 'project_' . $key;
}

/**
 * Keys of derived (synced) fields only.
 *
 * @return string[]
 */
function tufte_blocks_project_derived_keys(): array {
	return array_keys(
		array_filter(
			tufte_blocks_project_fields(),
			static fn( array $field ): bool => ! $field['manual']
		)
	);
}

/**
 * Sanitize a field value according to its registry type.
 *
 * Unknown keys sanitize as text. Never returns null.
 *
 * @param string $key   Registry key.
 * @param mixed  $value Raw value.
 * @return string
 */
function tufte_blocks_project_sanitize_field( string $key, $value ): string {
	$fields = tufte_blocks_project_fields();
	$type   = $fields[ $key ]['type'] ?? 'text';
	$value  = is_scalar( $value ) ? trim( (string) $value ) : '';

	if ( '' === $value ) {
		return '';
	}

	return 'url' === $type ? esc_url_raw( $value ) : sanitize_text_field( $value );
}

/**
 * Register one post meta key per registry field, plus the hidden source cache.
 *
 * @since 1.5.0
 * @return void
 */
function tufte_blocks_project_register_meta(): void {
	$auth = static fn(): bool => current_user_can( 'edit_posts' );

	foreach ( tufte_blocks_project_fields() as $key => $field ) {
		register_post_meta(
			'project',
			tufte_blocks_project_meta_key( $key ),
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => static fn( $value ): string => tufte_blocks_project_sanitize_field( $key, $value ),
				'auth_callback'     => $auth,
			)
		);
	}

	// Hidden, never edited by hand, written only by the sync job.
	register_post_meta(
		'project',
		'_project_source_cache',
		array(
			'show_in_rest'  => false,
			'single'        => true,
			'type'          => 'array',
			'default'       => array(),
			'auth_callback' => '__return_false',
		)
	);
}
add_action( 'init', 'tufte_blocks_project_register_meta' );

/**
 * Format "6.4" as "6.4 or higher". Leaves anything that is not a bare
 * version number alone, so overrides typed as prose pass through.
 *
 * @param string $value Resolved value.
 * @return string
 */
function tufte_blocks_project_format_or_higher( string $value ): string {
	if ( preg_match( '/^\d+(\.\d+)*$/', $value ) ) {
		/* translators: %s: version number */
		return sprintf( __( '%s or higher', 'tufte-blocks' ), $value );
	}
	return $value;
}

/**
 * Format any parseable date as "Sep 2026". Unparseable values pass through.
 *
 * @param string $value Resolved value.
 * @return string
 */
function tufte_blocks_project_format_month_year( string $value ): string {
	$timestamp = strtotime( $value );
	if ( false === $timestamp ) {
		return $value;
	}
	// Format in UTC: a month-only value like "Sep 2026" parses as the 1st at
	// midnight UTC, and a site timezone west of UTC would shift it to August.
	return wp_date( 'M Y', $timestamp, new DateTimeZone( 'UTC' ) );
}
```

- [ ] **Step 2: Lint and check the keys register over REST**

```bash
phplint inc/projects/fields.php
PORT=$(ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1)
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project/5285?_fields=meta" | python3 -c "
import json,sys; m=json.load(sys.stdin)['meta']
print(sorted(k for k in m if k.startswith('project_')))"
```

Expected: 14 keys: `project_demo_url, project_github_url, project_license, project_link_directory, project_link_download, project_link_support, project_link_translate, project_release_date, project_requires_php, project_requires_wp, project_tagline, project_tested_up_to, project_version, project_wporg_slug`. `_project_source_cache` must NOT appear.

- [ ] **Step 3: Check the formatters from WP-CLI**

```bash
wpcli eval 'echo tufte_blocks_project_format_or_higher("6.4"), "|", tufte_blocks_project_format_or_higher("6.4 or higher"), "|", tufte_blocks_project_format_month_year("2026-09-14 7:08pm GMT"), "|", tufte_blocks_project_format_month_year("Sep 2026"), "|", tufte_blocks_project_format_month_year("soon"), "\n";'
```

Expected: `6.4 or higher|6.4 or higher|Sep 2026|Sep 2026|soon`.

- [ ] **Step 4: Commit**

```bash
git add inc/projects/fields.php
git commit -m "Add the project field registry and register every meta key

Fourteen project_* keys (four manual, ten overrides) and the hidden
_project_source_cache. Formatters for 'or higher' and 'Mon YYYY'.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 3.2: Seed the .org slug on Scroll Indicator

The sync job in Milestone 5 needs an identifier to run on. Write it now through REST so the value round-trips through the sanitizer. This needs a logged-in nonce, so use WP-CLI instead, which is the one write WP-CLI makes to the Studio database in this plan. Take a copy of the database first.

- [ ] **Step 1: Back up the SQLite file, then set the slug**

```bash
cp /Users/derekhanson/Studio/derekhansonblog/wp-content/database/.ht.sqlite \
   /Users/derekhanson/Studio/derekhansonblog/backups/ht-sqlite-2026-09-14.bak
wpcli post meta update 5285 project_wporg_slug scroll-indicator
PORT=$(ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1)
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project/5285?_fields=meta" | python3 -c "import json,sys;print(json.load(sys.stdin)['meta']['project_wporg_slug'])"
```

Expected: `Success: Updated custom field 'project_wporg_slug'.` then `scroll-indicator`. If the update errors with a database lock, stop Studio, rerun, restart Studio.

---

## Milestone 4: Header parser and source adapters, with tests

Pure PHP. Adapters are split into a `fetch` function (network) and a `map` function (pure), so tests exercise `map` on fixtures and never touch the network.

### Task 4.1: Test runner and header parser (TDD)

**Files:**
- Create: `tests/run.php`, `tests/test-header-parser.php`, `tests/fixtures/header-style-css.txt`, `tests/fixtures/header-plugin-php.txt`
- Create: `inc/projects/header-parser.php`
- Modify: `functions.php` loader list

- [ ] **Step 1: Write the runner**

`tests/run.php`:

```php
<?php
/**
 * Minimal test runner. Run with:
 *   wp --path=/Users/derekhanson/Studio/derekhansonblog eval-file tests/run.php
 * Prints one PASS/FAIL line per case and exits non-zero on any failure.
 *
 * Note: WP-CLI eval()s this file inside a method, so it cannot declare
 * strict_types and its counters must live in $GLOBALS.
 *
 * @package Tufte_Blocks
 */

$GLOBALS['tufte_tests_failed'] = 0;
$GLOBALS['tufte_tests_passed'] = 0;

/**
 * Assert two values are identical.
 *
 * @param mixed  $expected Expected.
 * @param mixed  $actual   Actual.
 * @param string $name     Case name.
 */
function tufte_assert_same( $expected, $actual, string $name ): void {
	global $tufte_tests_failed, $tufte_tests_passed;
	if ( $expected === $actual ) {
		++$tufte_tests_passed;
		echo "PASS  {$name}\n";
		return;
	}
	++$tufte_tests_failed;
	echo "FAIL  {$name}\n";
	echo '      expected: ' . var_export( $expected, true ) . "\n";
	echo '      actual:   ' . var_export( $actual, true ) . "\n";
}

/**
 * Load a fixture file's contents.
 *
 * @param string $name File name inside tests/fixtures.
 * @return string
 */
function tufte_fixture( string $name ): string {
	return (string) file_get_contents( __DIR__ . '/fixtures/' . $name );
}

/**
 * Load and decode a JSON fixture.
 *
 * @param string $name File name inside tests/fixtures.
 * @return array
 */
function tufte_fixture_json( string $name ): array {
	return (array) json_decode( tufte_fixture( $name ), true );
}

foreach ( glob( __DIR__ . '/test-*.php' ) as $tufte_test_file ) {
	echo "\n== " . basename( $tufte_test_file ) . "\n";
	require $tufte_test_file;
}

echo "\n{$GLOBALS['tufte_tests_passed']} passed, {$GLOBALS['tufte_tests_failed']} failed\n";
if ( $GLOBALS['tufte_tests_failed'] > 0 ) {
	exit( 1 );
}
```

- [ ] **Step 2: Write the fixtures**

`tests/fixtures/header-style-css.txt` (copy of the real Tufte Blocks header, LF endings):

```
/*
Theme Name: Tufte Blocks
Theme URI: https://github.com/dhanson-wp/tufte-blocks
Author: Derek Hanson
Author URI: https://derekhanson.blog
Description: A typography-first WordPress block theme inspired by Edward Tufte's design principles.
Version: 1.4.4
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 7.4
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tufte-blocks
*/

body { color: red; }
```

`tests/fixtures/header-plugin-php.txt` (copy of the real Scroll Indicator header; note no `Tested up to`):

```
<?php
/**
 * Plugin Name:       Scroll Indicator
 * Description:       An animated scroll indicator with multiple icon styles that encourages users to scroll down the page.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Derek Hanson
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       scroll-indicator
 *
 * @package ScrollIndicator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

- [ ] **Step 3: Write the failing parser tests**

`tests/test-header-parser.php`:

```php
<?php
declare(strict_types=1);

$style = tufte_blocks_parse_file_header( tufte_fixture( 'header-style-css.txt' ) );
tufte_assert_same( '1.4.4', $style['version'], 'style.css: version' );
tufte_assert_same( '6.4', $style['requires_wp'], 'style.css: requires at least' );
tufte_assert_same( '6.9', $style['tested_up_to'], 'style.css: tested up to' );
tufte_assert_same( '7.4', $style['requires_php'], 'style.css: requires php' );
tufte_assert_same( 'GNU General Public License v2 or later', $style['license'], 'style.css: license' );
tufte_assert_same( 'Tufte Blocks', $style['name'], 'style.css: theme name' );

$plugin = tufte_blocks_parse_file_header( tufte_fixture( 'header-plugin-php.txt' ) );
tufte_assert_same( '1.0.1', $plugin['version'], 'plugin.php: version' );
tufte_assert_same( '6.4', $plugin['requires_wp'], 'plugin.php: requires at least' );
tufte_assert_same( '', $plugin['tested_up_to'], 'plugin.php: missing tested up to is empty' );
tufte_assert_same( 'GPLv2 or later', $plugin['license'], 'plugin.php: license with leading asterisks and padding' );
tufte_assert_same( 'Scroll Indicator', $plugin['name'], 'plugin.php: plugin name' );

$crlf = str_replace( "\n", "\r\n", tufte_fixture( 'header-style-css.txt' ) );
$win  = tufte_blocks_parse_file_header( $crlf );
tufte_assert_same( '1.4.4', $win['version'], 'CRLF: version has no trailing CR' );
tufte_assert_same( 'GNU General Public License v2 or later', $win['license'], 'CRLF: license has no trailing CR' );

$none = tufte_blocks_parse_file_header( "/*\nTheme Name: Bare\n*/\n" );
tufte_assert_same( '', $none['version'], 'missing fields: version empty' );
tufte_assert_same( '', $none['requires_wp'], 'missing fields: requires_wp empty' );
tufte_assert_same( '', $none['license'], 'missing fields: license empty' );

$junk = tufte_blocks_parse_file_header( "<html><body>404 Not Found</body></html>" );
tufte_assert_same( '', $junk['version'], 'not a header: version empty' );
tufte_assert_same( '', $junk['name'], 'not a header: name empty' );
tufte_assert_same( array( 'name', 'version', 'requires_wp', 'tested_up_to', 'requires_php', 'license' ), array_keys( $junk ), 'always returns all six keys' );

$tricky = tufte_blocks_parse_file_header( "/*\nVersion: 2.0.0\nDescription: Version: not this\n*/" );
tufte_assert_same( '2.0.0', $tricky['version'], 'takes the first match at line start only' );

$empty = tufte_blocks_parse_file_header( '' );
tufte_assert_same( '', $empty['version'], 'empty input: version empty' );
```

- [ ] **Step 4: Run it and watch it fail**

```bash
cd /Users/derekhanson/Studio/derekhansonblog/wp-content/themes/tufte-blocks
wpcli eval-file tests/run.php
```

Expected: fatal `Call to undefined function tufte_blocks_parse_file_header()`.

- [ ] **Step 5: Write the parser**

`inc/projects/header-parser.php`:

```php
<?php
/**
 * WordPress file header parser.
 *
 * Reads the standard header block from a style.css or main plugin file and
 * returns the six fields the project sync cares about. Missing fields are
 * empty strings, never wrong values. This is the one piece of real string
 * handling in the feature and it is covered by tests/test-header-parser.php.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse a WordPress file header.
 *
 * Mirrors core's get_file_data(): a header line is `Label: value` at the
 * start of a line, optionally preceded by whitespace, `*`, `#`, or `/`.
 * Only the first 8 KB is inspected, as core does.
 *
 * @param string $contents Raw file contents.
 * @return array{name:string,version:string,requires_wp:string,tested_up_to:string,requires_php:string,license:string}
 */
function tufte_blocks_parse_file_header( string $contents ): array {
	$labels = array(
		'name'         => array( 'Theme Name', 'Plugin Name' ),
		'version'      => array( 'Version' ),
		'requires_wp'  => array( 'Requires at least' ),
		'tested_up_to' => array( 'Tested up to' ),
		'requires_php' => array( 'Requires PHP' ),
		'license'      => array( 'License' ),
	);

	$head = str_replace( "\r", "\n", substr( $contents, 0, 8 * 1024 ) );
	$out  = array();

	foreach ( $labels as $key => $names ) {
		$out[ $key ] = '';
		foreach ( $names as $name ) {
			// `License:` must not match `License URI:`, so require the colon right after the label.
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $name, '/' ) . ':(.*)$/mi', $head, $match ) ) {
				$out[ $key ] = trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $match[1] ) );
				break;
			}
		}
	}

	return $out;
}
```

- [ ] **Step 6: Add it to the loader and run the tests**

In `functions.php`, change the loader array to:

```php
foreach ( array(
	'post-type',
	'tool-icons',
	'fields',
	'header-parser',
) as $tufte_blocks_projects_file ) {
```

```bash
phplint inc/projects/header-parser.php
wpcli eval-file tests/run.php
```

Expected: every line `PASS`, ending `21 passed, 0 failed`.

- [ ] **Step 7: Commit**

```bash
git add tests inc/projects/header-parser.php functions.php
git commit -m "Add the file header parser with a wp eval-file test runner

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 4.2: wordpress.org adapter (TDD)

**Files:**
- Create: `inc/projects/sources/wporg.php`, `tests/fixtures/wporg-scroll-indicator.json`, `tests/test-adapters.php`
- Modify: `functions.php` loader

- [ ] **Step 1: Write the fixture**

`tests/fixtures/wporg-scroll-indicator.json` (trimmed from the real 2026-09-14 response; keep exactly these keys):

```json
{
  "name": "Scroll Indicator",
  "slug": "scroll-indicator",
  "version": "1.0.2",
  "author": "<a href=\"https://profiles.wordpress.org/dhansondesigns/\">Derek Hanson</a>",
  "requires": "6.4",
  "tested": "7.0.4",
  "requires_php": "7.4",
  "last_updated": "2026-09-14 7:08pm GMT",
  "added": "2026-09-14",
  "homepage": "",
  "download_link": "https://downloads.wordpress.org/plugin/scroll-indicator.1.0.2.zip",
  "support_url": "https://wordpress.org/support/plugin/scroll-indicator/",
  "banners": {
    "low": "https://ps.w.org/scroll-indicator/assets/banner-772x250.png?rev=3695697",
    "high": "https://ps.w.org/scroll-indicator/assets/banner-1544x500.png?rev=3695697"
  }
}
```

- [ ] **Step 2: Write the failing tests**

`tests/test-adapters.php` (first half; the GitHub half is appended in Task 4.3):

```php
<?php
declare(strict_types=1);

// ---- wordpress.org ----------------------------------------------------------

$org = tufte_blocks_project_map_wporg( tufte_fixture_json( 'wporg-scroll-indicator.json' ) );
tufte_assert_same( '1.0.2', $org['version'], 'wporg: version' );
tufte_assert_same( '2026-09-14 7:08pm GMT', $org['release_date'], 'wporg: release_date is last_updated, unformatted' );
tufte_assert_same( '6.4', $org['requires_wp'], 'wporg: requires_wp' );
tufte_assert_same( '7.0.4', $org['tested_up_to'], 'wporg: tested_up_to' );
tufte_assert_same( '7.4', $org['requires_php'], 'wporg: requires_php' );
tufte_assert_same( 'https://downloads.wordpress.org/plugin/scroll-indicator.1.0.2.zip', $org['link_download'], 'wporg: link_download' );
tufte_assert_same( 'https://wordpress.org/plugins/scroll-indicator/', $org['link_directory'], 'wporg: link_directory synthesised from slug' );
tufte_assert_same( 'https://wordpress.org/support/plugin/scroll-indicator/', $org['link_support'], 'wporg: link_support' );
tufte_assert_same( 'https://translate.wordpress.org/projects/wp-plugins/scroll-indicator/', $org['link_translate'], 'wporg: link_translate synthesised from slug' );
tufte_assert_same( 'https://ps.w.org/scroll-indicator/assets/banner-1544x500.png?rev=3695697', $org['banner'], 'wporg: high banner kept for sideload' );
tufte_assert_same( false, array_key_exists( 'license', $org ), 'wporg: no license key (the API does not publish one)' );

$sparse = tufte_blocks_project_map_wporg( array( 'slug' => 'x' ) );
tufte_assert_same( '', $sparse['version'], 'wporg sparse: missing version is empty string' );
tufte_assert_same( 'https://wordpress.org/plugins/x/', $sparse['link_directory'], 'wporg sparse: directory link still synthesised' );
tufte_assert_same( '', $sparse['banner'], 'wporg sparse: no banner is empty string' );

$noslug = tufte_blocks_project_map_wporg( array() );
tufte_assert_same( '', $noslug['link_directory'], 'wporg no slug: no synthesised links' );
tufte_assert_same( '', $noslug['link_translate'], 'wporg no slug: no translate link' );

$err = tufte_blocks_project_fetch_wporg( '' );
tufte_assert_same( true, is_wp_error( $err ), 'wporg fetch: empty slug is a WP_Error without a request' );
```

- [ ] **Step 3: Run and watch it fail**

```bash
wpcli eval-file tests/run.php
```

Expected: fatal `Call to undefined function tufte_blocks_project_map_wporg()`.

- [ ] **Step 4: Write the adapter**

`inc/projects/sources/wporg.php`:

```php
<?php
/**
 * wordpress.org plugin directory adapter.
 *
 * Knows nothing about posts. fetch() does the network call, map() turns an
 * API response array into registry field values. Only map() is tested.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User agent for all outbound project requests.
 *
 * @return string
 */
function tufte_blocks_project_user_agent(): string {
	return 'tufte-blocks/' . TUFTE_BLOCKS_VERSION . ' (+https://derekhanson.blog; project sync)';
}

/**
 * Fetch plugin information from api.wordpress.org.
 *
 * @param string $slug Plugin slug, e.g. "scroll-indicator".
 * @return array|WP_Error Field values from map(), or an error.
 */
function tufte_blocks_project_fetch_wporg( string $slug ) {
	$slug = sanitize_title( $slug );
	if ( '' === $slug ) {
		return new WP_Error( 'tufte_wporg_no_slug', 'No wordpress.org slug.' );
	}

	$url = add_query_arg(
		array(
			'action'        => 'plugin_information',
			'request[slug]' => $slug,
		),
		'https://api.wordpress.org/plugins/info/1.2/'
	);

	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 10,
			'user-agent' => tufte_blocks_project_user_agent(),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'tufte_wporg_http', sprintf( 'wordpress.org returned HTTP %d for %s.', $code, $slug ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['slug'] ) ) {
		return new WP_Error( 'tufte_wporg_body', sprintf( 'wordpress.org returned no plugin data for %s.', $slug ) );
	}

	return tufte_blocks_project_map_wporg( $data );
}

/**
 * Map a plugin_information response to registry field values.
 *
 * Pure. Missing keys become empty strings. Also returns 'banner' (the high
 * resolution banner URL) for the one-time featured image sideload; it is not
 * a registry field.
 *
 * @param array $data Decoded API response.
 * @return array<string,string>
 */
function tufte_blocks_project_map_wporg( array $data ): array {
	$slug = isset( $data['slug'] ) ? sanitize_title( (string) $data['slug'] ) : '';
	$str  = static fn( string $key ): string => isset( $data[ $key ] ) && is_scalar( $data[ $key ] ) ? trim( (string) $data[ $key ] ) : '';

	return array(
		'version'        => $str( 'version' ),
		'release_date'   => $str( 'last_updated' ),
		'requires_wp'    => $str( 'requires' ),
		'tested_up_to'   => $str( 'tested' ),
		'requires_php'   => $str( 'requires_php' ),
		'link_download'  => $str( 'download_link' ),
		'link_directory' => $slug ? 'https://wordpress.org/plugins/' . $slug . '/' : '',
		'link_support'   => $str( 'support_url' ),
		'link_translate' => $slug ? 'https://translate.wordpress.org/projects/wp-plugins/' . $slug . '/' : '',
		'banner'         => isset( $data['banners']['high'] ) && is_string( $data['banners']['high'] ) ? $data['banners']['high'] : '',
	);
}
```

- [ ] **Step 5: Add to loader, run tests**

Loader array gains `'sources/wporg'` after `'header-parser'`.

```bash
phplint inc/projects/sources/wporg.php
wpcli eval-file tests/run.php
```

Expected: all PASS, `38 passed, 0 failed`.

- [ ] **Step 6: Commit**

```bash
git add inc/projects/sources/wporg.php tests functions.php
git commit -m "Add the wordpress.org source adapter with fixture tests

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 4.3: GitHub adapter (TDD)

**Files:**
- Create: `inc/projects/sources/github.php`, `tests/fixtures/github-repo-tufte-blocks.json`, `tests/fixtures/github-release-tufte-blocks.json`, `tests/fixtures/github-repo-scroll-indicator.json`
- Modify: `tests/test-adapters.php`, `functions.php` loader

- [ ] **Step 1: Write the fixtures**

`tests/fixtures/github-repo-tufte-blocks.json`:

```json
{
  "full_name": "dhanson-wp/tufte-blocks",
  "html_url": "https://github.com/dhanson-wp/tufte-blocks",
  "default_branch": "main",
  "license": { "key": "gpl-3.0", "name": "GNU General Public License v3.0", "spdx_id": "GPL-3.0" }
}
```

`tests/fixtures/github-release-tufte-blocks.json`:

```json
{
  "tag_name": "v1.3.0",
  "published_at": "2026-02-27T18:49:41Z",
  "zipball_url": "https://api.github.com/repos/dhanson-wp/tufte-blocks/zipball/v1.3.0",
  "assets": [
    { "name": "tufte-blocks-1.3.0.zip", "browser_download_url": "https://github.com/dhanson-wp/tufte-blocks/releases/download/v1.3.0/tufte-blocks-1.3.0.zip" }
  ]
}
```

`tests/fixtures/github-repo-scroll-indicator.json`:

```json
{
  "full_name": "dhanson-wp/scroll-indicator",
  "html_url": "https://github.com/dhanson-wp/scroll-indicator",
  "default_branch": "trunk",
  "license": { "key": "other", "name": "Other", "spdx_id": "NOASSERTION" }
}
```

- [ ] **Step 2: Append the failing GitHub tests to `tests/test-adapters.php`**

```php

// ---- GitHub -----------------------------------------------------------------

tufte_assert_same( array( 'dhanson-wp', 'tufte-blocks' ), tufte_blocks_project_parse_github_url( 'https://github.com/dhanson-wp/tufte-blocks' ), 'github url: owner/repo' );
tufte_assert_same( array( 'dhanson-wp', 'tufte-blocks' ), tufte_blocks_project_parse_github_url( 'https://github.com/dhanson-wp/tufte-blocks.git/' ), 'github url: strips .git and trailing slash' );
tufte_assert_same( null, tufte_blocks_project_parse_github_url( 'https://gitlab.com/x/y' ), 'github url: other host is null' );
tufte_assert_same( null, tufte_blocks_project_parse_github_url( '' ), 'github url: empty is null' );

$theme = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	tufte_blocks_parse_file_header( tufte_fixture( 'header-style-css.txt' ) ),
	tufte_fixture_json( 'github-release-tufte-blocks.json' )
);
tufte_assert_same( '1.4.4', $theme['version'], 'github theme: version from style.css header, not the v1.3.0 release' );
tufte_assert_same( '2026-02-27T18:49:41Z', $theme['release_date'], 'github theme: release_date from release published_at' );
tufte_assert_same( '6.4', $theme['requires_wp'], 'github theme: requires_wp from header' );
tufte_assert_same( '6.9', $theme['tested_up_to'], 'github theme: tested_up_to from header' );
tufte_assert_same( '7.4', $theme['requires_php'], 'github theme: requires_php from header' );
tufte_assert_same( 'GNU General Public License v2 or later', $theme['license'], 'github theme: header License beats GitHub GPL-3.0' );
tufte_assert_same( 'https://github.com/dhanson-wp/tufte-blocks/releases/download/v1.3.0/tufte-blocks-1.3.0.zip', $theme['link_download'], 'github theme: first release asset' );
tufte_assert_same( 'https://github.com/dhanson-wp/tufte-blocks/issues', $theme['link_support'], 'github theme: issues url' );
tufte_assert_same( false, array_key_exists( 'link_directory', $theme ), 'github: no link_directory' );
tufte_assert_same( false, array_key_exists( 'link_translate', $theme ), 'github: no link_translate' );

$plugin = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-scroll-indicator.json' ),
	tufte_blocks_parse_file_header( tufte_fixture( 'header-plugin-php.txt' ) ),
	array()
);
tufte_assert_same( '1.0.1', $plugin['version'], 'github plugin: version from plugin header' );
tufte_assert_same( '', $plugin['release_date'], 'github plugin: no release means empty date' );
tufte_assert_same( '', $plugin['link_download'], 'github plugin: no release means empty download' );
tufte_assert_same( 'GPLv2 or later', $plugin['license'], 'github plugin: header license' );
tufte_assert_same( '', $plugin['tested_up_to'], 'github plugin: missing header field stays empty' );

$nolicense = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	tufte_blocks_parse_file_header( "/*\nTheme Name: X\nVersion: 9\n*/" ),
	array()
);
tufte_assert_same( 'GPL-3.0', $nolicense['license'], 'github: spdx_id used when header has no License' );

$noassert = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-scroll-indicator.json' ),
	tufte_blocks_parse_file_header( "/*\nPlugin Name: X\n*/" ),
	array()
);
tufte_assert_same( '', $noassert['license'], 'github: NOASSERTION is ignored' );

$noassets = tufte_blocks_project_map_github(
	tufte_fixture_json( 'github-repo-tufte-blocks.json' ),
	array(),
	array( 'tag_name' => 'v2', 'published_at' => '2026-01-01T00:00:00Z', 'assets' => array() )
);
tufte_assert_same( '', $noassets['link_download'], 'github: release with no assets gives empty download' );
tufte_assert_same( '2026-01-01T00:00:00Z', $noassets['release_date'], 'github: release date still taken' );

$err = tufte_blocks_project_fetch_github( 'not a url' );
tufte_assert_same( true, is_wp_error( $err ), 'github fetch: unparseable url is a WP_Error without a request' );
```

- [ ] **Step 3: Run and watch it fail**

```bash
wpcli eval-file tests/run.php
```

Expected: fatal `Call to undefined function tufte_blocks_project_parse_github_url()`.

- [ ] **Step 4: Write the adapter**

`inc/projects/sources/github.php`:

```php
<?php
/**
 * GitHub source adapter.
 *
 * Reads the repository (default branch, license), the WordPress file header
 * on the default branch (version and requirements), and the latest release
 * (date and download asset). Version always comes from the header, never
 * the release tag: releases lag behind the code on Derek's repos.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract owner and repo from a GitHub URL.
 *
 * @param string $url Repository URL.
 * @return array{0:string,1:string}|null
 */
function tufte_blocks_project_parse_github_url( string $url ): ?array {
	$parts = wp_parse_url( trim( $url ) );
	if ( empty( $parts['host'] ) || ! in_array( strtolower( $parts['host'] ), array( 'github.com', 'www.github.com' ), true ) ) {
		return null;
	}
	$segments = array_values( array_filter( explode( '/', $parts['path'] ?? '' ) ) );
	if ( count( $segments ) < 2 ) {
		return null;
	}
	$repo = preg_replace( '/\.git$/', '', $segments[1] );
	return array( $segments[0], $repo );
}

/**
 * GET a URL with the shared timeout and user agent. Returns the body or WP_Error.
 *
 * @param string $url     URL.
 * @param array  $headers Extra headers.
 * @return string|WP_Error
 */
function tufte_blocks_project_http_get( string $url, array $headers = array() ) {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 10,
			'user-agent' => tufte_blocks_project_user_agent(),
			'headers'    => $headers,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'tufte_http_' . $code, sprintf( 'HTTP %d from %s', $code, $url ) );
	}
	return wp_remote_retrieve_body( $response );
}

/**
 * Fetch everything the GitHub adapter needs and map it.
 *
 * Up to four requests: repo, raw style.css, raw {repo}.php (only when
 * style.css is not a theme header), releases/latest. A missing release is
 * not an error; a missing repo or header is.
 *
 * @param string $url Repository URL.
 * @return array|WP_Error
 */
function tufte_blocks_project_fetch_github( string $url ) {
	$parsed = tufte_blocks_project_parse_github_url( $url );
	if ( null === $parsed ) {
		return new WP_Error( 'tufte_github_url', sprintf( 'Not a GitHub repository URL: %s', $url ) );
	}
	list( $owner, $repo ) = $parsed;
	$api                  = 'https://api.github.com/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo );
	$accept               = array( 'Accept' => 'application/vnd.github+json' );

	$repo_body = tufte_blocks_project_http_get( $api, $accept );
	if ( is_wp_error( $repo_body ) ) {
		return $repo_body;
	}
	$repo_data = json_decode( $repo_body, true );
	if ( ! is_array( $repo_data ) || empty( $repo_data['default_branch'] ) ) {
		return new WP_Error( 'tufte_github_repo', sprintf( 'No repository data for %s/%s', $owner, $repo ) );
	}

	$raw_base = 'https://raw.githubusercontent.com/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/' . rawurlencode( (string) $repo_data['default_branch'] ) . '/';
	$header   = array();
	$style    = tufte_blocks_project_http_get( $raw_base . 'style.css' );
	if ( ! is_wp_error( $style ) ) {
		$header = tufte_blocks_parse_file_header( $style );
	}
	if ( empty( $header['version'] ) ) {
		$main = tufte_blocks_project_http_get( $raw_base . rawurlencode( $repo ) . '.php' );
		if ( ! is_wp_error( $main ) ) {
			$header = tufte_blocks_parse_file_header( $main );
		}
	}
	if ( empty( $header['version'] ) ) {
		return new WP_Error( 'tufte_github_header', sprintf( 'No WordPress file header with a Version found in %s/%s', $owner, $repo ) );
	}

	$release      = array();
	$release_body = tufte_blocks_project_http_get( $api . '/releases/latest', $accept );
	if ( ! is_wp_error( $release_body ) ) {
		$decoded = json_decode( $release_body, true );
		$release = is_array( $decoded ) ? $decoded : array();
	}

	return tufte_blocks_project_map_github( $repo_data, $header, $release );
}

/**
 * Map GitHub data to registry field values. Pure.
 *
 * @param array $repo    Decoded /repos/{owner}/{repo} response.
 * @param array $header  Output of tufte_blocks_parse_file_header().
 * @param array $release Decoded /releases/latest response, or empty array.
 * @return array<string,string>
 */
function tufte_blocks_project_map_github( array $repo, array $header, array $release ): array {
	$h = static fn( string $key ): string => isset( $header[ $key ] ) ? trim( (string) $header[ $key ] ) : '';

	$license = $h( 'license' );
	if ( '' === $license ) {
		$spdx    = isset( $repo['license']['spdx_id'] ) ? (string) $repo['license']['spdx_id'] : '';
		$license = ( '' !== $spdx && 'NOASSERTION' !== $spdx ) ? $spdx : '';
	}

	$download = '';
	if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
		foreach ( $release['assets'] as $asset ) {
			if ( ! empty( $asset['browser_download_url'] ) ) {
				$download = (string) $asset['browser_download_url'];
				break;
			}
		}
	}

	$html_url = isset( $repo['html_url'] ) ? rtrim( (string) $repo['html_url'], '/' ) : '';

	return array(
		'version'       => $h( 'version' ),
		'release_date'  => isset( $release['published_at'] ) ? (string) $release['published_at'] : '',
		'requires_wp'   => $h( 'requires_wp' ),
		'tested_up_to'  => $h( 'tested_up_to' ),
		'requires_php'  => $h( 'requires_php' ),
		'license'       => $license,
		'link_download' => $download,
		'link_support'  => $html_url ? $html_url . '/issues' : '',
	);
}
```

- [ ] **Step 5: Add to loader, run tests**

Loader array gains `'sources/github'` after `'sources/wporg'`.

```bash
phplint inc/projects/sources/github.php
wpcli eval-file tests/run.php
```

Expected: all PASS, `62 passed, 0 failed`.

- [ ] **Step 6: One real fetch of each adapter, from the command line, to see the mapping against live data**

```bash
wpcli eval 'print_r( tufte_blocks_project_fetch_wporg( "scroll-indicator" ) ); print_r( tufte_blocks_project_fetch_github( "https://github.com/dhanson-wp/tufte-blocks" ) ); print_r( tufte_blocks_project_fetch_github( "https://github.com/dhanson-wp/scroll-indicator" ) );'
```

Expected: `.org` version `1.0.2`; Tufte Blocks version `1.4.4`, license `GNU General Public License v2 or later`, download the v1.3.0 zip; Scroll Indicator version `1.0.1`, `link_download` the v1.0.1 zip, license `GPLv2 or later`. No `WP_Error`.

- [ ] **Step 7: Commit**

```bash
git add inc/projects/sources/github.php tests functions.php
git commit -m "Add the GitHub source adapter with fixture tests

Version comes from the file header on the default branch, never the
release tag. Header License beats the GitHub-detected license; NOASSERTION
is ignored.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 5: Sync job, REST route, WP-CLI command, banner sideload

### Task 5.1: The cache writer and sync functions

**Files:**
- Create: `inc/projects/sync.php`
- Modify: `functions.php` loader

Cache shape written to `_project_source_cache`:

```php
array(
	'fetched_at' => '2026-09-14T20:15:00+00:00', // gmdate( 'c' ) of the last attempt that changed anything
	'wporg'      => array( 'version' => '1.0.2', ... ) | null,   // null = project has no .org slug
	'github'     => array( 'version' => '1.4.4', ... ) | null,   // null = project has no GitHub URL
	'errors'     => array( 'wporg' => 'message' ),               // last run's failures, for the sidebar
)
```

A failed fetch keeps the previous source array and records the error. Removing an identifier sets that source to `null` so stale data drops.

- [ ] **Step 1: Write sync.php**

```php
<?php
/**
 * Project sync job.
 *
 * Twice-daily WP-Cron event that runs the source adapters for every
 * published project and writes _project_source_cache. Also a REST route for
 * the sidebar's "Refresh now" button and a WP-CLI command for debugging.
 * The render path never calls anything in this file.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TUFTE_BLOCKS_PROJECT_SYNC_HOOK = 'tufte_blocks_projects_sync';

/**
 * Read the cache for a project. Always returns the full shape.
 *
 * @param int $post_id Project ID.
 * @return array{fetched_at:string,wporg:?array,github:?array,errors:array}
 */
function tufte_blocks_project_get_cache( int $post_id ): array {
	$cache = get_post_meta( $post_id, '_project_source_cache', true );
	$cache = is_array( $cache ) ? $cache : array();
	return array(
		'fetched_at' => isset( $cache['fetched_at'] ) ? (string) $cache['fetched_at'] : '',
		'wporg'      => isset( $cache['wporg'] ) && is_array( $cache['wporg'] ) ? $cache['wporg'] : null,
		'github'     => isset( $cache['github'] ) && is_array( $cache['github'] ) ? $cache['github'] : null,
		'errors'     => isset( $cache['errors'] ) && is_array( $cache['errors'] ) ? $cache['errors'] : array(),
	);
}

/**
 * Merge one adapter result into a cache array without ever blanking good data.
 *
 * Pure, so it is testable: given the previous cache, a source name, and an
 * adapter result (array, WP_Error, or null for "no identifier"), return the
 * new cache.
 *
 * @param array               $cache  Previous cache (full shape).
 * @param string              $source 'wporg' or 'github'.
 * @param array|WP_Error|null $result Adapter result.
 * @return array
 */
function tufte_blocks_project_merge_source( array $cache, string $source, $result ): array {
	unset( $cache['errors'][ $source ] );

	if ( null === $result ) {
		$cache[ $source ] = null;
		return $cache;
	}

	if ( is_wp_error( $result ) ) {
		$cache['errors'][ $source ] = $result->get_error_message();
		return $cache; // previous $cache[ $source ] is left intact.
	}

	$cache[ $source ] = $result;
	return $cache;
}

/**
 * Sync one project: run whichever adapters it has identifiers for, write the
 * cache, sideload the banner once.
 *
 * @param int $post_id Project ID.
 * @return array The new cache.
 */
function tufte_blocks_project_sync( int $post_id ): array {
	$cache = tufte_blocks_project_get_cache( $post_id );

	$slug   = (string) get_post_meta( $post_id, tufte_blocks_project_meta_key( 'wporg_slug' ), true );
	$github = (string) get_post_meta( $post_id, tufte_blocks_project_meta_key( 'github_url' ), true );

	$wporg_result = '' === $slug ? null : tufte_blocks_project_fetch_wporg( $slug );
	$cache        = tufte_blocks_project_merge_source( $cache, 'wporg', $wporg_result );

	$github_result = '' === $github ? null : tufte_blocks_project_fetch_github( $github );
	$cache         = tufte_blocks_project_merge_source( $cache, 'github', $github_result );

	foreach ( $cache['errors'] as $source => $message ) {
		error_log( sprintf( '[tufte-blocks] project %d %s sync failed: %s', $post_id, $source, $message ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$cache['fetched_at'] = gmdate( 'c' );
	update_post_meta( $post_id, '_project_source_cache', $cache );

	if ( is_array( $wporg_result ) && ! empty( $wporg_result['banner'] ) ) {
		tufte_blocks_project_sideload_banner( $post_id, (string) $wporg_result['banner'] );
	}

	return $cache;
}

/**
 * Sync every published project. Cron callback.
 *
 * @return void
 */
function tufte_blocks_project_sync_all(): void {
	$ids = get_posts(
		array(
			'post_type'      => 'project',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $ids as $id ) {
		tufte_blocks_project_sync( (int) $id );
	}
}
add_action( TUFTE_BLOCKS_PROJECT_SYNC_HOOK, 'tufte_blocks_project_sync_all' );

/**
 * Make sure the twice-daily event is scheduled. Themes have no activation
 * hook, so check on init; wp_next_scheduled() is a cheap option read.
 *
 * @return void
 */
function tufte_blocks_project_schedule_sync(): void {
	if ( ! wp_next_scheduled( TUFTE_BLOCKS_PROJECT_SYNC_HOOK ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'twicedaily', TUFTE_BLOCKS_PROJECT_SYNC_HOOK );
	}
}
add_action( 'init', 'tufte_blocks_project_schedule_sync' );

/**
 * Unschedule when the theme is switched away.
 *
 * @return void
 */
function tufte_blocks_project_unschedule_sync(): void {
	wp_clear_scheduled_hook( TUFTE_BLOCKS_PROJECT_SYNC_HOOK );
}
add_action( 'switch_theme', 'tufte_blocks_project_unschedule_sync' );

/**
 * Sideload the .org banner as the featured image, once. If a featured image
 * already exists it is never touched, so a custom image is never reverted.
 *
 * @param int    $post_id Project ID.
 * @param string $url     Banner URL.
 * @return void
 */
function tufte_blocks_project_sideload_banner( int $post_id, string $url ): void {
	if ( has_post_thumbnail( $post_id ) || '' === $url ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $url, $post_id, get_the_title( $post_id ) . ' banner', 'id' );
	if ( is_wp_error( $attachment_id ) ) {
		error_log( sprintf( '[tufte-blocks] project %d banner sideload failed: %s', $post_id, $attachment_id->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return;
	}

	set_post_thumbnail( $post_id, (int) $attachment_id );
}

/**
 * Sidebar-facing summary of a project's synced state: resolved values per
 * derived field, provenance, timestamp, errors.
 *
 * @param int $post_id Project ID.
 * @return array
 */
function tufte_blocks_project_synced_summary( int $post_id ): array {
	$cache    = tufte_blocks_project_get_cache( $post_id );
	$resolved = array();
	foreach ( tufte_blocks_project_derived_keys() as $key ) {
		$resolved[ $key ] = tufte_blocks_project_resolve_field( $key, '', $cache, false );
	}
	return array(
		'fetched_at' => $cache['fetched_at'],
		'has_wporg'  => null !== $cache['wporg'],
		'has_github' => null !== $cache['github'],
		'errors'     => $cache['errors'],
		'resolved'   => $resolved,
	);
}

/**
 * REST: POST /tufte-blocks/v1/projects/{id}/sync, and a read-only field on
 * the project resource so the sidebar can show synced values on load.
 *
 * @return void
 */
function tufte_blocks_project_register_rest(): void {
	register_rest_route(
		'tufte-blocks/v1',
		'/projects/(?P<id>\d+)/sync',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'permission_callback' => static fn( WP_REST_Request $request ): bool => current_user_can( 'edit_post', (int) $request['id'] ),
			'callback'            => static function ( WP_REST_Request $request ) {
				$post_id = (int) $request['id'];
				if ( 'project' !== get_post_type( $post_id ) ) {
					return new WP_Error( 'tufte_not_project', 'Not a project.', array( 'status' => 404 ) );
				}
				tufte_blocks_project_sync( $post_id );
				return rest_ensure_response( tufte_blocks_project_synced_summary( $post_id ) );
			},
			'args'                => array(
				'id' => array( 'validate_callback' => static fn( $value ): bool => is_numeric( $value ) ),
			),
		)
	);

	register_rest_field(
		'project',
		'project_synced',
		array(
			'get_callback' => static fn( array $post ): array => tufte_blocks_project_synced_summary( (int) $post['id'] ),
			'schema'       => array( 'type' => 'object', 'context' => array( 'edit' ), 'readonly' => true ),
		)
	);
}
add_action( 'rest_api_init', 'tufte_blocks_project_register_rest' );

/**
 * WP-CLI: wp tufte-blocks project-sync [<id>]
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'tufte-blocks project-sync',
		static function ( array $args ): void {
			$ids = $args ? array( (int) $args[0] ) : get_posts(
				array(
					'post_type'      => 'project',
					'post_status'    => 'publish',
					'posts_per_page' => 50,
					'fields'         => 'ids',
				)
			);
			foreach ( $ids as $id ) {
				$cache = tufte_blocks_project_sync( (int) $id );
				WP_CLI::log( sprintf( '%d %s', $id, get_the_title( (int) $id ) ) );
				WP_CLI::log( '  fetched_at: ' . $cache['fetched_at'] );
				WP_CLI::log( '  wporg:      ' . ( null === $cache['wporg'] ? '(none)' : wp_json_encode( $cache['wporg'] ) ) );
				WP_CLI::log( '  github:     ' . ( null === $cache['github'] ? '(none)' : wp_json_encode( $cache['github'] ) ) );
				foreach ( $cache['errors'] as $source => $message ) {
					WP_CLI::warning( "  {$source}: {$message}" );
				}
				WP_CLI::log( '  thumbnail:  ' . ( has_post_thumbnail( (int) $id ) ? (string) get_post_thumbnail_id( (int) $id ) : 'none' ) );
			}
			WP_CLI::success( 'Synced ' . count( $ids ) . ' project(s).' );
		}
	);
}
```

`tufte_blocks_project_resolve_field()` is written in Task 6.1; until then `tufte_blocks_project_synced_summary()` will fatal if called. That is fine because nothing calls it before Milestone 6 except the REST field, which is only evaluated in `edit` context. Do not load the editor until 6.1 is done.

- [ ] **Step 2: Add to loader**

Loader array gains `'sync'` after `'sources/github'`.

```bash
phplint inc/projects/sync.php
wpcli eval 'echo wp_next_scheduled( "tufte_blocks_projects_sync" ) ? "scheduled\n" : "not scheduled\n";'
```

Expected: `scheduled` (the init hook ran during the WP-CLI bootstrap).

- [ ] **Step 3: Add merge tests** to a new file `tests/test-sync.php`

```php
<?php
declare(strict_types=1);

$base = array( 'fetched_at' => '', 'wporg' => array( 'version' => '1.0.2' ), 'github' => array( 'version' => '1.0.1' ), 'errors' => array() );

$after_error = tufte_blocks_project_merge_source( $base, 'wporg', new WP_Error( 'x', 'boom' ) );
tufte_assert_same( array( 'version' => '1.0.2' ), $after_error['wporg'], 'merge: a failed fetch keeps the previous values' );
tufte_assert_same( 'boom', $after_error['errors']['wporg'], 'merge: the error is recorded' );

$after_ok = tufte_blocks_project_merge_source( $after_error, 'wporg', array( 'version' => '1.0.3' ) );
tufte_assert_same( array( 'version' => '1.0.3' ), $after_ok['wporg'], 'merge: a good fetch replaces values' );
tufte_assert_same( false, isset( $after_ok['errors']['wporg'] ), 'merge: a good fetch clears the error' );

$removed = tufte_blocks_project_merge_source( $base, 'github', null );
tufte_assert_same( null, $removed['github'], 'merge: removing the identifier drops that source' );
tufte_assert_same( array( 'version' => '1.0.2' ), $removed['wporg'], 'merge: the other source is untouched' );
```

```bash
wpcli eval-file tests/run.php
```

Expected: `68 passed, 0 failed`.

- [ ] **Step 4: Commit**

```bash
git add inc/projects/sync.php tests/test-sync.php functions.php
git commit -m "Add the project sync job: cron, REST route, WP-CLI, banner sideload

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 5.2: Run a real sync (after Task 6.1 lands)

Do this task after Milestone 6 Task 6.1, because the CLI output path calls the resolver. It is placed here so the milestone reads in order.

- [ ] **Step 1: Sync both projects**

```bash
wpcli tufte-blocks project-sync
```

Expected: Scroll Indicator shows `wporg` with version `1.0.2`, `github` with version `1.0.1`, and `thumbnail:` a new attachment ID. Tufte Blocks shows `wporg: (none)`, `github` version `1.4.4`, `thumbnail: none`. No warnings.

- [ ] **Step 2: Confirm the banner landed and the cache is hidden from REST**

```bash
PORT=$(ps aux | grep "Studio/derekhansonblog" | grep -oE "127.0.0.1:[0-9]+" | sort -u | head -1)
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project/5285?_fields=featured_media,meta" | python3 -c "import json,sys;d=json.load(sys.stdin);print(d['featured_media'], '_project_source_cache' in d['meta'])"
```

Expected: a non-zero ID and `False`.

- [ ] **Step 3: Offline run leaves cached values intact**

```bash
wpcli eval 'add_filter( "pre_http_request", fn() => new WP_Error( "offline", "simulated outage" ) ); $c = tufte_blocks_project_sync( 5285 ); echo $c["wporg"]["version"], " ", $c["github"]["version"], " ", wp_json_encode( $c["errors"] ), "\n";'
```

Expected: `1.0.2 1.0.1 {"wporg":"simulated outage","github":"simulated outage"}`. Then run `wpcli tufte-blocks project-sync 5285` once more to clear the recorded errors.

---

## Milestone 6: Binding source, field resolver, empty-value filter, with tests

### Task 6.1: Resolver and binding source (TDD)

**Files:**
- Create: `inc/projects/bindings.php`, `tests/test-resolver.php`
- Modify: `functions.php` loader

- [ ] **Step 1: Write the failing tests**

`tests/test-resolver.php`:

```php
<?php
declare(strict_types=1);

$cache = array(
	'fetched_at' => '2026-09-14T00:00:00+00:00',
	'wporg'      => array( 'version' => '1.0.2', 'requires_wp' => '6.4', 'release_date' => '2026-09-14 7:08pm GMT' ),
	'github'     => array( 'version' => '1.0.1', 'license' => 'GPLv2 or later', 'link_support' => 'https://github.com/x/y/issues' ),
	'errors'     => array(),
);

tufte_assert_same( '9.9', tufte_blocks_project_resolve_field( 'version', '9.9', $cache ), 'resolve: override beats .org' );
tufte_assert_same( '1.0.2', tufte_blocks_project_resolve_field( 'version', '', $cache ), 'resolve: .org beats GitHub' );
tufte_assert_same( 'GPLv2 or later', tufte_blocks_project_resolve_field( 'license', '', $cache ), 'resolve: GitHub fills what .org lacks' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'tested_up_to', '', $cache ), 'resolve: nothing anywhere is empty string' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'not_a_field', 'x', $cache ), 'resolve: unregistered key is empty even with a value' );
tufte_assert_same( '', tufte_blocks_project_resolve_field( 'version', '   ', array( 'wporg' => null, 'github' => null ) ), 'resolve: whitespace override does not count' );

tufte_assert_same( '6.4 or higher', tufte_blocks_project_resolve_field( 'requires_wp', '', $cache ), 'resolve: formatter applied by default' );
tufte_assert_same( '6.4', tufte_blocks_project_resolve_field( 'requires_wp', '', $cache, false ), 'resolve: formatter can be skipped' );
tufte_assert_same( 'Sep 2026', tufte_blocks_project_resolve_field( 'release_date', '', $cache ), 'resolve: date formatted Mon YYYY' );
tufte_assert_same( 'Sep 2026', tufte_blocks_project_resolve_field( 'release_date', 'Sep 2026', $cache ), 'resolve: prose override left alone by date formatter' );

// Manual fields resolve from the override slot only (they have no source data).
tufte_assert_same( 'A quiet cue.', tufte_blocks_project_resolve_field( 'tagline', 'A quiet cue.', $cache ), 'resolve: manual field returns its value' );

// Post-backed lookup, using the object cache so no database write is needed.
$fake_id = 987654321;
wp_cache_set(
	$fake_id,
	array(
		'project_version'       => array( '' ),
		'project_tagline'       => array( 'Hello' ),
		'_project_source_cache' => array( serialize( $cache ) ),
	),
	'post_meta'
);
tufte_assert_same( '1.0.2', tufte_blocks_project_get_field( $fake_id, 'version' ), 'get_field: reads cache through post meta' );
tufte_assert_same( 'Hello', tufte_blocks_project_get_field( $fake_id, 'tagline' ), 'get_field: reads manual meta' );
tufte_assert_same( '', tufte_blocks_project_get_field( $fake_id, 'demo_url' ), 'get_field: unset manual meta is empty' );

// Binding source callback signature.
// Core copies the source's uses_context into $block->context inside
// WP_Block::process_block_bindings(), so a direct call sets it by hand.
$block          = new WP_Block( array( 'blockName' => 'core/paragraph', 'attrs' => array() ), array() );
$block->context = array( 'postId' => $fake_id, 'postType' => 'project' );
tufte_assert_same( '1.0.2', tufte_blocks_project_binding_value( array( 'key' => 'version' ), $block, 'content' ), 'binding: resolves via block context postId' );
tufte_assert_same( '', tufte_blocks_project_binding_value( array(), $block, 'content' ), 'binding: missing key arg is empty' );
tufte_assert_same( true, null !== get_block_bindings_source( 'tufte-blocks/project-field' ), 'binding: source is registered' );

// The real path: core renders a bound paragraph and calls our source with context.
$rendered = ( new WP_Block(
	array(
		'blockName'    => 'core/paragraph',
		'attrs'        => array( 'metadata' => array( 'bindings' => array( 'content' => array( 'source' => 'tufte-blocks/project-field', 'args' => array( 'key' => 'version' ) ) ) ) ),
		'innerHTML'    => '<p>placeholder</p>',
		'innerContent' => array( '<p>placeholder</p>' ),
	),
	array( 'postId' => $fake_id, 'postType' => 'project' )
) )->render();
tufte_assert_same( true, str_contains( $rendered, '>1.0.2<' ), 'binding: core render replaces paragraph content via the source' );

wp_cache_delete( $fake_id, 'post_meta' );
```

- [ ] **Step 2: Run and watch it fail**

```bash
wpcli eval-file tests/run.php
```

Expected: fatal `Call to undefined function tufte_blocks_project_resolve_field()`.

- [ ] **Step 3: Write bindings.php**

```php
<?php
/**
 * Project field resolver and block bindings source.
 *
 * The only branching logic in the feature lives here: manual override, then
 * wordpress.org, then GitHub, then empty. Templates bind to
 * tufte-blocks/project-field with {"key":"version"} and never learn where
 * the value came from. Nothing here touches the network.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve one field from an override value and a cache array. Pure.
 *
 * @param string $key      Registry key.
 * @param string $override Value of the project_{key} meta (manual value or override).
 * @param array  $cache    Cache in the shape of tufte_blocks_project_get_cache().
 * @param bool   $format   Apply the registry formatter (default true).
 * @return string Empty string when nothing resolves.
 */
function tufte_blocks_project_resolve_field( string $key, string $override, array $cache, bool $format = true ): string {
	$fields = tufte_blocks_project_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}

	$value = trim( $override );

	if ( '' === $value && ! $fields[ $key ]['manual'] ) {
		foreach ( array( 'wporg', 'github' ) as $source ) {
			if ( isset( $cache[ $source ][ $key ] ) && is_scalar( $cache[ $source ][ $key ] ) ) {
				$candidate = trim( (string) $cache[ $source ][ $key ] );
				if ( '' !== $candidate ) {
					$value = $candidate;
					break;
				}
			}
		}
	}

	if ( '' === $value ) {
		return '';
	}

	if ( $format && ! empty( $fields[ $key ]['format'] ) && is_callable( $fields[ $key ]['format'] ) ) {
		$value = (string) call_user_func( $fields[ $key ]['format'], $value );
	}

	return $value;
}

/**
 * Resolve a field for a post.
 *
 * @param int    $post_id Project ID.
 * @param string $key     Registry key.
 * @return string
 */
function tufte_blocks_project_get_field( int $post_id, string $key ): string {
	if ( $post_id <= 0 ) {
		return '';
	}
	$override = get_post_meta( $post_id, tufte_blocks_project_meta_key( $key ), true );
	return tufte_blocks_project_resolve_field(
		$key,
		is_scalar( $override ) ? (string) $override : '',
		tufte_blocks_project_get_cache( $post_id )
	);
}

/**
 * Block bindings callback.
 *
 * @param array    $source_args    Binding args; expects 'key'.
 * @param WP_Block $block_instance The block being rendered.
 * @param string   $attribute_name Bound attribute (unused; the value is the same for any attribute).
 * @return string
 */
function tufte_blocks_project_binding_value( array $source_args, WP_Block $block_instance, string $attribute_name ): string {
	$key     = isset( $source_args['key'] ) ? (string) $source_args['key'] : '';
	$post_id = isset( $block_instance->context['postId'] ) ? (int) $block_instance->context['postId'] : (int) get_the_ID();
	if ( '' === $key ) {
		return '';
	}
	return tufte_blocks_project_get_field( $post_id, $key );
}

/**
 * Register the binding source.
 *
 * @return void
 */
function tufte_blocks_project_register_binding_source(): void {
	register_block_bindings_source(
		'tufte-blocks/project-field',
		array(
			'label'              => __( 'Project field', 'tufte-blocks' ),
			'get_value_callback' => 'tufte_blocks_project_binding_value',
			'uses_context'       => array( 'postId', 'postType' ),
		)
	);
}
add_action( 'init', 'tufte_blocks_project_register_binding_source' );
```

- [ ] **Step 4: Add to loader, run tests**

Loader array gains `'bindings'` after `'sync'`.

```bash
phplint inc/projects/bindings.php
wpcli eval-file tests/run.php
```

Expected: `86 passed, 0 failed`.

- [ ] **Step 5: Commit, then go back and do Task 5.2**

```bash
git add inc/projects/bindings.php tests/test-resolver.php functions.php
git commit -m "Add the project field resolver and tufte-blocks/project-field binding source

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

Now perform Task 5.2 (real sync, banner, offline run).

### Task 6.2: Empty-value render filter (TDD)

**Files:**
- Modify: `inc/projects/bindings.php` (append), `tests/test-resolver.php` (append)

Behavior:
1. Any block whose `metadata.bindings` uses `tufte-blocks/project-field` is dropped when any of those bindings resolves to `''`.
2. A `core/group` with className `tufte-project-detail` is dropped when its rendered HTML contains no `tufte-project-value` element (its value paragraph was dropped by rule 1).
3. Everything else passes through untouched.

- [ ] **Step 1: Append failing tests to `tests/test-resolver.php`** (before the final `wp_cache_delete` line; move that line to the very end)

```php

// ---- empty-value filter -----------------------------------------------------

$bound = static fn( string $key, array $extra = array() ): array => array(
	'blockName' => 'core/paragraph',
	'attrs'     => array_merge( array( 'metadata' => array( 'bindings' => array( 'content' => array( 'source' => 'tufte-blocks/project-field', 'args' => array( 'key' => $key ) ) ) ) ) ), $extra ),
	'innerHTML' => '<p>x</p>',
);
$ctx = array( 'postId' => $fake_id, 'postType' => 'project' );
// Build a WP_Block with context already populated, as core does before render_block fires.
$inst = static function ( array $parsed ) use ( $ctx ): WP_Block {
	$b          = new WP_Block( $parsed, array() );
	$b->context = $ctx;
	return $b;
};

tufte_assert_same( '<p>1.0.2</p>', tufte_blocks_project_filter_empty_bindings( '<p>1.0.2</p>', $bound( 'version' ), $inst( $bound( 'version' ) ) ), 'filter: bound block with a value passes through' );
tufte_assert_same( '', tufte_blocks_project_filter_empty_bindings( '<p></p>', $bound( 'tested_up_to' ), $inst( $bound( 'tested_up_to' ) ) ), 'filter: bound block with an empty value is removed' );

$plain = array( 'blockName' => 'core/paragraph', 'attrs' => array(), 'innerHTML' => '<p>hi</p>' );
tufte_assert_same( '<p>hi</p>', tufte_blocks_project_filter_empty_bindings( '<p>hi</p>', $plain, $inst( $plain ) ), 'filter: unbound block passes through' );

$other = array( 'blockName' => 'core/paragraph', 'attrs' => array( 'metadata' => array( 'bindings' => array( 'content' => array( 'source' => 'core/post-meta', 'args' => array( 'key' => 'nope' ) ) ) ) ), 'innerHTML' => '<p></p>' );
tufte_assert_same( '<p></p>', tufte_blocks_project_filter_empty_bindings( '<p></p>', $other, $inst( $other ) ), 'filter: other binding sources are not our business' );

$row = array( 'blockName' => 'core/group', 'attrs' => array( 'className' => 'tufte-project-detail' ), 'innerHTML' => '' );
tufte_assert_same( '', tufte_blocks_project_filter_empty_bindings( '<div class="wp-block-group tufte-project-detail"><p class="tufte-project-label">Version</p></div>', $row, $inst( $row ) ), 'filter: detail row without a value is removed' );
$row_html = '<div class="wp-block-group tufte-project-detail"><p class="tufte-project-label">Version</p><p class="tufte-project-value">1.0.2</p></div>';
tufte_assert_same( $row_html, tufte_blocks_project_filter_empty_bindings( $row_html, $row, $inst( $row ) ), 'filter: detail row with a value passes through' );

wp_cache_delete( $fake_id, 'post_meta' );
```

- [ ] **Step 2: Run and watch it fail**

```bash
wpcli eval-file tests/run.php
```

Expected: fatal `Call to undefined function tufte_blocks_project_filter_empty_bindings()`.

- [ ] **Step 3: Append the filter to `inc/projects/bindings.php`**

```php

/**
 * Drop blocks whose project-field binding resolved to nothing.
 *
 * Block Bindings renders an empty element when a value is empty; the design
 * says a button with no URL must not render at all, never fall back to "#".
 * Also drops a details row (core/group.tufte-project-detail) whose value
 * paragraph was dropped, so no orphan label is left behind.
 *
 * @param string   $block_content Rendered HTML.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance (for context).
 * @return string
 */
function tufte_blocks_project_filter_empty_bindings( string $block_content, array $block, WP_Block $instance ): string {
	$class_name = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( 'core/group' === ( $block['blockName'] ?? '' ) && str_contains( $class_name, 'tufte-project-detail' ) ) {
		return str_contains( $block_content, 'tufte-project-value' ) ? $block_content : '';
	}

	$bindings = $block['attrs']['metadata']['bindings'] ?? null;
	if ( ! is_array( $bindings ) ) {
		return $block_content;
	}

	foreach ( $bindings as $attribute => $binding ) {
		if ( ( $binding['source'] ?? '' ) !== 'tufte-blocks/project-field' ) {
			continue;
		}
		$value = tufte_blocks_project_binding_value( (array) ( $binding['args'] ?? array() ), $instance, (string) $attribute );
		if ( '' === $value ) {
			return '';
		}
	}

	return $block_content;
}
add_filter( 'render_block', 'tufte_blocks_project_filter_empty_bindings', 20, 3 );
```

- [ ] **Step 4: Run tests**

```bash
wpcli eval-file tests/run.php
```

Expected: `92 passed, 0 failed`.

- [ ] **Step 5: Commit**

```bash
git add inc/projects/bindings.php tests/test-resolver.php
git commit -m "Drop project blocks whose bound field resolves to empty

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 7: Editor sidebar panel

### Task 7.1: Enqueue and config

**Files:**
- Create: `inc/projects/editor.php`
- Modify: `functions.php` loader

- [ ] **Step 1: Write editor.php**

```php
<?php
/**
 * "Project details" sidebar panel: enqueue and config.
 *
 * Loads only when editing a project. The JS is vanilla wp.element (no JSX,
 * no build) and reads the field registry from window.tufteProjectFields.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the panel script on project edit screens.
 *
 * @return void
 */
function tufte_blocks_project_enqueue_editor(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'project' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	wp_enqueue_script(
		'tufte-blocks-project-fields',
		get_template_directory_uri() . '/assets/js/project-fields.js',
		array( 'wp-plugins', 'wp-editor', 'wp-components', 'wp-element', 'wp-data', 'wp-core-data', 'wp-api-fetch', 'wp-i18n' ),
		TUFTE_BLOCKS_VERSION,
		true
	);

	$fields = array();
	foreach ( tufte_blocks_project_fields() as $key => $field ) {
		$fields[] = array(
			'key'     => $key,
			'metaKey' => tufte_blocks_project_meta_key( $key ),
			'label'   => $field['label'],
			'type'    => $field['type'],
			'group'   => $field['group'],
			'manual'  => $field['manual'],
		);
	}

	wp_add_inline_script(
		'tufte-blocks-project-fields',
		'window.tufteProjectFields = ' . wp_json_encode( array( 'fields' => $fields, 'restBase' => 'tufte-blocks/v1/projects' ) ) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'tufte_blocks_project_enqueue_editor' );
```

Loader array gains `'editor'` after `'bindings'`.

### Task 7.2: The panel script

**Files:**
- Create: `assets/js/project-fields.js`

- [ ] **Step 1: Write the script**

```js
/**
 * Project details sidebar panel.
 *
 * Three parts: Source (identifiers the sync runs on), Synced values
 * (read-only, with Refresh now), Overrides (collapsed, placeholders show
 * the synced value). Vanilla wp.element, no build step.
 *
 * @package Tufte_Blocks
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var Fragment = wp.element.Fragment;
	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editor.PluginDocumentSettingPanel;
	var TextControl = wp.components.TextControl;
	var Button = wp.components.Button;
	var Spinner = wp.components.Spinner;
	var Notice = wp.components.Notice;
	var useSelect = wp.data.useSelect;
	var useEntityProp = wp.coreData.useEntityProp;
	var apiFetch = wp.apiFetch;
	var __ = wp.i18n.__;
	var config = window.tufteProjectFields || { fields: [], restBase: 'tufte-blocks/v1/projects' };

	var GROUP_LABELS = {
		source: __( 'Source', 'tufte-blocks' ),
		editorial: __( 'Editorial', 'tufte-blocks' ),
		release: __( 'Release', 'tufte-blocks' ),
		requirements: __( 'Requirements', 'tufte-blocks' ),
		links: __( 'Links', 'tufte-blocks' )
	};

	function byGroup( fields ) {
		var out = {};
		fields.forEach( function ( f ) {
			( out[ f.group ] = out[ f.group ] || [] ).push( f );
		} );
		return out;
	}

	function heading( text ) {
		return el( 'p', { style: { margin: '16px 0 4px', fontWeight: 600, textTransform: 'uppercase', fontSize: '11px', letterSpacing: '0.05em' } }, text );
	}

	function ManualField( props ) {
		return el( TextControl, {
			__nextHasNoMarginBottom: true,
			__next40pxDefaultSize: true,
			label: props.field.label,
			type: props.field.type === 'url' ? 'url' : 'text',
			value: props.meta[ props.field.metaKey ] || '',
			onChange: function ( value ) {
				var next = {};
				next[ props.field.metaKey ] = value;
				props.setMeta( Object.assign( {}, props.meta, next ) );
			}
		} );
	}

	function Panel() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );
		var postId = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostId();
		}, [] );
		var initialSynced = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostAttribute( 'project_synced' );
		}, [] );

		if ( postType !== 'project' ) {
			return null;
		}

		var metaPair = useEntityProp( 'postType', 'project', 'meta' );
		var meta = metaPair[ 0 ] || {};
		var setMeta = metaPair[ 1 ];

		var syncedState = useState( null );
		var synced = syncedState[ 0 ] || initialSynced || { resolved: {}, errors: {}, fetched_at: '' };
		var setSynced = syncedState[ 1 ];
		var busyState = useState( false );
		var errorState = useState( '' );
		var showOverridesState = useState( false );

		var groups = byGroup( config.fields );
		var manual = config.fields.filter( function ( f ) { return f.manual; } );
		var derived = config.fields.filter( function ( f ) { return ! f.manual; } );

		function refresh() {
			busyState[ 1 ]( true );
			errorState[ 1 ]( '' );
			apiFetch( { path: '/' + config.restBase + '/' + postId + '/sync', method: 'POST' } )
				.then( function ( data ) { setSynced( data ); } )
				.catch( function ( err ) { errorState[ 1 ]( ( err && err.message ) || __( 'Refresh failed.', 'tufte-blocks' ) ); } )
				.finally( function () { busyState[ 1 ]( false ); } );
		}

		var sourceErrors = Object.keys( synced.errors || {} ).map( function ( source ) {
			return el( Notice, { key: source, status: 'warning', isDismissible: false }, source + ': ' + synced.errors[ source ] );
		} );

		return el( PluginDocumentSettingPanel, { name: 'tufte-project-details', title: __( 'Project details', 'tufte-blocks' ) },
			// 1. Source and editorial (manual fields).
			[ 'source', 'editorial' ].map( function ( group ) {
				return el( Fragment, { key: group },
					heading( GROUP_LABELS[ group ] ),
					( groups[ group ] || [] ).filter( function ( f ) { return f.manual; } ).map( function ( f ) {
						return el( ManualField, { key: f.key, field: f, meta: meta, setMeta: setMeta } );
					} )
				);
			} ),
			// 2. Synced values.
			heading( __( 'Synced values', 'tufte-blocks' ) ),
			el( 'p', { style: { fontSize: '12px', color: '#757575', margin: '0 0 8px' } },
				synced.fetched_at
					? __( 'Last fetched: ', 'tufte-blocks' ) + new Date( synced.fetched_at ).toLocaleString()
					: __( 'Never fetched. Save the identifiers above, then refresh.', 'tufte-blocks' )
			),
			sourceErrors,
			errorState[ 0 ] ? el( Notice, { status: 'error', isDismissible: false }, errorState[ 0 ] ) : null,
			el( 'dl', { style: { display: 'grid', gridTemplateColumns: 'auto 1fr', gap: '4px 12px', fontSize: '12px', margin: '0 0 8px' } },
				derived.map( function ( f ) {
					var value = ( synced.resolved || {} )[ f.key ] || '';
					return el( Fragment, { key: f.key },
						el( 'dt', { style: { color: '#757575' } }, f.label ),
						el( 'dd', { style: { margin: 0, wordBreak: 'break-all' } }, value || '—' )
					);
				} )
			),
			el( Button, { variant: 'secondary', onClick: refresh, disabled: busyState[ 0 ] || ! postId },
				busyState[ 0 ] ? el( Spinner ) : __( 'Refresh now', 'tufte-blocks' )
			),
			// 3. Overrides, collapsed.
			heading( __( 'Overrides', 'tufte-blocks' ) ),
			el( Button, { variant: 'link', onClick: function () { showOverridesState[ 1 ]( ! showOverridesState[ 0 ] ); } },
				showOverridesState[ 0 ] ? __( 'Hide overrides', 'tufte-blocks' ) : __( 'Show overrides', 'tufte-blocks' )
			),
			showOverridesState[ 0 ] ? derived.map( function ( f ) {
				var placeholder = ( synced.resolved || {} )[ f.key ] || '';
				return el( TextControl, {
					key: f.key,
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
					label: f.label,
					type: f.type === 'url' ? 'url' : 'text',
					value: meta[ f.metaKey ] || '',
					placeholder: placeholder,
					help: placeholder ? __( 'Clear to use the synced value.', 'tufte-blocks' ) : '',
					onChange: function ( value ) {
						var next = {};
						next[ f.metaKey ] = value;
						setMeta( Object.assign( {}, meta, next ) );
					}
				} );
			} ) : null
		);
	}

	registerPlugin( 'tufte-blocks-project-details', { render: Panel, icon: null } );
} )( window.wp );
```

Note: `synced.resolved` values from the REST field are unformatted (`format=false` in `tufte_blocks_project_synced_summary`), so the placeholders show raw `6.4`, matching what an override should contain.

- [ ] **Step 2: Load the editor for project 5285 and check**

Open `http://$PORT/wp-admin/post.php?post=5285&action=edit` in the Browser pane. Verify:

1. A "Project details" panel appears in the Post sidebar with Source (slug `scroll-indicator`, GitHub URL), Editorial (empty tagline, empty demo URL), Synced values listing Version `1.0.2`, Released `2026-09-14 7:08pm GMT`, and so on, plus a "Last fetched" time.
2. "Refresh now" spins and returns without error.
3. "Show overrides" reveals ten inputs with the synced values as placeholders.
4. Type a tagline `A quiet cue that there is more below the fold.` and click Update. Reload; it persists. Check via REST:

```bash
curl -s -H "Host: derekhansonblog.wp.local" "http://$PORT/wp-json/wp/v2/project/5285?_fields=meta" | python3 -c "import json,sys;print(json.load(sys.stdin)['meta']['project_tagline'])"
```

5. Browser console shows no errors from `project-fields.js`.

Repeat on 5284: set tagline `The typography-first theme this site runs on.`

- [ ] **Step 3: Commit**

```bash
git add inc/projects/editor.php assets/js/project-fields.js functions.php
git commit -m "Add the Project details sidebar panel

Source identifiers, synced values with Refresh now, and collapsed
overrides. Vanilla wp.element against the wp.* globals; no build step.

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 8: Archive template and CSS

### Task 8.1: Rewrite `templates/archive-project.html`

**Files:**
- Rewrite: `templates/archive-project.html`

- [ ] **Step 1: Replace the file**

```html
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","className":"tufte-projects-archive","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|70"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<main class="wp-block-group tufte-projects-archive" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--80)">

	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"className":"tufte-eyebrow","textColor":"primary"} -->
		<p class="tufte-eyebrow has-primary-color has-text-color">Projects</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"className":"tufte-projects-title"} -->
		<h1 class="wp-block-heading tufte-projects-title">Things I have built for WordPress.</h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"tufte-projects-intro","textColor":"secondary"} -->
		<p class="tufte-projects-intro has-secondary-color has-text-color">Blocks, plugins, and themes, each one narrow in scope and released when there is something worth releasing. Source is public; support happens in the open.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":10,"query":{"perPage":50,"pages":0,"offset":0,"postType":"project","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide"} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"className":"tufte-project-grid","style":{"spacing":{"blockGap":"var:preset|spacing|60"}},"layout":{"type":"grid","minimumColumnWidth":"320px"}} -->

			<!-- wp:group {"tagName":"article","className":"tufte-project-card","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
			<article class="wp-block-group tufte-project-card has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px">

				<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1544/500","scale":"cover","className":"tufte-project-banner"} /-->

				<!-- wp:group {"className":"tufte-project-body","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
				<div class="wp-block-group tufte-project-body" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

					<!-- wp:post-terms {"term":"project_type","separator":" · ","className":"tufte-eyebrow tufte-project-type","textColor":"primary"} /-->

					<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"x-large","className":"tufte-project-title"} /-->

					<!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false,"excerptLength":30,"textColor":"secondary","fontSize":"small","className":"tufte-project-excerpt"} /-->

					<!-- wp:group {"className":"tufte-project-meta","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
					<div class="wp-block-group tufte-project-meta">
						<!-- wp:paragraph {"className":"tufte-project-version","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"version"}}}}} -->
						<p class="tufte-project-version">1.0.0</p>
						<!-- /wp:paragraph -->

						<!-- wp:paragraph {"className":"tufte-project-date","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"release_date"}}}}} -->
						<p class="tufte-project-date">Jan 2026</p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

				</div>
				<!-- /wp:group -->

			</article>
			<!-- /wp:group -->

		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
			<!-- wp:paragraph {"textColor":"secondary"} -->
			<p class="has-secondary-color has-text-color">No projects yet. Check back soon.</p>
			<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->

</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```

### Task 8.2: Replace the PROJECT CARD CSS with the project section (archive half)

**Files:**
- Modify: `assets/css/patterns.css` (delete from the `PROJECT CARD` banner comment to end of file, append the new section)

- [ ] **Step 1: Remove the old section**

```bash
LINE=$(grep -n "^   PROJECT CARD" assets/css/patterns.css | cut -d: -f1)
head -n $((LINE-2)) assets/css/patterns.css > /tmp/patterns.css.new && mv /tmp/patterns.css.new assets/css/patterns.css
tail -5 assets/css/patterns.css
```

Expected: the file now ends with the closing `}` of the section that preceded PROJECT CARD, followed by a blank line.

- [ ] **Step 2: Append the archive CSS**

```css
/* ========================================
   PROJECTS
   Archive grid, card, single hero, buttons,
   details panel, patterns, project nav.
   Every value is a theme.json token.
   ======================================== */

/* --- Shared type helpers --- */
.tufte-project-mono,
.tufte-project-meta p,
.tufte-project-back,
.tufte-project-nav,
.tufte-project-dl,
.tufte-project-links .wp-block-button__link {
	font-family: var(--wp--preset--font-family--monospace);
}

/* --- Archive header --- */
.tufte-projects-archive .tufte-projects-title {
	max-width: 26ch;
}

.tufte-projects-archive .tufte-projects-intro {
	max-width: 62ch;
}

/* --- Card --- */
.tufte-project-card {
	position: relative;
	overflow: hidden;
	height: 100%;
	transition: border-color var(--wp--custom--transition),
	            transform var(--wp--custom--transition);
}

.tufte-project-card:hover,
.tufte-project-card:focus-within {
	border-color: var(--wp--preset--color--primary) !important;
	transform: translateY(-2px);
}

/* Banner: full bleed, fixed aspect, hairline below */
.tufte-project-card .tufte-project-banner {
	margin: 0;
	width: 100%;
	border-bottom: 1px solid var(--wp--preset--color--border);
}

.tufte-project-card .tufte-project-banner img {
	display: block;
	width: 100%;
	aspect-ratio: 1544 / 500;
	object-fit: cover;
	border-radius: 0;
}

/* Body fills the remaining height so the meta line sits at the bottom */
.tufte-project-card .tufte-project-body {
	flex: 1;
	width: 100%;
	min-width: 0;
}

.tufte-project-card .tufte-project-type,
.tufte-project-card .tufte-project-type a {
	color: var(--wp--preset--color--primary);
	text-decoration: none;
	margin-bottom: 0 !important;
}

.tufte-project-card .tufte-project-title {
	margin: 0;
}

.tufte-project-card .tufte-project-title a {
	color: var(--wp--preset--color--foreground);
	transition: color var(--wp--custom--transition);
}

.tufte-project-card .tufte-project-title a:hover {
	color: var(--wp--preset--color--primary);
}

/* Stretched link: the title link covers the whole card */
.tufte-project-card .tufte-project-title a::after {
	content: "";
	position: absolute;
	inset: 0;
	z-index: 1;
}

.tufte-project-card .tufte-project-excerpt p {
	margin: 0;
}

/* Meta line: v1.0.2 · Sep 2026 */
.tufte-project-card .tufte-project-meta {
	margin-top: auto !important;
	padding-top: var(--wp--preset--spacing--20);
}

.tufte-project-meta p {
	margin: 0;
	font-size: 0.7rem;
	letter-spacing: 0.1em;
	text-transform: uppercase;
	color: var(--wp--preset--color--secondary);
}

.tufte-project-meta .tufte-project-version::before {
	content: "v";
}

.tufte-project-meta .tufte-project-version + .tufte-project-date::before {
	content: "· ";
}

@media (prefers-reduced-motion: reduce) {
	.tufte-project-card,
	.tufte-project-card .tufte-project-title a {
		transition-duration: 0.01ms !important;
	}
}
```

- [ ] **Step 3: Verify the archive**

Open `http://$PORT/projects/` in the Browser pane at desktop width, then at 400px (resize_window width 400). Check:

- Two cards, Scroll Indicator first (newer date), two-up on desktop, one-up at 400px.
- Scroll Indicator shows the .org banner at 1544:500; Tufte Blocks card has no image area at all (block renders nothing without a thumbnail) and the body starts at the top border.
- Eyebrow `PLUGIN` / `THEME` in gold mono; H2 title; excerpt; meta line `V1.0.2 · SEP 2026` for Scroll Indicator and `V1.4.4 · FEB 2026` for Tufte Blocks.
- Hover lifts the card and turns the border gold; the whole card is clickable.
- Switch style variation to Light in the Site Editor and reload: colors follow.

```bash
curl -s -H "Host: derekhansonblog.wp.local" -L "http://$PORT/projects/" | grep -o 'tufte-project-version">[^<]*' 
```

Expected: `tufte-project-version">1.0.2` and `tufte-project-version">1.4.4`.

- [ ] **Step 4: Commit**

```bash
git add templates/archive-project.html assets/css/patterns.css
git commit -m "Rebuild the projects archive as a banner-card grid bound to synced fields

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 8.3: Update `patterns/project-card.php` to match

**Files:**
- Rewrite: `patterns/project-card.php`

- [ ] **Step 1: Replace the file**

```php
<?php
/**
 * Title: Project Card
 * Slug: tufte-blocks/project-card
 * Categories: tufte-projects
 * Block Types: core/post-template
 * Description: A project card with a full-bleed banner, type eyebrow, title, excerpt, and version line. Matches the projects archive template.
 * Keywords: project, card, portfolio, showcase
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"article","className":"tufte-project-card","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"blockGap":"0"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<article class="wp-block-group tufte-project-card has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px">

	<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"1544/500","scale":"cover","className":"tufte-project-banner"} /-->

	<!-- wp:group {"className":"tufte-project-body","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
	<div class="wp-block-group tufte-project-body" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">

		<!-- wp:post-terms {"term":"project_type","separator":" · ","className":"tufte-eyebrow tufte-project-type","textColor":"primary"} /-->

		<!-- wp:post-title {"level":2,"isLink":true,"fontSize":"x-large","className":"tufte-project-title"} /-->

		<!-- wp:post-excerpt {"moreText":"","showMoreOnNewLine":false,"excerptLength":30,"textColor":"secondary","fontSize":"small","className":"tufte-project-excerpt"} /-->

		<!-- wp:group {"className":"tufte-project-meta","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group tufte-project-meta">
			<!-- wp:paragraph {"className":"tufte-project-version","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"version"}}}}} -->
			<p class="tufte-project-version">1.0.0</p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"tufte-project-date","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"release_date"}}}}} -->
			<p class="tufte-project-date">Jan 2026</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</article>
<!-- /wp:group -->
```

- [ ] **Step 2: Confirm the pattern and template card are identical**

```bash
diff <(sed -n '/^<!-- wp:group {"tagName":"article"/,/^<\/article>/p' templates/archive-project.html | sed 's/^\t\t\t//') \
     <(sed -n '/^<!-- wp:group {"tagName":"article"/,/^<\/article>/p' patterns/project-card.php) && echo SAME
```

Expected: `SAME`.

- [ ] **Step 3: Commit**

```bash
git add patterns/project-card.php
git commit -m "Match the project card pattern to the archive template

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 9: Single template, patterns, and CSS

### Task 9.1: Featured image caption filter (removed after review)

> Derek dropped the caption on 2026-09-23 after seeing it live. `inc/projects/render.php` and the figcaption CSS were removed; the featured figure renders bare.

**Files:**
- Create: `inc/projects/render.php`
- Modify: `functions.php` loader

The handoff's figure has a caption. The featured image block has no caption support, so the attachment's own caption (editable in the media library) is appended as a `<figcaption>` when rendering a project's featured image outside a query loop.

- [ ] **Step 1: Write render.php**

```php
<?php
/**
 * Project render tweaks that are not bindings.
 *
 * @package Tufte_Blocks
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Append the attachment caption to a project's featured image on the single
 * template. Skips cards (className tufte-project-banner) and posts without a
 * caption. Marks the figure with tufte-project-figure for styling.
 *
 * @param string   $block_content Rendered HTML.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function tufte_blocks_project_featured_caption( string $block_content, array $block, WP_Block $instance ): string {
	if ( 'core/post-featured-image' !== ( $block['blockName'] ?? '' ) || '' === $block_content ) {
		return $block_content;
	}
	if ( str_contains( (string) ( $block['attrs']['className'] ?? '' ), 'tufte-project-banner' ) ) {
		return $block_content;
	}
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : 0;
	if ( ! $post_id || 'project' !== get_post_type( $post_id ) ) {
		return $block_content;
	}
	$caption = wp_get_attachment_caption( (int) get_post_thumbnail_id( $post_id ) );
	if ( ! $caption ) {
		return $block_content;
	}
	$figcaption = '<figcaption class="wp-element-caption">' . wp_kses_post( $caption ) . '</figcaption>';
	$content    = preg_replace( '/<\/figure>\s*$/', $figcaption . '</figure>', $block_content, 1 );
	return str_replace( 'class="wp-block-post-featured-image', 'class="wp-block-post-featured-image tufte-project-figure', $content ?? $block_content );
}
add_filter( 'render_block', 'tufte_blocks_project_featured_caption', 10, 3 );
```

Loader array gains `'render'` after `'editor'`. The final loader list is: `post-type, tool-icons, fields, header-parser, sources/wporg, sources/github, sync, bindings, editor, render`.

- [ ] **Step 2: Lint and commit**

```bash
phplint inc/projects/render.php
git add inc/projects/render.php functions.php
git commit -m "Append the attachment caption to a project's featured image

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 9.2: Rewrite `templates/single-project.html`

**Files:**
- Rewrite: `templates/single-project.html`

Section order: back link, hero, featured figure, post content, details panel, project nav. Every bound block uses `tufte-blocks/project-field`; empty ones vanish via the filter.

- [ ] **Step 1: Replace the file**

```html
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","className":"tufte-project-single","style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|70"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<main class="wp-block-group tufte-project-single" style="padding-top:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--80)">

	<!-- wp:paragraph {"className":"tufte-project-back","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} -->
	<p class="tufte-project-back" style="margin-bottom:var(--wp--preset--spacing--60)"><a href="/projects/">← All projects</a></p>
	<!-- /wp:paragraph -->

	<!-- wp:group {"tagName":"section","align":"wide","className":"tufte-project-hero","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<section class="wp-block-group alignwide tufte-project-hero">
		<!-- wp:post-terms {"term":"project_type","separator":" · ","className":"tufte-eyebrow tufte-project-type","textColor":"primary"} /-->

		<!-- wp:post-title {"level":1,"className":"tufte-project-h1"} /-->

		<!-- wp:paragraph {"className":"tufte-project-tagline","fontSize":"large","textColor":"secondary","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"tagline"}}}}} -->
		<p class="tufte-project-tagline has-secondary-color has-text-color has-large-font-size">Tagline</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"className":"tufte-project-actions","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
		<div class="wp-block-buttons tufte-project-actions">
			<!-- wp:button {"className":"tufte-project-download","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"link_download"}}}}} -->
			<div class="wp-block-button tufte-project-download"><a class="wp-block-button__link wp-element-button">Download</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-outline tufte-project-demo","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"demo_url"}}}}} -->
			<div class="wp-block-button is-style-outline tufte-project-demo"><a class="wp-block-button__link wp-element-button">Demo</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-link","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"link_directory"}}}}} -->
			<div class="wp-block-button is-style-link"><a class="wp-block-button__link wp-element-button">Plugin directory</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</section>
	<!-- /wp:group -->

	<!-- wp:post-featured-image {"align":"wide","className":"tufte-project-figure"} /-->

	<!-- wp:post-content {"layout":{"inherit":true,"justifyContent":"left"}} /-->

	<!-- wp:group {"tagName":"aside","className":"tufte-project-details","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<aside class="wp-block-group tufte-project-details has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
		<!-- wp:heading {"level":3,"className":"tufte-project-details-title","fontFamily":"monospace","style":{"typography":{"fontSize":"0.9rem","textTransform":"uppercase","letterSpacing":"0.05em"}}} -->
		<h3 class="wp-block-heading tufte-project-details-title has-monospace-font-family" style="font-size:0.9rem;letter-spacing:0.05em;text-transform:uppercase">Details</h3>
		<!-- /wp:heading -->

		<!-- wp:group {"className":"tufte-project-dl","layout":{"type":"default"}} -->
		<div class="wp-block-group tufte-project-dl">
			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">Version</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"version"}}}}} -->
				<p class="tufte-project-value">1.0.0</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">Released</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"release_date"}}}}} -->
				<p class="tufte-project-value">Jan 2026</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">WordPress</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"requires_wp"}}}}} -->
				<p class="tufte-project-value">6.4 or higher</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">Tested to</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"tested_up_to"}}}}} -->
				<p class="tufte-project-value">7.0</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">PHP</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"requires_php"}}}}} -->
				<p class="tufte-project-value">7.4 or higher</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"tufte-project-detail","layout":{"type":"default"}} -->
			<div class="wp-block-group tufte-project-detail">
				<!-- wp:paragraph {"className":"tufte-project-label"} -->
				<p class="tufte-project-label">License</p>
				<!-- /wp:paragraph -->
				<!-- wp:paragraph {"className":"tufte-project-value","metadata":{"bindings":{"content":{"source":"tufte-blocks/project-field","args":{"key":"license"}}}}} -->
				<p class="tufte-project-value">GPL v2 or later</p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->

		<!-- wp:buttons {"className":"tufte-project-links","layout":{"type":"flex","orientation":"vertical"},"style":{"spacing":{"blockGap":"var:preset|spacing|20"}}} -->
		<div class="wp-block-buttons tufte-project-links">
			<!-- wp:button {"className":"is-style-link","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"github_url"}}}}} -->
			<div class="wp-block-button is-style-link"><a class="wp-block-button__link wp-element-button">Browse the code</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-link","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"link_support"}}}}} -->
			<div class="wp-block-button is-style-link"><a class="wp-block-button__link wp-element-button">Support</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-link","metadata":{"bindings":{"url":{"source":"tufte-blocks/project-field","args":{"key":"link_translate"}}}}} -->
			<div class="wp-block-button is-style-link"><a class="wp-block-button__link wp-element-button">Translate</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</aside>
	<!-- /wp:group -->

	<!-- wp:group {"tagName":"nav","align":"wide","className":"tufte-project-nav","style":{"border":{"top":{"color":"var:preset|color|border","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|50"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<nav class="wp-block-group alignwide tufte-project-nav" style="border-top-color:var(--wp--preset--color--border);border-top-width:1px;padding-top:var(--wp--preset--spacing--50)">
		<!-- wp:paragraph -->
		<p><a href="/projects/">← All projects</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:post-navigation-link {"type":"next","label":"","showTitle":true,"arrow":"arrow"} /-->
	</nav>
	<!-- /wp:group -->

</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```

### Task 9.3: Append the single-template CSS

**Files:**
- Modify: `assets/css/patterns.css` (append after the archive block from Task 8.2)

- [ ] **Step 1: Append**

```css
/* --- Single: back link and project nav --- */
.tufte-project-back,
.tufte-project-nav {
	text-transform: uppercase;
	letter-spacing: 0.1em;
}

.tufte-project-back {
	font-size: 0.7rem;
	margin-top: 0 !important;
}

.tufte-project-nav {
	font-size: 0.8rem;
	letter-spacing: 0.05em;
}

.tufte-project-nav p,
.tufte-project-nav .wp-block-post-navigation-link {
	margin: 0;
}

/* --- Single: hero --- */
.tufte-project-hero .tufte-project-type,
.tufte-project-hero .tufte-project-type a {
	color: var(--wp--preset--color--primary);
	text-decoration: none;
}

.tufte-project-hero .tufte-project-h1 {
	font-size: clamp(2.5rem, 6vw, 3rem);
	max-width: 30ch;
}

.tufte-project-hero .tufte-project-tagline {
	font-style: italic;
	line-height: 1.5;
	max-width: 44ch;
	margin-bottom: var(--wp--preset--spacing--60) !important;
}

/* Primary button: theme default plus the handoff's roomier padding */
.tufte-project-actions .wp-block-button__link {
	padding: 0.75rem 1.5rem;
}

/* Secondary button: hairline border, foreground text, gold on hover */
.tufte-project-actions .is-style-outline .wp-block-button__link {
	border-color: var(--wp--preset--color--border);
	color: var(--wp--preset--color--foreground);
}

.tufte-project-actions .is-style-outline .wp-block-button__link:hover,
.tufte-project-actions .is-style-outline .wp-block-button__link:focus {
	background: transparent;
	border-color: var(--wp--preset--color--primary);
	color: var(--wp--preset--color--primary);
	box-shadow: none;
}

/* Tertiary (link style) keeps default padding so it aligns with the buttons */
.tufte-project-actions .is-style-link .wp-block-button__link {
	padding: 0.75rem 0;
}

/* --- Single: featured figure --- */
.tufte-project-single .tufte-project-figure img {
	width: 100%;
	border-radius: 0;
}

.tufte-project-single .tufte-project-figure .wp-element-caption {
	font-size: var(--wp--preset--font-size--small);
	color: var(--wp--preset--color--secondary);
	margin-top: var(--wp--preset--spacing--30);
}

/* --- Single: details panel --- */
.tufte-project-details {
	max-width: 480px;
}

.tufte-project-details .tufte-project-details-title {
	margin: 0;
}

.tufte-project-dl {
	display: grid;
	grid-template-columns: auto 1fr;
	gap: var(--wp--preset--spacing--20) var(--wp--preset--spacing--50);
	font-size: 0.8rem;
}

.tufte-project-dl .tufte-project-detail {
	display: contents;
}

.tufte-project-dl p {
	margin: 0;
}

.tufte-project-dl .tufte-project-label {
	color: var(--wp--preset--color--secondary);
}

.tufte-project-dl .tufte-project-value {
	color: var(--wp--preset--color--foreground);
}

.tufte-project-links .wp-block-button__link {
	font-size: 0.8rem;
	padding: 0;
}

/* --- Pattern: features grid --- */
.tufte-project-features .tufte-project-features-title {
	max-width: 34ch;
}

.tufte-project-feature {
	transition: border-color var(--wp--custom--transition),
	            transform var(--wp--custom--transition);
}

.tufte-project-feature:hover {
	border-color: var(--wp--preset--color--primary) !important;
	transform: translateY(-2px);
}

.tufte-project-feature h3 {
	margin-bottom: var(--wp--preset--spacing--20);
}

.tufte-project-feature p {
	margin: 0;
}

.tufte-project-feature code {
	font-family: var(--wp--preset--font-family--monospace);
	font-size: 0.85em;
}

/* --- Pattern: install steps (01, 02, ...) --- */
.tufte-project-steps {
	list-style: none;
	padding-left: 0;
	margin: 0;
	max-width: 46ch;
	counter-reset: tufte-step;
	display: flex;
	flex-direction: column;
	gap: 1.25rem;
}

.tufte-project-steps li {
	position: relative;
	padding-left: 2.5rem;
	counter-increment: tufte-step;
}

/* Positioned rather than flex so an inline <code> stays in the text run */
.tufte-project-steps li::before {
	content: counter(tufte-step, decimal-leading-zero);
	position: absolute;
	left: 0;
	top: 0.45rem;
	font-family: var(--wp--preset--font-family--monospace);
	font-size: 0.7rem;
	letter-spacing: 0.1em;
	color: var(--wp--preset--color--primary);
}

/* --- Pattern: FAQ (hairline rows instead of the theme's boxed details) --- */
.tufte-project-faq .wp-block-details {
	border: 0;
	border-top: 1px solid var(--wp--preset--color--border);
	border-radius: 0;
	padding: 1.25rem 0;
	margin: 0;
}

.tufte-project-faq .wp-block-details:last-child {
	border-bottom: 1px solid var(--wp--preset--color--border);
}

.tufte-project-faq .wp-block-details summary {
	padding: 0;
	font-size: var(--wp--preset--font-size--medium);
}

.tufte-project-faq .wp-block-details > *:not(summary) {
	padding: 0;
	margin-top: var(--wp--preset--spacing--30);
	font-size: var(--wp--preset--font-size--small);
	color: var(--wp--preset--color--secondary);
}

@media (prefers-reduced-motion: reduce) {
	.tufte-project-feature {
		transition-duration: 0.01ms !important;
	}
}
```

- [ ] **Step 2: Verify the single template**

Open `http://$PORT/projects/scroll-indicator/` and `http://$PORT/projects/tufte-blocks/` in the Browser pane, desktop and 400px, dark and light. Check:

- Scroll Indicator: back link, `PLUGIN` eyebrow, H1, italic tagline, Download (filled) + Plugin directory (text link). **No Demo button** (demo_url empty). Banner figure. Details: six rows filled, links Browse the code, Support, Translate.
- Tufte Blocks: `THEME` eyebrow, tagline, Download (v1.3.0 zip) + Demo (outline, to derekhanson.blog). **No Plugin directory link.** No figure (no thumbnail). Details: Version `1.4.4`, Released `Feb 2026`, WordPress `6.4 or higher`, Tested to `6.9`, PHP `7.4 or higher`, License `GNU General Public License v2 or later`. Links: Browse the code, Support. **No Translate.**
- Project nav: "← All projects" left; on the older project (Tufte Blocks) the right side shows "Scroll Indicator →"; on the newest it shows nothing.

```bash
curl -s -H "Host: derekhansonblog.wp.local" -L "http://$PORT/projects/scroll-indicator/" | grep -c "tufte-project-demo"
curl -s -H "Host: derekhansonblog.wp.local" -L "http://$PORT/projects/tufte-blocks/" | grep -c "Plugin directory"
curl -s -H "Host: derekhansonblog.wp.local" -L "http://$PORT/projects/tufte-blocks/" | grep -o 'tufte-project-value">[^<]*'
```

Expected: `0`, `0`, then six values as listed above.

- [ ] **Step 3: Site Editor check**

Open `http://$PORT/wp-admin/site-editor.php?postType=wp_template` and open both project templates. Expected: no "Block Recovery" prompt, bound paragraphs show their placeholder text with the binding chip.

- [ ] **Step 4: Commit**

```bash
git add templates/single-project.html assets/css/patterns.css
git commit -m "Rebuild the single project template around bound project fields

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 9.4: Content patterns

**Files:**
- Create: `patterns/project-features.php`, `patterns/project-install-steps.php`, `patterns/project-faq.php`

- [ ] **Step 1: `patterns/project-features.php`**

```php
<?php
/**
 * Title: Project Features
 * Slug: tufte-blocks/project-features
 * Categories: tufte-projects
 * Post Types: project
 * Description: Eyebrow, heading, and a grid of six feature cards for a project page.
 * Keywords: project, features, grid, cards
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","align":"wide","className":"tufte-project-features","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group alignwide tufte-project-features">
	<!-- wp:heading {"level":2,"className":"tufte-project-features-title","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} -->
	<h2 class="wp-block-heading tufte-project-features-title" style="margin-bottom:var(--wp--preset--spacing--60)">Editor controls you already know.</h2>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"tufte-project-feature-grid","style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","minimumColumnWidth":"260px"}} -->
	<div class="wp-block-group tufte-project-feature-grid">
		<?php
		$tufte_features = array(
			array( 'Native controls', 'Text color, spacing, typography, and alignment come from core block supports, not a parallel settings panel.' ),
			array( 'Positioning', 'Fixed to the screen, or dragged into place with absolute positioning for hero-style sections.' ),
			array( 'CSS-only motion', 'Animation is CSS and respects <code>prefers-reduced-motion</code>. No animation libraries.' ),
			array( 'Click to scroll', 'A real button element. Click or keyboard-activate it to move down the page; it hides itself once scrolling starts.' ),
			array( 'Five icon styles', 'Mouse, arrow, chevron, dots, and hand, at preset sizes or any custom CSS size value.' ),
			array( 'Works without JS', 'The icon and label still render with JavaScript off. Script only powers click-to-scroll and hide-on-scroll.' ),
		);
		foreach ( $tufte_features as $tufte_feature ) :
			?>
		<!-- wp:group {"className":"tufte-project-feature","style":{"border":{"width":"1px","color":"var:preset|color|border","radius":"4px"},"spacing":{"padding":{"top":"var:preset|spacing|50","right":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
		<div class="wp-block-group tufte-project-feature has-border-color" style="border-color:var(--wp--preset--color--border);border-width:1px;border-radius:4px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php echo esc_html( $tufte_feature[0] ); ?></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"fontSize":"small","textColor":"secondary"} -->
			<p class="has-secondary-color has-text-color has-small-font-size"><?php echo wp_kses( $tufte_feature[1], array( 'code' => array() ) ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
```

- [ ] **Step 2: `patterns/project-install-steps.php`**

```php
<?php
/**
 * Title: Project Install Steps
 * Slug: tufte-blocks/project-install-steps
 * Categories: tufte-projects
 * Post Types: project
 * Description: Heading and a numbered list of install steps for a project page.
 * Keywords: project, install, steps
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","className":"tufte-project-install","style":{"border":{"top":{"color":"var:preset|color|border","width":"1px"}},"spacing":{"padding":{"top":"var:preset|spacing|70"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group tufte-project-install" style="border-top-color:var(--wp--preset--color--border);border-top-width:1px;padding-top:var(--wp--preset--spacing--70)">
	<!-- wp:heading {"level":2} -->
	<h2 class="wp-block-heading">Four steps, no configuration.</h2>
	<!-- /wp:heading -->

	<!-- wp:list {"ordered":true,"className":"tufte-project-steps"} -->
	<ol class="wp-block-list tufte-project-steps">
		<!-- wp:list-item -->
		<li>Install from the Plugins screen, or upload the folder to <code>/wp-content/plugins/</code>.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Activate it.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Open the block editor and add the Scroll Indicator block.</li>
		<!-- /wp:list-item -->

		<!-- wp:list-item -->
		<li>Choose an icon style, size, color, and optional label.</li>
		<!-- /wp:list-item -->
	</ol>
	<!-- /wp:list -->
</section>
<!-- /wp:group -->
```

- [ ] **Step 3: `patterns/project-faq.php`**

```php
<?php
/**
 * Title: Project FAQ
 * Slug: tufte-blocks/project-faq
 * Categories: tufte-projects
 * Post Types: project
 * Description: Heading and four expandable questions for a project page.
 * Keywords: project, faq, questions, details
 *
 * @package Tufte_Blocks
 */

?>

<!-- wp:group {"tagName":"section","className":"tufte-project-faq","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
<section class="wp-block-group tufte-project-faq">
	<!-- wp:heading {"level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
	<h2 class="wp-block-heading" style="margin-bottom:var(--wp--preset--spacing--50)">Before you ask.</h2>
	<!-- /wp:heading -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Does it need a JavaScript animation library?</summary>
	<!-- wp:paragraph -->
	<p>No. Animations are CSS. The front-end script only powers click-to-scroll and automatic hide-on-scroll.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Can I change the icon color?</summary>
	<!-- wp:paragraph -->
	<p>Yes. The block uses core text color support, so color comes from the normal block controls and your theme palette.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>Does it respect reduced-motion preferences?</summary>
	<!-- wp:paragraph -->
	<p>Yes. Animated effects only run when the visitor has not requested reduced motion.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->

	<!-- wp:details -->
	<details class="wp-block-details"><summary>What happens with JavaScript off?</summary>
	<!-- wp:paragraph -->
	<p>The icon and optional text still render. Only click-to-scroll and hide-on-scroll require JavaScript.</p>
	<!-- /wp:paragraph -->
	</details>
	<!-- /wp:details -->
</section>
<!-- /wp:group -->
```

- [ ] **Step 4: Lint and confirm the patterns register**

WordPress caches the theme's pattern files by theme version when `WP_DEBUG` is off (it is off in Studio), so new pattern files are invisible until the cache is cleared or the version changes. Clear it once after adding files, in its own process:

```bash
wpcli eval 'wp_get_theme()->delete_pattern_cache();'
```


```bash
for f in patterns/project-*.php; do phplint "$f"; done
wpcli eval 'foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $p ) { if ( str_starts_with( $p["name"], "tufte-blocks/project" ) ) echo $p["name"], "\n"; }'
```

Expected: four names: `project-card`, `project-faq`, `project-features`, `project-install-steps`.

- [ ] **Step 5: Insert all three patterns into Scroll Indicator's content**

In the editor for 5285, after the existing intro paragraph: replace the old "Features" heading and list with the Project Features pattern, add Project Install Steps, then Project FAQ. Keep the two "How It Works" paragraphs above the features pattern. Update. Then check the frontend at desktop and 400px: feature grid three-up then one-up, steps numbered `01` to `04` in gold, FAQ rows with `+` toggles that rotate to `×`, no Block Recovery prompt in the editor.

- [ ] **Step 6: Commit**

```bash
git add patterns/project-features.php patterns/project-install-steps.php patterns/project-faq.php
git commit -m "Add project features, install steps, and FAQ patterns

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

---

## Milestone 10: Content pass, version bump, PR, deploy

### Task 10.1: Content pass

- [ ] **Step 1: Tufte Blocks content (5284)**: in the editor, insert Project Features and edit the six cards to describe the theme (headings suggested: Typography first, Dark by default, Light variation, Block patterns, Full-site editing, Minimal ornamentation; bodies from the existing bullet list, em dashes removed). Delete the old "Requirements" list; the details panel covers it. Insert Project FAQ with two or three theme questions, or skip it if there is nothing to ask yet.
- [ ] **Step 2: Featured image for Tufte Blocks**: upload `~/Downloads/Claude Design Handoffs/design_handoff_projects_cpt/reference/assets/tufte-blocks-screenshot.png` as the featured image, or the theme's `screenshot.png`, cropped to 1544×500 if Derek prefers. The card and figure render without it, so this is optional for the pass.
- [ ] **Step 3: Captions**: in the media library, give the Scroll Indicator banner the caption `The block in place at the foot of a hero section.` and confirm the figcaption appears under the figure.
- [ ] **Step 4: Confirm excerpts**: the archive card shows 30 words. Trim both excerpts in the editor if they wrap past three lines at 400px.

### Task 10.2: Version bump

**Files:**
- Modify: `style.css` line 7, `functions.php` version define, `README.md` (optional line about projects)

- [ ] **Step 1: Bump**

```bash
sed -i '' 's/^Version: 1.4.4$/Version: 1.5.0/' style.css
sed -i '' "s/define( 'TUFTE_BLOCKS_VERSION', '1.4.4' );/define( 'TUFTE_BLOCKS_VERSION', '1.5.0' );/" functions.php
grep -n "1.5.0" style.css functions.php
```

Expected: one hit in each file.

- [ ] **Step 2: Full test run and a final visual sweep**

```bash
wpcli eval-file tests/run.php
git status --short
```

Expected: `92 passed, 0 failed`; only `style.css` and `functions.php` modified. Then open the archive and one single page once more in dark and light, desktop and 400px, and with `prefers-reduced-motion: reduce` emulated (card hover no longer animates).

- [ ] **Step 3: Commit**

```bash
git add style.css functions.php
git commit -m "Bump version to 1.5.0

Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>"
```

### Task 10.3: Hand off

Do **not** push or open a PR until Derek asks. When he does:

```bash
git push -u origin projects-cpt
gh pr create --repo dhanson-wp/tufte-blocks --base main --head projects-cpt --title "Projects CPT: archive and single templates (v1.5.0)" --body-file docs/superpowers/plans/2026-09-14-projects-cpt-pr-body.md
```

Merging to `main` triggers the SFTP deploy. After it lands: on WordPress.com, open Scroll Indicator in the editor, set the .org slug to `scroll-indicator`, click Refresh now, confirm the featured image was sideloaded, and set both taglines. The cron event schedules itself on the first request after deploy.

---

## Self-review against the spec

**Spec coverage.** Reconcile (M1), split (M2), registry + 14 meta keys + hidden cache (3.1), adapters + shared header parser + tests (M4), sync with cron/REST/CLI/sideload/failure-keeps-cache (M5, tested in 5.1 step 3 and 5.2 step 3), binding source + resolver + empty filter + tests (M6), sidebar with Source / Synced / Overrides and Refresh now (M7), archive grid at 320px min with date DESC and bound version/date (M8), single template order and details panel from core blocks (9.2), three content patterns (9.4), FAQ reusing the theme details toggle (9.4 + CSS), deploy excludes (1.3), 1.5.0 bump (10.2), demo deferred (Demo button bound to `demo_url`, renders nothing when empty).

**Deliberate deviations, all small.** (1) A fifth registry group `editorial` holds tagline and demo URL; the spec's four groups had no home for them. (2) The figure caption comes from the attachment caption via `inc/projects/render.php`, since the data model has no caption field and the featured image block cannot carry one. (3) The details panel sits below post content rather than beside the install steps, which follows from the spec's own split of template vs. content. (4) Em dashes removed from all copy per Derek's voice rule.

**Type consistency check.** `tufte_blocks_project_resolve_field( string, string, array, bool = true ): string` is called with four args in `synced_summary` and with three in tests. `tufte_blocks_project_binding_value( array, WP_Block, string ): string` matches `get_value_callback` and the filter's call. `tufte_blocks_project_get_cache()` returns the four-key shape that `merge_source` and `resolve_field` read. Loader order puts `fields` before `header-parser`, both before `sources/*`, before `sync`, before `bindings`. `tufte_blocks_project_user_agent()` is defined in `sources/wporg.php` and used in `sources/github.php`, which loads after it.
