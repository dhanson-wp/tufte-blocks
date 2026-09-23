# Projects CPT: archive and single templates

Design spec, 2026-09-14. Source: `design_handoff_projects_cpt` (Claude Design handoff,
unpacked to `~/Downloads/Claude Design Handoffs/`).

## Goal

Get Derek's projects live on derekhanson.blog with the archive and single templates from
the handoff design. Scroll Indicator and Tufte Blocks are the first two projects.

## Starting state

The `project` CPT already exists, but only in the Studio site copy of the theme, which
isn't under version control:

| Location | Version | Has project code? | Git? |
|---|---|---|---|
| `Studio/derekhansonblog/wp-content/themes/tufte-blocks/` | 1.3.0 | yes | no |
| `Studio/tufte-blocks/wp-content/themes/tufte-blocks/` | 1.4.4 | no | yes, `main` |

The repo is what SFTP-mirrors to WordPress.com on push to `main`, so nothing
project-related can reach production today. That's the first thing to fix.

Already built in the site copy:

- `project` CPT, archive slug `projects`, supports title/editor/excerpt/thumbnail/
  custom-fields/revisions/page-attributes
- `project_type` taxonomy (terms: Plugin, Theme) and `project_tool` (terms: Claude, Cursor)
- Post meta `project_github_url`, `project_demo_url`
- Term meta `tool_icon` plus its media-uploader admin UI (about 200 lines)
- `templates/archive-project.html`, `templates/single-project.html`, `patterns/project-card.php`
- `assets/images/tools/*.svg` (antigravity, claude, cursor, gemini, telex)

Content: two published projects, Scroll Indicator (5285) and Tufte Blocks (5284). Both have
excerpts and post content. **Neither has a featured image.**

The divergence between the copies is small and almost entirely one-directional. Only
`functions.php` and `assets/css/patterns.css` are modified in both places, and in both cases
the site's changes are pure appends. The repo is ahead on comments work (v1.4.1 through
1.4.4) that the site copy predates.

## Decisions

| Question | Decision |
|---|---|
| Where does the work live? | The repo. Port the site's project work into it. |
| How does Studio run it? | The site's theme folder becomes the git checkout. |
| Scope of this spec | Templates and data model. The interactive demo is deferred. |
| Field editing UI | Custom sidebar panel, vanilla JS, no build step. |
| Comments divergence | Repo wins. Discard the site copy's older version. |
| `project_type` | Stays a taxonomy. Terms exist and have content attached. |
| Section split | Meta-bound sections in the template, prose sections in post content. |
| Where field values come from | Derived from wordpress.org and GitHub, not typed by hand. |
| Source precedence | Manual override, then .org, then GitHub, then empty. |
| Fetch timing | Twice-daily cron writes to cache. The render path never makes a request. |
| Version for non-.org projects | Parsed from the `style.css` header on the default branch. |
| Featured images | Sideloaded once from the .org banner, never overwritten afterwards. |

### Why not a symlink

The obvious way to keep one source of truth is to symlink the site's theme folder at the
repo. It doesn't work. Studio pins `open_basedir` to the site root
(`/Users/derekhanson/Studio/derekhansonblog`), so PHP refuses to read anything that resolves
outside it. Tested and confirmed blocked.

Instead, the site's theme folder becomes the git working copy: copy the repo's `.git`
directory into it, and the local project work shows up as uncommitted changes against
`main`. Everything stays inside the site root, Studio is happy, and the reconciliation
becomes a reviewable `git status` rather than a manual file shuffle.

`Studio/tufte-blocks` stays where it is as a second clone for theme-only testing.

### Why no build step

The theme is plain PHP, CSS, and theme.json, with no `package.json` and no `node_modules`.
The deploy Action is a dumb `lftp mirror` of the checkout, so a JSX build would mean either
committing build artifacts or adding npm to the pipeline. Neither is worth it for one
settings panel.

The sidebar panel uses `wp.element.createElement` instead of JSX, against the `wp.*` globals
WordPress already enqueues. It gets real `TextControl` and `PluginDocumentSettingPanel`
components, just without the transpile.

## Data model

The handoff lists thirteen fields typed by hand. Most of them are already published facts
about each project, so instead of retyping them they're derived from the source of truth and
cached locally.

### Manual fields

Four fields are genuinely editorial or identify the sources, so they're typed in the editor:

