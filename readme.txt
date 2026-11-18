=== Tufte Blocks ===
Contributors: dhanson
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

A typography-first block theme inspired by Edward Tufte's design principles.

= Post formats =

The theme supports all nine core post formats and styles them with core's own classes, so they look right with no plugin, with Tufte Formats, or with Post Formats for Block Themes. Set the format in the editor's post settings.

* Status and aside: no visible title. The date, linked to the post, opens the entry, and the text reads at body size.
* Quote: the first Quote block gets the epigraph treatment.
* Link: the first link in the first paragraph reads as the headline.
* Image and gallery: the first image or gallery runs the full content width. On wide screens its caption moves into the right margin. The margin note works on the theme's own single template, which it keys on through the hero image wrapper; other layouts show the caption below the image.
* Audio and video: the first player or embed runs the full content width.
* Chat: start each line with the speaker's name in bold, followed by a colon, as in <strong>Name:</strong> Hello. The name shows in small capitals.

== Frequently Asked Questions ==

= I used Projects. Where did they go? =

Install the Tufte Projects plugin. Your projects, fields, and templates come back unchanged.

= Do post formats need a plugin? =

No. The theme supports and styles all nine core formats on its own. It also works with Tufte Formats and Post Formats for Block Themes if you'd rather manage formats with a plugin.

= Where do image margin notes appear? =

On wide screens, the caption of the first image or gallery in a post moves into the right margin on the theme's own single template. On narrower screens, and in other layouts, the caption stays below the image.

== Copyright ==

Tufte Blocks, Copyright 2026 Derek Hanson
Tufte Blocks is distributed under the terms of the GNU General Public License v2 or later.

== Resources ==

* ET Book typeface: MIT License, Copyright Dmitry Krasny, Bonnie Scranton, and Edward Tufte. License text in assets/fonts/et-book/LICENSE. https://github.com/edwardtufte/et-book
* Portrait placeholder (assets/images/placeholder-portrait.svg): a plain silhouette drawn for this theme, licensed under the theme's own GPLv2 or later.

== Privacy ==

Tufte Blocks doesn't collect, store, or send any user data. It sets no cookies and makes no outside requests.

== Changelog ==

= 2.0.1 =
Released 2026-11-17.

**Added**

* The ET Book license now ships with the font, in `assets/fonts/et-book/LICENSE`.

**Changed**

* The footer pattern shows your site's own tagline instead of a fixed line about the theme.

**Fixed**

* A link color you set on a block, such as Details or Group, now shows on its links. The theme's own link color used to override it.
* Links in the Light style variation use its darker gold, so they're readable against the light background. They used the dark palette's gold before.
* The LICENSE file now holds the GPLv2 text, matching the theme's GPLv2 or later license. It held the GPLv3 text before.

= 2.0.0 =
**Changed**

* **Breaking:** Projects moved to the Tufte Projects plugin. Install it to keep your projects, fields, and templates; the theme no longer registers them.
* Status posts now show in the blog loop like any other post. A plugin like Tufte Formats can keep them out.
* Every post format is supported and styled, with or without a plugin.
* The footer now loads from a pattern with a small theme credit link, and its year no longer uses a shortcode.
* Text in the theme's patterns is translatable.

**Removed**

* The prerender and Threads script tweaks are gone from the theme.

= 1.10.0 =
**Changed**

* Projects is moving to its own plugin, Tufte Projects. This release is the bridge: when the plugin is active, the theme steps aside and the plugin takes over, with no change to your projects, fields, or templates.

**Removed**

* Two leftover one-time routines that ran on every page load, the page template reset and the Additional CSS cleanup. A page template you save in the Site Editor now stays saved.

= 1.9.4 =
**Fixed**

* Theme styles no longer leak into the editor's sidebars and modals. Inline code in plugin panels, such as Jetpack Social's {title} and {url} placeholders, is readable again.

= 1.9.3 =
**Removed**

* The background job that requested pages every four minutes. The site no longer schedules any cron events of its own.

**Fixed**

* A long URL pasted into a note wraps onto the next line instead of pushing the page wider than a phone screen.

= 1.9.2 =
**Added**

* Pages on the live site stay in the host's page cache, so a first click on a page nobody visited recently no longer waits on a full render. A background job requests the main pages every four minutes; add `define( 'TUFTE_BLOCKS_CACHE_WARM', false );` to `wp-config.php` to turn it off.

