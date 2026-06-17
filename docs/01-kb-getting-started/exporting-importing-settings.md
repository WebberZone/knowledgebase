---
slug: exporting-importing-settings
title: "Exporting and Importing Settings"
products: [knowledgebase]
sections: [01-kb-getting-started]
tags: [knowledgebase,settings,export,import]
status: publish
order: 0
---

The Knowledge Base plugin lets you back up all plugin settings as a JSON file and restore them on the same site or a different one. Both actions are available under **Knowledge Base → Tools**.

## Exporting settings

Click **Export Settings** to download a `.json` file named `{site}-settings-{date}.json`. The file contains every setting stored under the `wzkb_settings` option.

Sensitive values are automatically stripped before the file is created and are never written to disk. This includes:

- API keys and tokens (for example, the GitHub PAT)
- Webhook secrets
- Any other field registered with type `sensitive` in the plugin's settings

For repeater fields such as GitHub repository mappings, sensitive subfields (PAT tokens, webhook secrets) are blanked out in the export while the rest of each row — owner, repository name, branch, and so on — is preserved.

## Importing settings

To restore settings, click **Choose File**, select a `.json` file exported by this plugin, then click **Import Settings**.

The importer merges the file into the current settings: values from the file overwrite non-sensitive settings, while sensitive values are handled separately — the importer re-reads them from the existing database and writes them back, so they are never touched by the imported data. This means API keys and webhook secrets are preserved whether you are importing to the same site or a different one.

For repeater rows (such as GitHub repository mappings), the importer matches rows by their `row_id`. Sensitive subfields in matched rows are restored from the site's existing data. For any new rows introduced by the import, sensitive subfields are left blank and must be filled in manually after the import.

> **Note:** On a fresh site with no existing settings, sensitive values will be blank after import because there is nothing to restore. You will need to enter API keys and webhook secrets manually.

## Who can export or import

Both actions require the `manage_options` capability (WordPress Administrator by default).