| Meta key | Type | Purpose | Status |
|---|---|---|---|
| `project_tagline` | text | The italic deck under the H1. Editorial, has no upstream source. | new |
| `project_wporg_slug` | text | The .org plugin slug, e.g. `scroll-indicator`. Empty when a project isn't on .org. | new |
| `project_github_url` | url | Repo URL. Also the "Code" link in the details panel. | exists |
| `project_demo_url` | url | Demo link. Empty for now, see "What's deferred". | exists |

### Derived fields

Ten fields are written by the sync job, never typed:

| Field | .org source | GitHub source |
|---|---|---|
| `version` | `version` | `style.css` / plugin header `Version:` |
| `release_date` | `last_updated` | header date, else release `published_at` |
| `requires_wp` | `requires` | header `Requires at least:` |
| `tested_up_to` | `tested` | header `Tested up to:` |
| `requires_php` | `requires_php` | header `Requires PHP:` |
| `license` | header `License:` | `license.spdx_id`, ignoring `NOASSERTION` |
| `link_download` | `download_link` | latest release asset |
| `link_directory` | `https://wordpress.org/plugins/{slug}/` | not applicable |
| `link_support` | `support_url` | repo `/issues` |
| `link_translate` | `https://translate.wordpress.org/projects/wp-plugins/{slug}/` | not applicable |

Each derived field also gets an optional override meta key (`project_version`,
`project_license`, and so on). An override is used only when non-empty.

**Precedence: manual override, then .org, then GitHub, then empty.** A field that resolves
to empty means its block doesn't render at all.

.org wins over GitHub because the two genuinely disagree. As of 2026-09-14 the .org API
reports Scroll Indicator at 1.0.2 while the latest GitHub release is tagged v1.0.1. The
directory is what users install from, so it's authoritative for anything it publishes.

For projects with no .org listing, version comes from parsing the `style.css` or plugin
header on the repo's default branch, not from the releases API. Tufte Blocks is the reason:
its latest GitHub release is v1.3.0 while the theme is actually at 1.4.4, so releases would
publish a wrong number on Derek's own site. The file header is always current.

### Storage

The synced payload is one hidden meta key, `_project_source_cache`, holding the fetched
values plus a `fetched_at` timestamp. Individual override keys stay separate and
human-edited. Nothing in the render path ever reads a remote API.

### Taxonomies and core fields

`project_type` and `project_tool` stay taxonomies. Terms exist and have content attached, so
switching `project_type` to a text field, which the handoff floats as an open question, would
lose data for no gain. `post_title`, `post_content`, `post_excerpt`, and the featured image
stay core.

### Archive query

`post_type=project`, `orderby=date`, `order=DESC`, no pagination. The current template
queries `menu_order` ascending with `inherit:true`, so this changes.

## Components

Each unit below has one job and can be understood without reading the others.

### Field registry (`inc/projects/fields.php`)

A single PHP array is the source of truth for every field: key, label, type
(`text` | `url`), group (`source` | `release` | `requirements` | `links`), and whether it's
manual or derived. It drives `register_post_meta`, the sidebar panel config, and the
adapters' output mapping. Adding a field later is a one-line change in one file.

Depends on: nothing. Consumed by: meta registration, the editor panel, the adapters, the
resolver.

### Source adapters (`inc/projects/sources/`)

Two adapters, each a single function taking an identifier and returning a flat array of
field values or a `WP_Error`. Neither knows anything about WordPress posts.

- `wporg.php` — takes a slug, calls
  `api.wordpress.org/plugins/info/1.2/?action=plugin_information`, maps the response to
  field keys, and synthesises the directory and translate URLs from the slug.
- `github.php` — takes an owner/repo, calls the repo endpoint for `license.spdx_id`, and
  fetches `style.css` or the main plugin file from the default branch to parse the header
  block for version and requirement lines.

Both use `wp_remote_get` with a 10-second timeout and a descriptive user agent. A failed
fetch returns `WP_Error` and is logged. **A failure never overwrites good cached data**,
because a .org hiccup shouldn't blank the version number on a live page.

The header parser is shared: given file contents, return the standard WordPress header
fields. It's the one piece of real string handling here and needs direct tests.

### Sync job (`inc/projects/sync.php`)

A twice-daily WP-Cron event walks published projects, runs whichever adapters that project
has identifiers for, merges the results by precedence, and writes
`_project_source_cache`. Also exposed as a REST route so the sidebar's "Refresh now" button
can trigger a single project on demand, and as a WP-CLI command for debugging.

