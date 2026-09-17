=== SitePulse Guard by PPros ===
Contributors: sakibbd08
Tags: geo, accessibility, wcag, site-health, llms-txt
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Companion for Yoast or Rank Math: AI search visibility, approve-before-write accessibility, and plugin-bloat health checks in one small plugin.

== Description ==

SitePulse Guard by PPros is a **companion**, not another SEO plugin and not another llms.txt-only generator.

It is built for sites that already use Yoast or Rank Math and still have three gaps those plugins do not fully cover:

* **AI search visibility (GEO)** — publish `llms.txt` / `llms-full.txt` from real content, allow answer engines while blocking known training scrapers, and see a plain-English AI Readiness Score.
* **Real accessibility (EAA / WCAG)** — scan for missing alt text, weak headings, unlabeled fields, and vague links. Suggested alt text is **never** written until you approve it. A visitor widget is a helper, not a substitute for fixes.
* **Site health** — unused or heavy plugins, common SEO/cache/security overlap, local Core Web Vitals impact, and a simple security surface.

= What makes SitePulse Guard by PPros different =

Most directory plugins in this area do one job (usually `llms.txt`). SitePulse Guard by PPros is the small toolkit that sits **beside** your SEO plugin:

* Schema is deferred when Yoast or Rank Math is already outputting JSON-LD, so you do not get duplicate markup.
* Accessibility changes are approve-before-write. Nothing is silently patched in the media library.
* Core features run locally. No AI provider is required to generate files, scores, crawler rules, or rule-based alt text.
* Optional AI-assisted alt text uses the **WordPress AI Client** (WordPress 7.0+) and the provider the site owner already configured under Settings → Connectors. SitePulse Guard by PPros does not store vendor API keys and does not call a provider HTTP API directly.

= AI Visibility =

* Auto-generate and preview `llms.txt` and `llms-full.txt` from published content
* Choose post types and regenerate in one click
* Allow answer engines and block known training scrapers via `robots.txt`
* JSON-LD structured data (WebSite, Organization, Article/WebPage, FAQ) only when Yoast/Rank Math are not active
* AI Readiness Score (0–100) with clear next steps

= Accessibility =

* Image alt scan (missing and filename-like text)
* Rule-based suggestions; optional AI via the WordPress AI Client when a site-wide provider is configured
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
* Admin assets load only on SitePulse Guard by PPros screens
* Native WordPress APIs — no Composer stack in the free plugin

== External services ==

Core features (llms.txt generation, robots.txt rules, scores, rule-based alt text, site health) run on your WordPress site. They do not send content to a third-party API.

= WordPress AI Client (optional) =

Optional AI-assisted alt text is available on WordPress 7.0 or later when the site owner has configured an AI provider under **Settings → Connectors**. SitePulse Guard by PPros uses the core WordPress AI Client instead of calling a vendor directly.

* **What it is used for:** improving suggested image alt text when an administrator clicks generate.
* **What data is sent, and when:** the generation prompt, a short image context (title/filename), and — when the model supports it — the public image URL. Data is sent only after an administrator action, never on ordinary front-end page views.
* **Where it goes:** whichever provider the site owner already connected (for example the official OpenAI, Anthropic, or Google provider plugins). SitePulse Guard by PPros does not collect or store those API keys.

If the site owner connects one of the official provider plugins, that vendor’s terms apply:

* OpenAI — [Terms of use](https://openai.com/policies/terms-of-use/) and [Privacy policy](https://openai.com/policies/privacy-policy/)
* Anthropic — [Consumer terms](https://www.anthropic.com/legal/consumer-terms) and [Privacy policy](https://www.anthropic.com/legal/privacy)
* Google Gemini — [API terms](https://ai.google.dev/gemini-api/terms) and [Privacy policy](https://policies.google.com/privacy)

If no provider is configured, or you turn the option off in SitePulse Guard by PPros settings, no prompt data leaves the site.

== Installation ==

1. Upload the `spg-by-ppros` folder to `/wp-content/plugins/`
2. Activate SitePulse Guard by PPros
3. Complete the setup wizard
4. Visit **SitePulse Guard by PPros → AI Visibility** and generate your files
5. If pretty permalinks are off, flush permalinks once under Settings → Permalinks
6. (Optional, WordPress 7.0+) Configure an AI provider under Settings → Connectors to improve alt-text suggestions

== Frequently Asked Questions ==

= Does this replace Yoast or Rank Math? =

No. Keep one SEO plugin for titles, schema, and sitemaps. SitePulse Guard by PPros adds AI files, accessibility, and health checks, and it skips JSON-LD when those plugins already handle it.

= Do I need an API key? =

No. `llms.txt`, crawler rules, scores, and rule-based alt text work without one. SitePulse Guard by PPros never asks you to paste a vendor key. Optional AI alt text uses the WordPress AI Client and a provider configured once for the whole site.

= How is this different from other llms.txt plugins? =

SitePulse Guard by PPros is not an llms.txt-only plugin. It combines AI visibility with approve-before-write accessibility and a site-health pass, and it is written as a companion to Yoast/Rank Math rather than a replacement.

= Will the visitor widget make my site accessible? =

No overlay can replace real content fixes. The widget is a helper. Use the scan and approval queue for actual issues.

= Can I use this with Elementor or Bricks? =

Yes. Builder pages with little stored HTML are noted in the scan so you are not given a false perfect score.

== Changelog ==

= 1.0.2 =
* Plugin name is now SitePulse Guard by PPros so the WordPress.org-restricted term "plugin" is not in the product name or slug.
* Directory slug is now `spg-by-ppros`.
* Translations load automatically on WordPress.org; `load_plugin_textdomain()` was removed.

= 1.0.1 =
* Renamed the plugin to SitePulse Guard by PPros.
* Optional AI alt text now uses the WordPress AI Client instead of a direct OpenAI integration.
* Documented optional third-party AI providers in this readme.

= 1.0.0 =
* Initial free release: AI visibility, accessibility core, site health, setup wizard.

== Upgrade Notice ==

= 1.0.2 =
Directory slug is now spg-by-ppros. Reactivate the plugin after updating if WordPress lists it as inactive.

= 1.0.1 =
Optional AI features now use the WordPress AI Client.
