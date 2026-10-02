---
slug: syncing-docs-with-github
title: "Syncing Docs with GitHub"
products: [knowledgebase]
sections: ["02-kb-advanced"]
tags: [github, knowledgebase, pro, sync]
status: publish
order: 5
toc: true
---

If you keep your documentation as Markdown on GitHub, [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) can sync it with your knowledge base in both directions. It imports `.md` files as articles, and pushes article edits back to GitHub as commits.

[toc]

## How it works

The plugin fetches Markdown files from your repository, converts them to blocks, and creates or updates knowledge base articles. Each article remembers its source file, so later syncs only process files that have changed.

The integration works in both directions:

- **Import (GitHub → WordPress)** — run on demand from the import wizard, or automatically via a push webhook.
- **Export/push-back (WordPress → GitHub)** — push a single article from its editor, push automatically on save, or bulk-push a whole mapping from the export wizard.

Images can be copied into your Media Library, and relative `.md` links are rewritten to article permalinks. The Markdown converter also understands tables, fenced code blocks with language hints, and all five <a href="https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax#alerts" target="_blank" rel="noreferrer noopener">GitHub Alerts</a>:

```markdown
> [!NOTE]
> Useful information that users should know, even when skimming content.
```

The other alert types are `[!TIP]`, `[!IMPORTANT]`, `[!WARNING]` and `[!CAUTION]`.

## What you need before you start

- A GitHub repository containing your `.md` documentation files.
- Knowledge Base Pro is installed and activated.
- Administrator access to your WordPress site.

## Step 1 — Prepare your Markdown files

Each Markdown file becomes an article. Add a block of settings, called frontmatter, between `---` lines at the top of each file:

```yaml
---
title: "Getting Started"
sections: [Installation]
status: publish
---

Your article content starts here...
```

Every field is optional: without a title the filename is used, and without sections the article has no section. A file with no frontmatter at all is skipped. Add `kb_exclude: true` to skip a file that has frontmatter but isn't an article.

### Frontmatter field reference

| Field | Aliases | Type | What it does |
| --- | --- | --- | --- |
| `title` | — | string | Article title shown in the KB. Defaults to the filename stem. |
| `slug` | — | string | The URL slug. Defaults to the filename stem. |
| `sections` | `categories`, `category`, `section` | comma-separated list | `wzkb_category` terms. Supports path notation for hierarchy (see below). Missing terms are created automatically. |
| `tags` | `tag` | comma-separated list | `wzkb_tag` terms. Missing terms are created automatically. |
| `products` | `product` | comma-separated list | `wzkb_product` terms. Used only when the mapping has no configured product; a configured mapping product takes precedence. |
| `order` | `menu_order` | integer | Sort order within a section (`menu_order`). |
| `status` | — | string | `publish`, `draft`, `pending`, `private`, or `future`. Overridden by the mapping's **Article Status** setting when that is set. |
| `kb_exclude` | — | boolean | Skips the import for this file. When `true`, the linked article is set to draft, or permanently deleted when the mapping's **When a File is Deleted** setting is set to **Delete permanently**. |
| `toc` | — | boolean | Insert a table-of-contents block before the first heading when no `[[toc]]` marker is present in the body. |
| `featured_image` | `thumbnail`, `cover`, `image` | string | Sets the article's featured image from an absolute URL or a path relative to the Markdown file. Requires **Import external media**. |
| `id` | — | integer | Optional stable document ID. Stored as `_wzkb_github_doc_id`. |

`sections`, `tags`, and `products` take a comma-separated list wrapped in square brackets, e.g. `tags: [setup, beginner]`. A single value still uses the brackets: `tags: [setup]`.

### How terms and capitalization are handled

Each value is matched to an existing term by its slug, so `Installation` and `installation` both find the `installation` term and capitalization never creates duplicates. When a term doesn't exist yet, it is created with the text exactly as you typed it as its name. So `sections: [Getting Started]` creates a section named "Getting Started" with the slug `getting-started`.

### A full example

```yaml
---
slug: advanced-configuration
title: "Advanced Configuration"
products: [knowledgebase, my-plugin]
sections: [Installation/Advanced, Troubleshooting]
tags: [advanced, beginner, configuration]
status: publish
order: 5
toc: true
featured_image: images/advanced-config.png
---

Your article content starts here...
```

### Organizing into subsections

Use a forward slash to place an article in a child section. This files the article under **Installation → Windows** and also in a top-level **Troubleshooting** section, creating any that don't exist:

```yaml
sections: [Installation/Windows, Troubleshooting]
```