Depends on: the adapters, the field registry. Nothing depends on it, which is the point:
if sync never runs, the site still renders from whatever was last cached.

### Field resolver and binding source (`inc/projects/bindings.php`)

`register_block_bindings_source( 'tufte-blocks/project-field' )` exposes a single binding
source taking a `key` argument. Its callback runs the precedence chain: manual override,
then `_project_source_cache`, then empty.

This replaces per-key `core/post-meta` bindings and means templates don't need to know
which fields are derived. A block binds to `tufte-blocks/project-field` with
`{"key":"version"}` and gets the right answer regardless of where it came from.

The empty-value `render_block` filter stays: Block Bindings renders an empty element when a
value resolves to nothing, and the handoff is explicit that a button with no URL must not
render at all, never falling back to `#`.

This file holds the only branching logic in the feature and carries the test burden:
resolution order, empty handling, unregistered keys, and blocks with no bindings passing
through untouched.

### Banner sideload (one-time, `inc/projects/sync.php`)

When a project has a .org slug, no featured image, and the API returns a high-resolution
banner, the sync job sideloads it into the media library with `media_sideload_image` and
sets it as the featured image. It runs once per project: if a featured image exists, it's
left alone forever, so Derek can replace it with something custom without the job
reverting him.

### Post type and taxonomies (`inc/projects/post-type.php`)

Moved verbatim from the site copy's `functions.php`. No behavior change.

### Tool icons (`inc/projects/tool-icons.php`)

The `tool_icon` term meta, its admin fields, the media uploader enqueue, and the
`render_block` filter that prepends icons to `project_tool` term links. Moved verbatim.
It's the largest single chunk (about 200 lines) and has nothing to do with the rest, which
is exactly why it gets its own file.

### Editor panel (`inc/projects/editor.php` + `assets/js/project-fields.js`)

Registers a `PluginDocumentSettingPanel` named "Project details" with three parts:

1. **Source** — the .org slug and GitHub URL, the two identifiers the sync job runs on.
2. **Synced values** — the resolved derived fields, shown read-only with a "last fetched"
   timestamp and a "Refresh now" button that calls the sync REST route for this post.
3. **Overrides** — the same fields as editable inputs, collapsed by default, each showing
   the synced value as its placeholder so it's obvious what you're overriding and what
   happens if you clear it.

Reads the field registry, passed from PHP via `wp_add_inline_script`. Loads only when
editing a `project`. Roughly 120 lines of JS, up from 80 because of the synced and override
split.

Depends on: the field registry and the sync REST route. Nothing depends on it.

### Archive template (`templates/archive-project.html`)

Core `query` with `post-template` using core's grid layout and `minimumColumnWidth: 320px`,
which produces the design's `auto-fit` / `minmax` behavior natively with no media queries.
Wide width 1000px, gap 2rem.

Card, per project: featured image at `aspect-ratio: 1544/500` wrapped in the permalink,
then a body holding the `project_type` eyebrow, an H2 title link, the excerpt, and a meta
line reading version and release date, both bound to meta.

The existing stretched-link CSS (`.tufte-project-card .wp-block-post-title a::after`)
carries over. The card layout changes from horizontal with a 48px icon to vertical with a
full-bleed banner, so the surrounding CSS is rewritten.

`patterns/project-card.php` is updated to match so the pattern and the template don't drift.

### Single template (`templates/single-project.html`)

Template holds the meta-bound and structural parts, in order: back link, hero (type eyebrow,
H1, tagline, up to three action buttons), featured figure with caption, `post-content`,
then the install-and-details row's details panel, then project nav.

Post content holds the prose that differs per project: intro paragraphs, the features grid,
the install steps, and the FAQ. These ship as insertable block patterns
(`project-features`, `project-install-steps`, `project-faq`) so each project is edited in the
block editor rather than in template markup.

The details panel is composed from core blocks in a two-column CSS grid, with static label
paragraphs beside meta-bound value paragraphs. A semantic `<dl>` would need a custom
dynamic block, and a dynamic block needs JS registration to preview in the Site Editor,
which reintroduces the build step for one small table. Not worth it.

The FAQ uses the theme's existing `core/details` pattern including the `+` to `x` toggle.

### Styles (`assets/css/patterns.css`)

