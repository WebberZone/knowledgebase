# Knowledge Base Settings

This document describes all available settings for the Knowledge Base plugin. Access settings via __Knowledge Base → Settings__ in your WordPress admin.

## General

### Multi-Product Mode

#### Enable Multi-Product Mode

Enable this option to use a dedicated "Products" menu to organize your knowledge base articles and sections by product. This system allows you to assign each article or section to one or more products, making it easier to manage documentation for different software, hardware, or service lines. If your knowledge base does not need this level of organization, you can leave this option disabled. This is a transitional feature for advanced organization and future compatibility.

### Permalinks

The following settings affect the permalinks of the knowledge base. These are set when registering the custom post type and taxonomy. Please visit the Permalinks page in the Settings menu to refresh permalinks if you get 404 errors.

#### Knowledge Base slug

This will set the opening path of the URL of the knowledge base and is set when registering the custom post type.

__Default:__ `knowledgebase`

#### Product slug

This slug forms part of the URL for product pages when Multi-Product Mode is enabled. The value is used when registering the custom taxonomy.

__Default:__ `kb/product`

#### Section slug

Each section is a section of the knowledge base. This setting is used when registering the custom section and forms a part of the URL when browsing section archives.

__Default:__ `kb/section`

#### Tags slug

Each article can have multiple tags. This setting is used when registering the custom tag and forms a part of the URL when browsing tag archives.

__Default:__ `kb/tags`

#### Article Permalink Structure (PRO)

Structure for article URLs. Default: %postname%

__Default:__ `%postname%`

### Performance

#### Enable cache

Cache the output of the queries to speed up retrieval of the knowledgebase. Recommended for large knowledge bases.

__Default:__ Disabled

#### Cache Time (PRO)

How long should the knowledge base be cached for. Default is 1 day.

__Options:__

- No expiry
- 1 Hour
- 6 Hours
- 12 Hours
- 1 Day (default)
- 3 Days
- 1 Week
- 2 Weeks
- 30 Days
- 60 Days
- 90 Days
- 1 Year

### Uninstall options

#### Delete options on uninstall

Check this box to delete the settings on this page when the plugin is deleted via the Plugins page in your WordPress Admin.

__Default:__ Enabled

#### Delete all content on uninstall

Check this box to delete all the posts, categories and tags created by the plugin. There is no way to restore the data if you choose this option.

__Default:__ Disabled

### Feed options

#### Include in feed

Adds the knowledge base articles to the main RSS feed for your site.

__Default:__ Enabled

#### Disable KB feed

The knowledge base articles have a default feed. This option will disable the feed. You might need to refresh your permalinks when changing this option.

__Default:__ Disabled

## Output

### Knowledge base title

This will be displayed as the title of the archive title as well as on other relevant places.

__Default:__ `Knowledge Base`

### First section level

Knowledge Base supports an unlimited hierarchy of sections. This setting determines which section level is displayed in the grid layout.

- __Set to 1__: Use when multi-product mode is enabled. Sections become the first level of each product.
- __Set to 2__: Use for traditional single-product knowledge bases. Top-level sections act as product categories.

This works in conjunction with the inbuilt styles to control the grid display. The default is 2, which was the behavior before version 3.0.

__Default:__ `2`

__Range:__ 1-5

### Show article count

If selected, the number of articles will be displayed in an orange circle next to the header. You can override the color by styling wzkb_section_count.

__Default:__ Enabled

### Show excerpt

Select to include the post excerpt after the article link.

__Default:__ Disabled

### Link section title

If selected, the title of each section of the knowledgebase will be linked to its own page.

__Default:__ Enabled

### Show empty sections

If selected, sections with no articles will also be displayed.

__Default:__ Disabled

### Max articles per section

Enter the number of articles that should be displayed in each section when viewing the knowledge base. After this limit is reached, the footer is displayed with the more link to view the category.

__Default:__ `5`