You can nest as deeply as you like.

### Featured images

`featured_image` (or `thumbnail`, `cover` or `image`) sets the article's featured image. Relative paths, including `./` and `../`, are resolved against the Markdown file; `https://` URLs are used as-is.

The image is copied into the Media Library, so **Import external media** must be on; otherwise the field is ignored. An image already in the Media Library is reused. Removing the field later clears a featured image the importer set, but not one you set in the editor.

### Table of contents markers and tables

Place a live TOC marker on its own line where the table of contents should appear. In your Markdown source, use one opening bracket, `toc`, and one closing bracket. The escaped form `[[toc]]` documents the marker without inserting a TOC.

From 3.1.5, imported TOC markers accept `heading_depth` and `min_headings` as well as the compact aliases `headingdepth` and `minheadings`. If both spellings are supplied, the compact alias takes precedence. The `title` attribute sets the heading above the TOC.

On export, ordinary tables become Markdown pipe tables. Tables with merged cells, captions, or block-level content inside cells remain HTML because pipe tables cannot represent that structure. The same conversion applies to Markdown ZIP exports.

## Step 2 — Create a GitHub Personal Access Token

The plugin needs permission to read your repository (and to write, if you enable push-back). Skip this step only if your repo is **public** and you do not need push-back.

1. On GitHub, click your profile picture → **Settings**.
2. Scroll to **Developer settings** → **Personal access tokens** → **Fine-grained tokens**.
3. Click **Generate new token**. (Direct link: <a href="https://github.com/settings/personal-access-tokens/new" target="_blank" rel="noreferrer noopener">github.com/settings/personal-access-tokens/new</a>.)
4. Give it a name such as "Knowledge Base Sync".
5. Set **Resource owner** to the account or organization that owns your repositories. (For organization repos, the org must also allow fine-grained tokens under **Organization Settings → Personal access tokens**.)
6. Under **Repository access**, choose **Only select repositories** and pick your docs repo.
7. Under **Permissions → Repository permissions**, set **Contents** to **Read-only** for import, or **Read and write** if you plan to push articles back to GitHub.
8. Click **Generate token** and copy it immediately — GitHub won't show it again.

Keep this token safe. You'll paste it into the plugin on the next step. Because fine-grained tokens are scoped per owner, you can set a **per-mapping** token later if different repositories belong to different owners.

## Step 3 — Connect your repository in plugin settings

All settings live under **Knowledge Base → Settings → GitHub**.

### Global settings

| Setting | Description |
| --- | --- |
| **GitHub Personal Access Token** | Default PAT used when a mapping does not specify its own. Stored encrypted. Use the **Verify Token** button to confirm it is valid. |
| **Webhook Secret** | HMAC secret used to validate incoming push webhook payloads. Stored encrypted. A string you choose and enter both here and on GitHub. See Step 5. |
| **Import external media** | When enabled, images in imported Markdown are sideloaded into the Media Library and their URLs rewritten to local copies. Already-sideloaded images are reused on re-import. |
| **Auto-push on save** | Pushes a linked article to GitHub every time it is saved, except autosaves, revisions and webhook imports. **Leave this off** unless you want a commit for every save. |

### Repository mappings

Each mapping configures one import/export source. Click **Add Repository** in the **Repository Mappings** section and fill in:

| Field | Description |
| --- | --- |
| **Product** | The Knowledge Base product (`wzkb_product` term) assigned to imported articles. A configured product overrides frontmatter products. Leave unset to use the frontmatter. |
| **Personal Access Token** | Per-mapping PAT. Overrides the global PAT for this repository — useful when repos belong to different owners. Leave blank to use the global token. |
| **Repository** | Begin typing to search repositories accessible with the configured token, then select `owner/repo-name` from the dropdown. |
| **Folder Path** | Subdirectory to restrict imports to, e.g. `docs/`. Leave blank to import the entire repo. The folder is scanned **recursively**, so all `.md` files in its subfolders are included too. |
| **Branch** | Branch, tag, or SHA to import from. Leave blank to use the repository default branch (wizard) or accept pushes to any branch (webhook). |
| **Article Status** | Force all imported articles to this status, overriding any `status` set in frontmatter. Leave on **Use frontmatter** to let each article control its own status. |
| **Slug Conflict Handling** | What to do when a GitHub file's slug matches an article that was not imported from GitHub: **Overwrite the existing article**, **Skip — leave the existing article unchanged**, or **Create a new article with a suffixed slug**. |
| **When a File is Deleted** | What to do with the linked article when its source file is removed from the repository: **Switch to draft** or **Delete permanently**. |
| **Push-back** | Enable pushing article changes back to GitHub (auto-push and the export wizard). Requires the PAT to have **contents: write**. |
| **Commit author name / email** | Optional. Attribute push commits to this identity. Leave blank to use the PAT owner's profile. |
| **Status** | Set to **Disabled** to pause a mapping entirely — no imports, no webhook processing, no push-back — without deleting it. |