The `PROJECT CARD` section is extended into a full project section covering the card grid,
hero, buttons, features grid, install steps, details panel, and project nav. Every value
comes from a theme.json token. No new color work: the handoff's palette is the theme's
existing dark and light variations.

## What's deferred

The interactive demo section. The handoff specifies a stateful control panel (five icon
styles, four sizes, two toggles) that live-updates a preview and a block-markup `<pre>`.
Done properly it's a block with an Interactivity API store and the real SVG paths from the
plugin's `src/`, and it appears on exactly one project.

For now, `project_demo_url` is left empty on both projects, and the empty-binding filter
means the Demo button simply doesn't render. No placeholder markup, nothing to clean up
later. The demo gets its own spec when we come back to it.

## Risks and blockers

**Featured images.** `featured_media` is 0 on both 5284 and 5285. Scroll Indicator solves
itself: the .org API exposes `banner-1544x500.png`, exactly the aspect the design calls for,
and the sync job sideloads it. Tufte Blocks has no .org listing and no banner, so it needs a
manual upload or the archive card renders without an image.

**Remote data is a live dependency.** Version numbers and links now come from api.wordpress.org
and api.github.com. Both are unauthenticated and rate-limited (GitHub at 60 requests/hour per
IP), which is ample for a twice-daily job over a handful of projects but would bite if sync
were ever moved into the render path. It must not be.

**Header parsing is fragile by nature.** Reading `Version:` out of a `style.css` header
depends on the file staying in the standard WordPress format. It's a safe assumption for
Derek's own repos and it's tested directly, but a malformed header yields an empty string
rather than a wrong value, which is the correct failure mode.

**The deploy mirrors everything.** The lftp exclude list covers `.git`, `.github`,
`node_modules`, editor folders, and `README.md`, but not `docs/` or `tests/`. This spec and
the tests would both be uploaded to the live server. Add `docs`, `docs/*`, `tests`, and
`tests/*` to the exclude globs.

**Two clones of one repo.** After this, `Studio/derekhansonblog/.../tufte-blocks` and
`Studio/tufte-blocks/.../tufte-blocks` are both checkouts of `dhanson-wp/tufte-blocks`. The
site copy is where the work happens. The other one needs a `git pull` before it's trusted
for anything.

## Testing

The theme has no test runner, and standing up the WordPress PHPUnit suite for a plain block
theme is disproportionate. But the sync layer added real logic, so three units get real
assertions in a committed `tests/` directory, run with `wp eval-file` against the Studio
site and printing a pass or fail line per case:

- **Header parser** — a well-formed `style.css` header, one with Windows line endings, one
  missing the fields entirely, and one that isn't a header at all. Missing fields yield empty
  strings, never wrong values.
- **Adapter mapping** — fixture API responses in, correct field arrays out, for both .org and
  GitHub. Uses saved JSON fixtures, so the tests never hit the network.
- **Field resolver** — precedence order (override beats .org beats GitHub beats empty), empty
  handling, unregistered keys, and blocks with no bindings passing through untouched.

Everything else is verified by looking at it:

- `git status` clean against `main` after the reconciliation, with the site still rendering
  at its Studio URL
- A real sync run against both live projects produces the expected values, and Scroll
  Indicator ends up with a featured image
- A sync run with the network unavailable leaves the previous cached values intact
- Archive and single render correctly at desktop and at 400px, in both the dark and light
  style variations
- Buttons and links absent when a field resolves empty, present and correct when filled
- Block editor shows no validation errors on either project, and the Site Editor opens both
  templates without a Block Recovery prompt
- `prefers-reduced-motion` collapses the hover transitions

## Milestones

1. Reconcile the two copies, commit the existing project work to a branch
2. Split project code out of `functions.php` into `inc/projects/`
3. Field registry, manual meta keys, and the override keys
4. Source adapters and the shared header parser, with tests
5. Sync job, REST route, WP-CLI command, and banner sideload
6. Binding source, field resolver, and empty-value filter, with tests
7. Editor sidebar panel
8. Archive template and CSS
9. Single template, patterns, and CSS
10. Content pass, version bump to 1.5.0, PR, deploy

Milestones 1 and 2 are refactors with no visible change and should land first and
separately, so any regression is obvious before new work starts. Milestones 4 through 6 are
pure PHP with no UI and can be verified entirely from the command line before any template
work begins.
