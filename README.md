# WebberZone Knowledge Base

![Knowledge Base](https://raw.githubusercontent.com/WebberZone/knowledgebase/master/wporg-assets/banner-1544x500.png)

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/knowledgebase.svg?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/knowledgebase.svg?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/knowledgebase?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/knowledgebase?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)

__Requires:__ 6.3

__Tested up to:__ 6.7

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) | [WordPress.org Plugin page](https://wordpress.org/plugins/knowledgebase/)

Effortlessly build a comprehensive knowledge base for unlimited products on your WordPress site and elevate your customer support experience.

## Description

[Knowledge Base](https://webberzone.com/plugins/knowledgebase/) is an easy-to-use WordPress plugin that allows you to create a knowledge base / FAQ section on your site.

You can use it to create a single or multi-product knowledge base with little effort.

The plugin was born after I tried several free plugins and themes that didn't fit my purpose. It's designed to be very easy to install and use out of the box.

You can view a [live demo of my knowledge base](https://webberzone.com/support/knowledgebase/).

### Terminology

* __Articles__: A custom post type `wz_knowledgebase` is used to store all the knowledge base articles
* __Sections__: A custom taxonomy ( `kbcategory` ) used to create the knowledge base. You will need *at least one category* to display the knowledge base. Add these categories under *Knowledge Base > Sections*
* __Tags__: Additionally you can use tags ( `kbtags` ) can also be used for each knowledge base article.

### Main features

* Supports unlimited knowledge bases using different sections with unlimited nested levels
* Inbuilt styles that display the Knowledge Base beautifully and are fully responsive - Uses the [Responsive Grid System](http://www.responsivegridsystem.com/)
* Customizable permalinks: Archives are enabled so your knowledge base can be viewed automatically at `/knowledgebase/` upon activation. You can change this on the Settings page
* Shortcode: `[knowledgebase]` will allow you to display the knowledge base on any page you choose. For other shortcodes, check the FAQ
* Gutenberg block: You can display the knowledge base using a block. Find it by typing `kb` or `knowledge base` when adding a new block
* Breadcrumbs: Default templates include breadcrumbs. Alternatively, use the function or shortcode to display this where you want
* Widgets: WZKB Articles, WZKB Sections and WZKB Breadcrumbs
* Inbuilt cache to speed up the display of your knowledge base articles

## Contribute

If you have an idea, I'd love to hear it. WebberZone Knowledge Base is also available on [Github](https://github.com/WebberZone/knowledgebase). You can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues) or, better yet, fork the plugin, add a new feature and send me a pull request.

## Screenshots

![Knowledge Base Menu in the WordPress Admin](https://raw.githubusercontent.com/WebberZone/knowledgebase/master/wporg-assets/screenshot-1.png)
*Knowledge Base Menu in the WordPress Admin*

For more screenshots, visit the [WordPress plugin page](http://wordpress.org/plugins/knowledgebase/screenshots/)

## Installation

### WordPress install (The easy way)

1. Navigate to "Plugins" within your WordPress Admin Area
2. Click "Add new" and in the search box enter "Knowledgebase" or "Knowledge Base"
3. Find the plugin in the list (usually the first result) and click "Install Now"
4. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

### Manual install

1. Download the plugin
2. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
3. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

### Usage

1. Visit `Knowledge Base &raquo; Sections` to add new categories to the knowledge base
2. Visit `Knowledge Base &raquo; Add New` to add new Articles to the knowledge base. You can select a section from there while adding
3. Optionally, create a new page or edit an existing one and add the shortcode `[knowledgebase]` or use the block to set up this page to display the knowledgebase

The plugin supports unlimited levels of category hierarchy. To build a multi-product knowledge base:

1. Set the *First section level* under the Output tab to 2
2. Create a set of top-level sections for each product
3. Create sub-sections for each of the products

[This live demo](https://webberzone.com/support/knowledgebase/) is a working example of a multi-product knowledge base.

## Frequently Asked Questions

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/knowledgebase/faq/) and the [Knowledge Base](https://webberzone.com/support/section/knowledgebase/).

If your question is not listed below, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). It is the fastest way to get support, as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

### 404 errors on the knowledge base

This is usually due to outdated permalinks. To flush the existing permalink rules, visit Settings > Permalinks in your WordPress admin area.

### Shortcodes

For details on all the shortcodes included in the plugin, refer to [this Knowledge Base article](https://webberzone.com/support/knowledgebase/knowledge-base-shortcodes/).

### Using your templates for archives and search

WebberZone Knowledge Base comes built with custom templates to display archives of the articles, category archives, and search results. You can easily override any of these templates by creating your template in your theme's folder or in `wp-content/knowledgebase/templates`

1. Article view: single-wz_knowledgebase.php or single-wz_knowledgebase.html
2. Articles archive: archive-wz_knowledgebase.php or archive-wz_knowledgebase.html
3. Category archive: taxonomy-wzkb_category.php or taxonomy-wzkb_category.html
4. Search results: wzkb-search.php or wzkb-search.html

### How do I sort the posts or sections

The plugin doesn't have an inbuilt feature to sort posts or sections. You will need an external plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) which allows you to easily drag and drop posts, sections or tags to display them in a custom order.

### How can I report security bugs?

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/knowledgebase)

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](http://wordpress.org/plugins/knowledgebase). In addition to stable releases, the latest beta versions are made available under [releases](https://github.com/WebberZone/knowledgebase/releases).
