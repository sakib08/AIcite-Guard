=== AIcite Guard ===
Contributors: sakibbd08
Tags: geo, llms.txt, accessibility, wcag, site health
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight AI search visibility (GEO), real accessibility (EAA / WCAG), and site health. Works alongside Yoast and Rank Math.

== Description ==

AIcite Guard is a small companion plugin for sites that already use Yoast or Rank Math. It does not replace those plugins. It covers three gaps they do not fully solve:

* **AI search visibility (GEO)** — generate `llms.txt` and `llms-full.txt`, control AI crawlers, and see a plain-English AI Readiness Score.
* **Real accessibility** — scan for missing alt text, weak headings, and unlabeled fields; approve fixes; offer a visitor widget; generate an accessibility statement.
* **Site health** — find unused or heavy plugins, common conflicts, Core Web Vitals impact, and a simple security surface.

The free version is meant to stay installed. Core tools work without a paid AI. An optional OpenAI key improves alt text, with a monthly generation cap.

= AI Visibility =

* Auto-generate and preview `llms.txt` and `llms-full.txt` from published content
* Choose post types and regenerate in one click
* Allow answer engines and block known training scrapers via `robots.txt`
* JSON-LD structured data (WebSite, Organization, Article/WebPage, FAQ) only when Yoast/Rank Math are not active
* AI Readiness Score (0–100) with clear next steps

= Accessibility =

* Image alt scan (missing and filename-like text)
* Rule-based suggestions; optional AI if you add your own key
* Per-page and site-wide scores
* Heading, form label, iframe, and vague-link warnings
* Visitor widget (text size, contrast, links, spacing, reduce motion)
* Accessibility statement page generator

Nothing is written to the media library until you approve it.

= Site Health Guardian =

* Unused / inactive plugin list with folder size
* Common SEO, cache, security, and optimizer overlap
* Local Core Web Vitals impact (plugin count, autoload size, cache, HTTPS)
* Outdated and possibly abandoned plugin reminders
* Overall health score on the dashboard

= Designed to stay light =

* No page-builder lock-in (Gutenberg, Elementor, Bricks, and others)
* Front end loads a tiny widget only when you enable it
* Admin assets load only on AIcite Guard screens
* Native WordPress APIs — no Composer stack in the free plugin

== Installation ==

1. Upload the `aicite-guard` folder to `/wp-content/plugins/`
2. Activate AIcite Guard
3. Complete the setup wizard
4. Visit **AIcite Guard → AI Visibility** and generate your files
5. If pretty permalinks are off, flush permalinks once under Settings → Permalinks

== Frequently Asked Questions ==

= Does this replace Yoast or Rank Math? =

No. Keep one SEO plugin for titles, schema, and sitemaps. AIcite Guard adds AI files, accessibility, and health checks.

= Do I need an API key? =

No. `llms.txt`, crawler rules, scores, and rule-based alt text work without one.

= Will the visitor widget make my site accessible? =

No overlay can replace real content fixes. The widget is a helper. Use the scan and approval queue for actual issues.

= Can I use this with Elementor or Bricks? =

Yes. Builder pages with little stored HTML are noted in the scan so you are not given a false perfect score.

== Changelog ==

= 1.0.0 =
* Initial free release: AI visibility, accessibility core, site health, setup wizard.

== Upgrade Notice ==

= 1.0.0 =
First public version of AIcite Guard.
