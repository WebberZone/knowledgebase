---
slug: ask-the-docs
title: "Ask the Docs: AI answers from your knowledge base"
products: [knowledgebase]
sections: ["01-kb-getting-started"]
tags: [ai, knowledgebase, pro, search]
status: publish
order: 0
toc: true
---

[toc]

**Ask the Docs** in [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/) answers a visitor's plain-language question from your published articles, with links to the articles it used. When your articles don't cover the question, it says so, points to the search results and, optionally, logs the question so you can see where your documentation falls short. Ask the Docs is in beta.

## Requirements

- Knowledge Base Pro 3.2.0 or later.
- WordPress 7.0 or later. Ask the Docs uses the AI Client built into WordPress 7.0. On older versions the feature does not load and the rest of the plugin works as before.
- An AI provider connected under **Settings → Connectors**, such as the official Anthropic, OpenAI or Google provider plugins. It must support text generation with structured JSON output.

Knowledge Base Pro never sees or stores API keys. WordPress sends each request through the provider you have connected, which bills you per request.

## Set up Ask the Docs

1. Connect a provider under **Settings → Connectors**.
2. Go to **Knowledge Base → Settings → AI (Beta)** and turn on **Enable Ask the Docs**. If the tab says no AI provider is configured, the provider isn't set up yet or doesn't support structured answers.
3. Set the **Daily request cap** and **Questions per visitor per hour** to fit your provider budget.