= 1.9.1 =
**Changed**

* Pages load faster when you click through the site. Internal links now prerender after a short hover, the main body font is preloaded, and the Threads embed script no longer blocks the page.

= 1.9.0 =
**Added**

* A query block shows notes when its Post format filter is set to Status.

**Changed**

* Notes are posts in the Status format. They stay out of the blog loop, archives, search, and the main feed, and have their own list at /type/status/ with its own feed.
* Notes hide their generated titles.

= 1.8.1 =
**Fixed**

* Microformats now work on the front page and other static pages with post lists, and on the notes list. Each post and note in a list is an h-entry with its name, link, and date.

= 1.8.0 =
**Added**

* Posts carry microformats2 markup (h-entry and h-feed), so sites that receive your webmentions can show your name, date, and content. Nothing changes on screen.
* Filter `tufte_blocks_author_url` to change where the author card points.

= 1.7.3 =
**Fixed**

* The Fediverse follow card fits phone screens, with the full name and handle showing and the button underneath.

= 1.7.2 =
**Fixed**

* The Fediverse follow dialog styles now win over the plugin's, so the dark theme colors apply.

= 1.7.1 =
**Fixed**

* The Fediverse follow dialog is readable on the dark theme, with light text and a gold button.

= 1.7.0 =
**Changed**

* Webmentions and pingbacks in your comments now link to the post that mentioned you, with a "Read on" button in place of Reply.

**Fixed**

* Reply no longer sends readers off to the other site on a mention.

= 1.6.0 =
**Added**

* Project pages show a "Built with" row in Details, listing each tool with its icon.
* The bundled tool icons (Antigravity, Claude, Cursor, Gemini, and Telex) are registered as a "Tufte Blocks" icon collection, so they show up in the core Icon block on WordPress 7.1 and later.
* A tool with no uploaded icon falls back to the bundled icon that matches its slug.

= 1.5.2 =
**Changed**

* The Webmention form under your comments now matches the comment form, with the same field, fonts, and gold button.

= 1.5.1 =
**Changed**

* Inline code now reads as a small monospace chip on the surface color, on the front end and in the editor.
* The features pattern opens on its heading without the "What it does" label above it.
* Project patterns spell "color" the American way.

**Fixed**

* Project banners no longer show a caption under the featured image.

= 1.5.0 =
**Added**

* The Projects post type with synced GitHub and WordPress.org details, archive and single templates, and patterns for features, install steps, and FAQs.

= 1.3.0 =
Released 2026-02-27.

**Added**

* A "Tufte Hero" style for the Featured Image block that sets the image in the right margin beside the post title and metadata, the classic Tufte margin figure. It shows in the margin at 1200px and wider, and stacks below the title on narrower screens.

**Changed**

* The single post template uses a left-aligned content column, with a wide hero group around the title, metadata, and featured image.

= 1.2.0 =
Released 2026-02-27.

**Changed**

* Links inside query loops (post titles, dates, categories, and excerpts) change color on hover instead of drawing an animated underline. The "Continue reading" link keeps its underline, and links in regular paragraphs are unchanged.
* Post title links change color only, on the front end and in the editor.

= 1.1.0 =
Released 2026-02-27.

**Added**

* Homepage pattern styles: post cards lift with a gold border on hover, and note rows line up behind a fixed-width date column. The whole card or row is clickable.

**Fixed**

* Wide-aligned blocks display at their full width in the block editor.

= 1.0.1 =
Released 2026-02-21.

**Fixed**

* The header and footer show on single post and page templates again when the theme is installed from the release zip or under a different folder name.

= 1.0.0 =
Released 2026-02-20.

**Added**

* First release of Tufte Blocks, a typography-first block theme inspired by Edward Tufte's design principles, set in ET Book with an optimal line length and generous margins.
* A dark palette with gold accents by default, plus a light style variation you can switch to in Appearance › Editor › Styles.
* Block patterns for the header, footer, about card, reading list, newsletter call to action, syllabus, testimonial, stats row, and more.
* Full site editing, so you can change templates and styles in the Site Editor.

== Upgrade Notice ==

= 2.0.0 =
Projects moved to the Tufte Projects plugin; install it before updating if you use Projects.