__Range:__ 1-500

### Show sidebar

Add the sidebar of your theme into the inbuilt templates for archive, sections and search. Activate this option if your theme does not already include this.

__Default:__ Disabled

### Show related articles

Add related articles at the bottom of the knowledge base article. Only works when using the inbuilt template.

__Default:__ Enabled

## Styles

### Include inbuilt styles

Uncheck this to disable this plugin from adding the inbuilt styles. You will need to add your own CSS styles if you disable this option.

__Default:__ Enabled

### Knowledge Base Style

Select a visual style for your knowledge base display. Premium styles are available in the Pro version.

__Default:__ `Classic`

__Available Styles:__

- Legacy
- Classic
- Modern (PRO)
- Minimal (PRO)
- Boxed (PRO)
- Gradient (PRO)
- Compact (PRO)
- Magazine (PRO)
- Professional (PRO)

### Number of columns

Set the number of columns to display the knowledge base archives.

__Default:__ `2`

__Range:__ 1-5

### Custom CSS

Enter any custom valid CSS without any wrapping &lt;style&gt; tags.

## Pro

### Article Rating

#### Enable Rating System

Allow visitors to rate the quality of knowledge base articles.

__Options:__

- Disabled (default)
- Useful / Not Useful
- 1-5 Star Rating

#### Vote Tracking Method

Choose how to prevent duplicate votes. Each method has different privacy implications. [Learn more about tracking methods and GDPR compliance](https://webberzone.com/support/knowledgebase/rating-system/).

__Options:__

- __No Tracking__ - Allows multiple votes (most privacy-friendly)
- __Cookie Only__ - Requires consent (default)
- __IP Address Only__ - Stores personal data
- __Cookie + IP Address__ - Requires both
- __Logged-in Users Only__ - Best for authenticated sites

#### Show Rating Statistics

Display the average rating and vote count below the rating buttons.

__Default:__ Enabled

### Help Widget

A floating help widget that provides self-service support with search, suggested articles, and contact form.

#### Enable Help Widget

Display a floating help widget on your site for self-service support.

__Default:__ Disabled

#### Display Location

Choose where the help widget appears on your site.

__Options:__

- Knowledge Base Only (default)
- Entire Site

#### Button Position

Choose where the help widget button appears on the screen.

__Options:__

- Bottom Right (default)
- Bottom Left

#### Button Style

Choose how the help widget button is displayed.

__Options:__

- Icon Only (default)
- Text Only
- Icon and Text

#### Button Text

Text to display on the help widget button (when text style is selected).

__Default:__ `Help`

#### Help Widget Color

Primary color for the help widget button and interface elements.

__Default:__ `#617DEC`

#### Help Widget Hover Color

Hover color for buttons and interactive elements.

__Default:__ `#4c63d2`

#### Help Widget Text Color

Text color for the help widget button and interface elements.

__Default:__ `#ffffff`

#### Help Widget Hover Text Color

Text color for the help widget button on hover.

__Default:__ `#ffffff`

#### Panel Background Color

Background color for the help widget panel.

__Default:__ `#ffffff`

#### Panel Text Color

Default text color within the help widget panel.

__Default:__ `#1a1a1a`

#### Link Hover Background

Background color when hovering over help widget links and list items.

__Default:__ `#f3f4f6`

#### Greeting Message

Welcome message shown when the help widget opens.

__Default:__ `Hi! How can we help you?`

#### Search Placeholder

Placeholder text for the search input field.

__Default:__ `Search for answers...`

#### Enable Contact Form

Allow visitors to send messages through the help widget.

__Default:__ Enabled

#### Contact Email

Email address where help widget contact form submissions will be sent.

__Default:__ Site admin email

#### Show on Mobile

Display the help widget on mobile devices.

__Default:__ Enabled

#### Enable Animations

Enable smooth animations and transitions for the help widget.

__Default:__ Enabled
