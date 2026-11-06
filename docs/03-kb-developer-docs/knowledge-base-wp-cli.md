---
slug: knowledge-base-wp-cli
title: "Knowledge Base WP-CLI commands"
products: [knowledgebase]
sections: ["03-kb-developer-docs"]
tags: [developer, github, knowledgebase, pro, wp-cli]
status: publish
order: 0
toc: true
---

[toc]

[Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) provides WP-CLI commands for search, status, cache, settings, ratings, Ask the Docs, and GitHub documentation sync. Install WP-CLI and configure your repositories in the GitHub settings before running the commands.

## Search and status

```bash
wp wzkb status --format=json
wp wzkb search "getting started" --limit=10
wp wzkb rating stats 123 --format=json
```

Status shows the plugin version, site, article counts, section count, and product count. Search returns published articles without passwords; `--limit` accepts 1 to 100. Rating statistics include total votes, sum, positive votes, and average for the requested article.

## Cache and settings

```bash
wp wzkb cache clear --dry-run
wp wzkb cache clear --yes
wp wzkb settings get kb_slug
wp wzkb settings export > settings.json
```

Cache clearing also runs the cache-cleared action used by Knowledge Base. Settings export returns saved settings as JSON and strips tokens and other registered sensitive fields. Settings get returns an effective non-sensitive value as JSON. Sensitive and unknown keys are refused. These commands do not modify settings.

## Ask the Docs *(Pro only)*

```bash
wp wzkb ai status --format=json
wp wzkb ai ask "How do I start?"
wp wzkb ai gaps-list --limit=50 --page=1 --format=json
wp wzkb ai cache-clear --dry-run
wp wzkb ai gaps-clear --dry-run
```

Ask uses the configured provider, cache, and request limits and can incur provider charges. The result is JSON. On the command line, requests without a visitor IP share the normal unknown-visitor rate limit. The gaps list groups unanswered questions from the last 30 calendar days in the site time zone.

`cache-clear` clears cached answers and provider status. `gaps-clear` permanently deletes all recorded questions, including answered questions. Each prompts for confirmation; pass `--yes` to run unattended. Both support `--dry-run`.

## GitHub commands *(Pro only)*

## Choose a repository mapping

Show enabled mappings and their indexes:

```bash
wp wzkb github status
```

Select a mapping with `--mapping=0`, all enabled mappings with `--mapping=all` or `--all`, or a repository with `--repo=owner/name`. Use only one selector. A repository used by multiple mappings must be selected by index. Mapping indexes can change when you reorder settings rows.

Status and diff default to all enabled mappings. Import and export require an explicit selector. The commands use the global GitHub token unless a mapping has its own token. Use the global WP-CLI `--url=<site>` option to select a multisite site.

## Import articles

Preview or import all enabled mappings:

```bash
wp wzkb github import --mapping=all --dry-run
wp wzkb github import --mapping=all --verbose
```

The import uses each mapping's branch, folder, product, status, and duplicate settings. `--verbose` shows each file as it is processed in table mode. The final table includes the action, article ID, notes, warnings, and errors.

Import one file, or process it again even if its saved SHA matches:

```bash
wp wzkb github import --mapping=0 --file=docs/start.md
wp wzkb github import --mapping=0 --file=docs/start.md --force
```

`--force` bypasses the unchanged SHA check. It does not bypass source locks or duplicate-handling settings. Files absent from the remote tree are not automatically deleted by this command. An excluded file can draft or permanently delete its linked article according to the mapping.

A dry run predicts actions without saving articles or terms, sideloading media, or changing GitHub. Hooks and concurrent changes can affect the actual result. Use `--user=<admin>` to assign an author and retain HTML allowed by that WordPress user. Without a user, WordPress applies its normal content filtering and uses author ID 0.

## Check differences

```bash
wp wzkb github diff --mapping=0 --format=json
```

`remote_changed` compares the remote file with the saved source SHA. `export_differs` compares it with Markdown generated from the current article. Formatting can change during a Markdown round trip. `not_imported` includes files that have no linked article, including intentionally excluded files.

## Export articles

Enable **Push-back** and provide a token with write access for each selected mapping. Preview generated Markdown or export:

```bash
wp wzkb github export --mapping=0 --post=123 --dry-run --format=json
wp wzkb github export --mapping=0 --yes
```

`--post=<id>` limits the export to a linked article. CLI export checks the saved source SHA against GitHub and reports a conflict when the remote file is missing or changed. Import and review the file before trying again. If the generated and remote Markdown match but the saved baseline is outdated, import to reconcile the baseline.

Exports use non-force branch updates. If an export phase fails, use its job ID to retry:

```bash
wp wzkb github export --job=<job_id> --yes --format=json
```

Do not combine `--job` with a selector, `--post`, or `--dry-run`. Jobs are eligible for cleanup after one hour of inactivity. If GitHub updated the branch but local sync state changed concurrently, the command reports an error and preserves the newer local state.

## Output and confirmations

GitHub status, diff, import, and export support `--format=table`, `json`, `csv`, and `yaml`. Table is the default. For exact exported Markdown, use JSON or YAML. WP-CLI prefixes CSV cells beginning with a hyphen with an apostrophe for spreadsheet safety.

Import and export ask for confirmation. Use `--yes` for unattended runs. Declining exits successfully without writing. A command exits with code `1` for invalid input or any failed operation; successful rows remain visible when other rows fail. A diff by itself is not an error.

Use `wp help wzkb github` or `wp help wzkb github import` to view command help.