Ask the Docs then answers in every knowledge base search box (the Knowledge Base Search block, the `[[kbsearch]]` shortcode and the knowledge base templates) and in the [Help widget](https://webberzone.com/support/knowledgebase/knowledge-base-help-widget/). Live search suggestions still appear while typing; pressing Enter or the search button asks the question. Without JavaScript, or when no provider is available, the search box submits to the knowledge base search results as usual.

## Settings

All settings are under **Knowledge Base → Settings → AI (Beta)**.

| Setting | Default | What it does |
| --- | --- | --- |
| **Enable Ask the Docs** | Off | Turns the feature on. |
| **AI provider** | Automatic | The connected provider that answers. Automatic lets WordPress choose, and is used if the chosen provider's plugin is deactivated. |
| **Model** | Provider default | The model used with the chosen provider. Shown when a provider is chosen; save after changing the provider to list its models. Models without reasoning usually answer in a second or two; reasoning models can take ten seconds or more. |
| **Fallback provider** | No fallback | Tried when the AI provider fails, for example when its usage limit is reached. Shown only when two or more providers are connected. |
| **Fallback model** | Provider default | The model used with the fallback provider. Shown when a fallback provider is chosen. |
| **Articles sent as context** | `4` | Matching articles sent with each question, from 1 to 8. More articles improve coverage but cost more. |
| **Maximum characters per article** | `3000` | Each article is trimmed to this length before it is sent, from 500 to 10,000. |
| **Answer length** | Short | **Short** gives two or three sentences. **Medium** gives one or two paragraphs. |
| **Show sources** | On | Lists links to the articles the answer is based on. |
| **Fallback message** | Built-in text | Shown with a search results link when there is no answer. |
| **Use in knowledge base search boxes** | On | Lets knowledge base search boxes answer questions. Every uncached search costs a provider request. |
| **Use in the Help widget** | On | Shows an answer above the article results in the Help widget, which must be enabled. |
| **Daily request cap** | `200` | Provider requests per day across the site, from 1 to 100,000. |
| **Questions per visitor per hour** | `10` | Per-visitor limit, from 1 to 1,000. |
| **Record questions for Content gaps** | Off | Stores question text for the Content gaps report. |
| **Log retention (days)** | `90` | Recorded questions are deleted after this many days, from 1 to 3,650. |

**Compare models.** The **Compare models** button under **AI provider** opens a window where you ask one question with two models side by side and see each answer, its sources and how long it took. **Use this model** fills in the matching **Model** setting; close the window and save to keep it. Each model costs one provider request, counted towards the daily request cap. These answers are not cached or recorded.

## What visitors see

The answer appears below the search box in a card that follows your **Knowledge Base Style**, or the Help widget's colors in the widget. It includes:

- **Based on this article** (or **these articles**): the articles the answer came from.
- **Related articles**: up to three other articles from the same section, or with the same tags, as the main source.
- **See all search results**: hidden when the visitor is already on the results page.
- A note that the answer was generated by AI and may contain mistakes.

When there is no answer, visitors see the **Fallback message** and a link to the search results. On the knowledge base search results page, the first search box answers the search automatically above the results. Later pages don't ask again.

## How it answers

Answers come only from your published articles; the model is told not to use general knowledge.

1. Each keyword is searched against your published, non-password-protected articles. Rare keywords such as "refund" count for more than common ones such as "WordPress".
2. The best matches are converted to plain text, trimmed, and sent to the provider with the question.
3. Knowledge Base Pro checks the reply and builds the source links from your own articles. See the [developer reference](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/) for how replies are validated.

If no articles match, no request is sent.

## Costs and limits

**Answers are cached for a week.** Rephrasings of a question share the cached answer: "How does the cache work?" and "how do caches work please" get the same answer, while question words and negations are kept apart, so "Why does X not work?" is asked separately from "Why does X work?". Cached answers don't count toward either limit.

An answer is retired as soon as an article it was built from is edited, unpublished or deleted, or its sections or tags change. Publishing or updating any article retires only cached "no answer" results, since the new article may answer them. Changing the Ask the Docs settings, or editing a section, product or tag, retires every cached answer.

**Providers that fail are paused.** A network, rate-limit, quota or server error pauses the provider for 5 minutes, growing to an hour on repeated failures, and the fallback provider answers meanwhile. Each provider gets 20 seconds to answer before the fallback is tried; some connectors allow reasoning models longer.

**Each visitor gets a fixed number of questions per hour.** Once a visitor reaches it, their questions go to the search results with no error shown.

**The daily cap switches Ask the Docs off** until the next day in your site's time zone. Search boxes then work as plain search forms.

**Requests must come from your site and a browser.** Requests from other sites, bots and scripts are refused before any provider request. See [Bot check](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/) in the developer reference. A determined script can fake both, so the daily cap remains the limit on cost.

To clear cached answers, go to **Knowledge Base → Tools** and click **Clear Cache**. This also clears the output and REST API caches and rechecks the provider. You rarely need it, because editing an article already clears cached answers.

## Privacy

The visitor's question and excerpts from your articles are sent to your AI provider. Question text is stored only when **Record questions for Content gaps** is on, and never with the IP address or user account. Rate limiting uses a salted hash of the IP address, deleted after the hour ends.

Knowledge Base Pro adds suggested text to **Settings → Privacy → Policy Guide**. Add your provider's name and a link to its privacy policy. Uninstalling Knowledge Base Pro deletes the question log, cached answers and usage counters.

## Content gaps

With **Record questions for Content gaps** on, **Knowledge Base → Content gaps** lists the questions your knowledge base could not answer. Matching questions are grouped, so "How do I export?" and "how do i export" count once, and a visitor repeating a question within an hour is counted once. The report also shows how many questions were asked and answered, the answer rate and how many came from the cache.

Each row links to a new article with the question as its title, and to the knowledge base search for it. **Export CSV** downloads the list for the selected dates. **Empty log** permanently deletes every recorded question, answered or not; it appears only when the log has questions.

## Troubleshooting

- **The AI tab says no provider is configured.** Check **Settings → Connectors**, then click **Check again** in the notice.
- **Search boxes don't answer questions.** No provider is available, so search boxes work as plain search forms.
- **Answers stopped partway through the day.** The daily request cap was reached. Raise it, or wait until the next day.
- **Every answer says it could not find an answer.** Your articles may not cover the questions. Turn on Content gaps recording and check the report.
- **Visitors behind a proxy share one limit.** Use the `wzkb_ai_visitor_ip` filter to return the client IP. See the [developer reference](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/).
- **Two lists of suggestions appear under the search box.** Update [Better Search](https://wordpress.org/plugins/better-search/) to 4.5.0 or later.

## See also

- [Ask the Docs developer reference](https://webberzone.com/support/knowledgebase/ask-the-docs-developer-reference/)
- [Knowledge Base Pro Help Widget](https://webberzone.com/support/knowledgebase/knowledge-base-help-widget/)
- [Live Search](https://webberzone.com/support/knowledgebase/live-search/)
