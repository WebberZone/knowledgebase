# WebberZone Knowledge Base

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/knowledgebase.svg?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](http://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/knowledgebase.svg?style=flat-square)](https://wordpress.org/plugins/knowledgebase/)
[![Build Status](https://travis-ci.org/WebberZone/knowledgebase.svg?branch=master)](https://travis-ci.org/WebberZone/knowledgebase)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WebberZone/knowledgebase/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WebberZone/knowledgebase/?branch=master)
[![Code Climate](https://codeclimate.com/github/WebberZone/knowledgebase/badges/gpa.svg)](https://codeclimate.com/github/WebberZone/knowledgebase)

__Requires:__ 4.8

__Tested up to:__ 5.3

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) | [WordPress.org Plugin page](https://wordpress.org/plugins/knowledgebase/)

Quickly and efficiently create a highly-flexible knowledge base or FAQ on your WordPress blog.

## Description

As the name suggests, [Knowledge Base](https://webberzone.com/plugins/knowledgebase/) will allow you to create a knowledge base / FAQ section on your WordPress blog.

The plugin was born after I tried several free plugins and themes out there and that couldn't fit my purpose. It's designed to be very easy to install and use out of the box and I'll be adding more features into the core and as addons.

The plugin uses a custom post in conjunction with custom taxonomies to create and display your knowledge base.

### Main features

* Uses a custom post type `wz_knowledgebase` with a slug of `wzkb` ensuring your data always stays even if you choose to delete this plugin
* Customizable permalinks: Archives are enabled so your knowledge base can be viewed at `/knowledgebase/` automatically on activation. You can change this in the Settings page
* Uses Categories ( `kbcategory` ) to automatically draw up the knowledge base. You will need at least one category in order to display the knowledge base
* Additionally tags ( `kbtags` ) can also be used for each knowledge base article
* Shortcode `[knowledgebase]` will allow you to display the knowledge base on any page of your choosing
* Breadcrumbs: Default templates include breadcrumbs. Alternatively, use functions or shortcode to display this where you want
* Widgets: WZKB Articles, WZKB Sections and WZKB Breadcrumbs
* Inbuilt styles that display the knowledge beautifully and are fully responsive - Uses the [Responsive Grid System](http://www.responsivegridsystem.com/)
* Supports unlimited nested of categories
* Inbuilt cache to speed up the display of your knowledge base articles

## Contribute

WebberZone Knowledge Base is fully functional and in fact, I use this to power the [WebberZone Knowledge Base](https://webberzone.com/support/knowledgebase). However, there are still many features that I plan to add to this plugin. This includes inbuilt templates for articles, live search, etc.

If you have an idea, I'd love to hear it. WebberZone Knowledge Base is also available on [Github](https://github.com/WebberZone/knowledgebase). You can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues) or, better yet, fork the plugin, add a new feature and send me a pull request.

## Screenshots

![Knowledge Base Menu in the WordPress Admin](https://raw.githubusercontent.com/WebberZone/knowledgebase/master/wporg-assets/screenshot-1.png)
_Knowledge Base Menu in the WordPress Admin_

For more screenshots visit the [WordPress plugin page](http://wordpress.org/plugins/knowledgebase/screenshots/)

## Installation

### WordPress install (The easy way)

1. Navigate to “Plugins” within your WordPress Admin Area
2. Click “Add new” and in the search box enter “Knowledgebase” or "Knowledge Base"
3. Find the plugin in the list (usually the first result) and click “Install Now”
4. Activate or Network activate the Plugin in WP-Admin under the Plugins screen

### Manual install

Download the plugin

1. Extract the contents of knowledgebase.zip to wp-content/plugins/ folder. You should get a folder called knowledgebase.
2. Activate or Network activate the Plugin in WP-Admin under the Plugins screen
3. Create a new page or edit an existing one and add the shortcode `[knowledgebase]` to set up this page to display the knowledgebase
4. Visit `Knowledge Base &raquo; Add New` to add new Articles to the knowledge base
5. Visit `Knowledge Base &raquo; Sections` to add new categories to the knowledge base. Alternatively, you can add new categories from the meta box in the Add New page

The plugin supports unlimited levels of category hierarchy, however, the recommended setting for creating the knowledge base is to create a top level category with the name of the knowledge base and sub-level categories for each section of this knowledge base. Check out the Category view screenshot as an example.

![Knowledge Base Category view in the WordPress Admin](https://raw.githubusercontent.com/WebberZone/knowledgebase/master/wporg-assets/screenshot-3.png)

## Frequently Asked Questions

Check out the [FAQ on the plugin page](http://wordpress.org/plugins/knowledgebase/faq/).

If your question isn't listed there, please create a new post at the [WordPress.org support forum](http://wordpress.org/support/plugin/knowledgebase). It is the fastest way to get support as I monitor the forums regularly. I also provide [premium *paid* support via email](https://webberzone.com/support/).

### 404 errors on the knowledge base

This is usually because of outdated permalinks. To flush the existing permalinks rules simply visit Settings &raquo; Permalinks in your WordPress admin area.

### Shortcode

You can display the knowledge base anywhere in your blog using the `[knowledgebase]` shortcode. The shortcode takes one optional attribute `category`:

```php
[knowledgebase category="92"]
```

*category* : Category ID for which you want to display the knowledge base. You can find the ID in the Sections listing under the Knowledge Base menu in the WordPress Admin.

You can also display the search form using `[kbsearch]`

### Using your own templates for archives and search

WebberZone Knowledge Base comes inbuilt with a set of custom templates to display archives of the articles, category archives as well as search results. You can easily override any of these templates by creating your own template in your theme's folder

1. Articles archive: archive-wz_knowledgebase.php
2. Category archive: taxonomy-wzkb_category.php
3. Search results: search-wz_knowledgebase.php

### How do I sort the posts or sections

The plugin doesn't have an inbuilt feature to sort posts or sections. You will need an external plugin like [Intuitive Custom Post Order](https://wordpress.org/plugins/intuitive-custom-post-order/) which allows you to easily drag and drop posts, sections or tags to display them in a custom order.

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](http://wordpress.org/plugins/knowledgebase). In addition to stable releases, latest beta versions are made available under [releases](https://github.com/WebberZone/knowledgebase/releases).