Save your settings when done.

## Step 4 — Run your first import

Go to **Knowledge Base → GitHub** and select the **Import** tab. Select your mapping (or **— All Mappings —**), optionally override the branch/ref, optionally force re-import of files whose SHA has not changed, then click **Import**. The wizard fetches your files one by one and shows a live results table — created, updated, skipped, or any errors.

Later imports only process files that have changed. Only `.md` and `.markdown` files are imported, from the folder and all its subfolders; other files are ignored.

## Step 5 — Set up automatic sync (optional)

To import automatically on every push, add a webhook to your repository:

1. In your GitHub repository, go to **Settings → Webhooks → Add webhook**.
2. Set **Payload URL** to `https://example.com/wp-json/wzkb/v1/github/webhook` (using your own site's domain).
3. Set **Content type** to `application/json`.
4. Generate any random string, paste it into **Secret**, and paste the same string into the **Webhook Secret** field in Knowledge Base settings.
5. Under "**Which events would you like to trigger this webhook?**" Choose "**Just the push event**".
6. Click **Add webhook**.

Each push now imports the files it added or changed. Renamed files keep their article, and removed files are drafted or deleted according to **When a File is Deleted**. A mapping with a **Branch** only accepts pushes to that branch.

## Pushing changes back to GitHub

With **Push-back** on and a token with write access, you can send edits made in WordPress back to the source file. The article is converted back to Markdown with its frontmatter and committed with a message starting `docs: `. Unchanged articles are skipped.

- **From the editor:** linked articles have a **GitHub** box with **Push to GitHub** and **Pull from GitHub**. Pull re-imports the file from GitHub, replacing the article content.
- **On save:** with **Auto-push on save** on, each save is pushed in the background.
- **In bulk:** go to **Knowledge Base → GitHub → Export**, choose a mapping, click **List Articles** to see what changed, then click **Push to GitHub**. All changes go into one commit per branch, and an interrupted export resumes where it stopped.

## Working with images

With **Import external media** on, images in your Markdown are copied into the Media Library and the article points to the local copy:

```markdown
![Screenshot](../images/screenshot.png)
![Logo](https://example.com/assets/logo.png)
```

Relative paths are resolved against the Markdown file and fetched from GitHub. Absolute URLs are downloaded as-is, and images already on your site are left alone. An image is never uploaded twice, even when it appears on several branches.

## REST endpoints

| Endpoint | Purpose |
| --- | --- |
| `POST /wp-json/wzkb/v1/github/webhook` | Receives GitHub push events after validating the `X-Hub-Signature-256` header against the **Webhook Secret**. |
| `GET /wp-json/wzkb/v1/github/validate` | Returns the GitHub login for the configured token. The **Verify Token** button calls it. |

On large pushes, the webhook returns **HTTP 202** straight away and processes the files in the background with WP-Cron, 10 files per run by default. Files that fail because of a lock or a network error are retried up to 3 times before being logged as failed, so GitHub never times out waiting for a response.

## Developer reference

Each hook below links to its full reference on webberzone.dev, with parameters and examples.

### Import

| Hook | Type | Use |
| --- | --- | --- |
| [`wzkb_github_skip_file`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_skip_file/) | Filter | Return `true` to skip a file before it is fetched. |
| [`wzkb_github_pre_import`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_pre_import/) | Filter | Change the post data, or set `skip` to `true` to cancel the import. |
| [`wzkb_github_markdown_html`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_markdown_html/) | Filter | Change the HTML generated from Markdown before it becomes blocks. |
| [`wzkb_github_post_import`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_post_import/) | Action | Runs after an article is created or updated. |
| [`wzkb_github_escapable_shortcode_tags`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_escapable_shortcode_tags/) | Filter | Shortcode tags kept as literal text on import and export. Default: every registered tag plus `toc`. |
| [`wzkb_github_max_images_per_file`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_max_images_per_file/) | Filter | Most images sideloaded per file. Default `25`; the rest keep their original URLs. |
| [`wzkb_github_max_image_bytes`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_max_image_bytes/) | Filter | Largest image sideloaded. Default 10 MB; larger images keep their original URLs. |
| [`wzkb_github_webhook_chunk_size`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_webhook_chunk_size/) | Filter | Files processed per background run. Default `10`. |
| [`wzkb_github_api_args`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_api_args/) | Filter | Change the request arguments, such as the timeout, for every GitHub API call. |

Each HTML element type also has a pair of filters for changing how it is converted to a block, such as [`wzkb_github_import_paragraph_pre`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_import_paragraph_pre/) and [`wzkb_github_import_paragraph_post`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_import_paragraph_post/). The types are `blockquote`, `code`, `details`, `div`, `figure`, `heading`, `image`, `list`, `paragraph`, `separator`, `table` and `toc`.

### Push and export

| Hook | Type | Use |
| --- | --- | --- |
| [`wzkb_github_pre_push`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_pre_push/) | Filter | Change the push data, or set `skip` to `true` to cancel pushing a single article. |
| [`wzkb_github_post_push`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_post_push/) | Action | Runs after an article is pushed. Receives the GitHub API response for a single push, or the commit URL from the export wizard. |
| [`wzkb_github_export_batch_size`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_batch_size/) | Filter | Articles checked per export preview request. Default `50`. |
| [`wzkb_github_export_chunk_size`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_chunk_size/) | Filter | Export tasks processed per chunk. Default `10`. |
| [`wzkb_github_export_tree_payload_bytes`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_tree_payload_bytes/) | Filter | Largest Git tree request sent by the export wizard. Default 5 MB. |
| [`wzkb_github_export_fallback_blob_bytes`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_fallback_blob_bytes/) | Filter | Largest file uploaded through the blob fallback. Default 20 MB. |

Each block type has a pair of export filters too, such as [`wzkb_github_export_paragraph_pre`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_paragraph_pre/) and [`wzkb_github_export_paragraph_post`](https://webberzone.dev/knowledgebase/hooks/wzkb_github_export_paragraph_post/). The types are `alerts`, `code`, `heading`, `html`, `image`, `list`, `paragraph`, `quote`, `separator`, `table` and `toc`.

### Post meta

| Meta key | Description |
| --- | --- |
| `_wzkb_github_repo` | Source repository, as `owner/repo`. |
| `_wzkb_github_path` | Path of the file in the repository, such as `docs/setup.md`. |
| `_wzkb_github_sha` | Git blob SHA at the last import or push, used to detect changes. |
| `_wzkb_github_last_sync` | Time of the last successful import or push. |
| `_wzkb_github_source_url` | GitHub URL of the source file. |
| `_wzkb_github_doc_id` | The `id` frontmatter value, if present. |
| `_wzkb_github_last_push_commit` | URL of the latest push commit. |

Sideloaded images carry `_wzkb_github_media_source` so they are never uploaded twice.

### Classes

The sync engine lives in the `WebberZone\Knowledge_Base\Pro\GitHub` namespace. [`Import_Processor`](https://webberzone.dev/knowledgebase/classes/WebberZone-Knowledge_Base-Pro-GitHub-Import_Processor/) can be extended. Override its protected `find_github_post()` to change how existing articles are matched, for example by `_wzkb_github_doc_id`. Call `build_post_map()` once before a batch to share one database query across files. See also [`Content_Importer`](https://webberzone.dev/knowledgebase/classes/WebberZone-Knowledge_Base-Pro-GitHub-Content_Importer/), [`Content_Exporter`](https://webberzone.dev/knowledgebase/classes/WebberZone-Knowledge_Base-Pro-GitHub-Content_Exporter/) and [`Webhook_Handler`](https://webberzone.dev/knowledgebase/classes/WebberZone-Knowledge_Base-Pro-GitHub-Webhook_Handler/).

## Troubleshooting

- **The article looks wrong.** Check that the frontmatter opens and closes with `---` on its own line, with no extra spaces.
- **A duplicate section was created.** Sections that don't exist are created, so a typo in `sections` creates a new one. Use the existing section's slug.
- **An image doesn't appear.** Turn on **Import external media** and check the image path relative to the Markdown file.
- **The push button is missing, or a push fails.** The article must be linked to GitHub, the mapping must be enabled with **Push-back** on, and the token needs **Contents: Read and write**. Use **Verify Token** to check it.
- **Pull returns a "locked" error.** Another pull or a webhook import is running for that article. Wait a few seconds and try again.
- **You want to pause a mapping.** Set its **Status** to **Disabled**.

For anything else, [open a support ticket](https://webberzone.com/request-support/).
