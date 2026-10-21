=== Tufte Blocks ===
Contributors: dhanson
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.10.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A typography-first block theme inspired by Edward Tufte's design principles.

== Post formats ==

The theme supports all nine core post formats and styles them with core's own classes, so they look right with no plugin, with Tufte Formats, or with Post Formats for Block Themes. Set the format in the editor's post settings.

* Status and aside: no visible title. The date, linked to the post, opens the entry, and the text reads at body size.
* Quote: the first Quote block gets the epigraph treatment.
* Link: the first link in the first paragraph reads as the headline.
* Image and gallery: the first image or gallery runs the full content width. On wide screens its caption moves into the right margin. The margin note works on the theme's own single template, which it keys on through the hero image wrapper; other layouts show the caption below the image.
* Audio and video: the first player or embed runs the full content width.
* Chat: start each line with the speaker's name in bold, followed by a colon, as in <strong>Name:</strong> Hello. The name shows in small capitals.

== Copyright ==

Tufte Blocks, Copyright 2026 Derek Hanson
Tufte Blocks is distributed under the terms of the GNU General Public License v2 or later.

== Resources ==

* ET Book typeface: MIT License, Copyright Dmitry Krasny, Bonnie Scranton, and Edward Tufte. https://github.com/edwardtufte/et-book
* Portrait placeholder (assets/images/placeholder-portrait.svg): a plain silhouette drawn for this theme, licensed under the theme's own GPLv2 or later.

== Upgrade Notice ==

= 2.0.0 =
Projects moved to the Tufte Projects plugin; install it before updating if you use Projects.

== Changelog ==

= 1.10.0 =
* Projects is moving to its own plugin, Tufte Projects. This release is the bridge: when the plugin is active, the theme steps aside and the plugin takes over, with no change to your projects, fields, or templates.
* Removed two leftover one-time routines that ran on every page load, the page template reset and the Additional CSS cleanup. A page template you save in the Site Editor now stays saved.

= 1.9.4 =
* Theme styles no longer leak into the editor's sidebars and modals. Inline code in plugin panels, such as Jetpack Social's {title} and {url} placeholders, is readable again.

= 1.9.3 =
* Removed the background job that requested pages every four minutes. The site no longer schedules any cron events of its own.
* A long URL pasted into a note wraps onto the next line instead of pushing the page wider than a phone screen.

= 1.9.2 =
* Pages on the live site stay in the host's page cache, so a first click on a page nobody visited recently no longer waits on a full render. A background job requests the main pages every four minutes; add define( 'TUFTE_BLOCKS_CACHE_WARM', false ); to wp-config.php to turn it off.

= 1.9.1 =
* Pages load faster when you click through the site. Internal links now prerender after a short hover, the main body font is preloaded, and the Threads embed script no longer blocks the page.

= 1.9.0 =
* Notes are posts in the Status format. They stay out of the blog loop, archives, search, and the main feed, and have their own list at /type/status/ with its own feed.
* A query block shows notes when its Post format filter is set to Status.
* Notes hide their generated titles.

= 1.8.1 =
* Microformats now work on the front page and other static pages with post lists, and on the notes list. Each post and note in a list is an h-entry with its name, link, and date.

= 1.8.0 =
* Posts carry microformats2 markup (h-entry and h-feed), so sites that receive your webmentions can show your name, date, and content. Nothing changes on screen.
* Filter tufte_blocks_author_url to change where the author card points.

= 1.7.3 =
* The Fediverse follow card fits phone screens, with the full name and handle showing and the button underneath.

= 1.7.2 =
* The Fediverse follow dialog styles now win over the plugin's, so the dark theme colors apply.

= 1.7.1 =
* The Fediverse follow dialog is readable on the dark theme, with light text and a gold button.

= 1.7.0 =
* Webmentions and pingbacks in your comments now link to the post that mentioned you, with a "Read on" button in place of Reply.
* Reply no longer sends readers off to the other site on a mention.

= 1.6.0 =
* Project pages show a "Built with" row in Details, listing each tool with its icon.
* The bundled tool icons (Antigravity, Claude, Cursor, Gemini, and Telex) are registered as a "Tufte Blocks" icon collection, so they show up in the core Icon block on WordPress 7.1 and later.
* A tool with no uploaded icon falls back to the bundled icon that matches its slug.

= 1.5.2 =
* The Webmention form under your comments now matches the comment form, with the same field, fonts, and gold button.

= 1.5.1 =
* Inline code now reads as a small monospace chip on the surface color, on the front end and in the editor.
* Project banners no longer show a caption under the featured image.
* The features pattern opens on its heading without the "What it does" label above it.
* Project patterns spell "color" the American way.

= 1.5.0 =
* Added the Projects post type with synced GitHub and WordPress.org details, archive and single templates, and patterns for features, install steps, and FAQs.
