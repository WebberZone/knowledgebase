[![Code Climate](https://codeclimate.com/github/WebberZone/knowledgebase/badges/gpa.svg)](https://codeclimate.com/github/WebberZone/knowledgebase) [![Build Status](https://travis-ci.org/WebberZone/knowledgebase.svg?branch=master)](https://travis-ci.org/WebberZone/knowledgebase)

# Knowledgebase

__Requires:__ 3.5

__Tested up to:__ 4.3

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Knowledgebase](https://github.com/WebberZone/knowledgebase)

The easiest way to create a Knowledgebase or FAQ on your WordPress blog.

## Description

As the name suggests, [Knowledgebase](https://github.com/WebberZone/knowledgebase) will allow you to create a simple Knowledgebase on your WordPress blog.

I felt the need to write up this simple plugin after I tried several free plugins out there, but none seemed to fit my purpose. In the spirit of open source, I'm releasing this to everyone who might find it useful.

### Main features:

* Uses a custom post type `wz_knowledgebase` with a slug of `wzkb` ensuring your data always stays even if you choose to delete this plugin
* Uses Categories ( `kbcategory` ) to automatically draw up the knowledgebase
* Additionally tags ( `kbtags` ) can also be used for each knowledgebase article
* Shortcode `[[knowledgebase]]` will allow you to display the knowledgebase on any page of your choosing. I prefer creating one with the slug `knowledgebase`
* Inbuilt styles to display the knowledge beautifully

There are still many features that can go into this plugin. Got an idea? Then you can [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues).

## Screenshots
![Knowledgebase Menu in the WordPress Admin](https://raw.githubusercontent.com/WebberZone/knowledgebase/master/assets/screenshot-1.png)

_Knowledgebase Menu in the WordPress Admin_

For more screenshots visit the <a href="http://wordpress.org/plugins/knowledgebase/screenshots/">WordPress plugin page</a>


## Installation

1. Upload `knowledgebase` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a new page or edit an existing one and add the shortcode `[knowledgebase]` to set up this page to display the knowledgebase
4. Visit `Knowledgebase &raquo; Add New` to add new Articles to the knowledgebase
5. Visit `Knowledgebase &raquo; KB Category` to add new categories to the knowledgebase. Alternatively, you can add new categories from the meta box in the Add New page

The plugin supports unlimited levels of category hierarchy, however, the recommended setting for creating the knowledge base is to create a top level category with the name of the knowledgebase and sub-level categories for each section of this knowledgebase. Check out the Category view screenshot as an example.


## Frequently Asked Questions

If your question isn't listed here, please post a comment at the <a href="http://wordpress.org/support/plugin/knowledgebase">WordPress.org support forum</a>. I monitor the forums on an ongoing basis. If you're looking for more advanced support, please see <a href="http://ajaydsouza.com/support/">details here</a>.

Alternatively, [create an issue on the Github page](https://github.com/WebberZone/knowledgebase/issues)


### Shortcode

You can display the knowledgebase anywhere in your blog using the `[knowledgebase]` shortcode. The recommended option is to add this to a dedicated page. The plugin takes one optional attribute `category`:

`[knowledgebase category="92"]`

*category* : Category ID for which you want to display the knowledge base. You can find the ID in the KB Category listing under Knowledgebase in the WordPress Admin.

