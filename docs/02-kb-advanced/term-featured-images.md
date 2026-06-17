---
slug: term-featured-images
title: "Term Featured Images"
products: [knowledgebase]
sections: [02-kb-advanced]
tags: [pro,images,products,sections]
status: publish
order: 6
---

> **This is a Pro feature.** Term Featured Images require [Knowledge Base Pro](https://webberzone.com/plugins/knowledgebase/).

Term Featured Images let you assign a featured image to any Knowledge Base product, section, or tag. Once set, the image appears in the product grid cards and in the header of archive pages, giving your knowledge base a more visual and polished appearance.

## Setting a featured image on a term

1. Go to **Knowledge Base → Products** (or **Sections**, or **Tags**) in your WordPress admin.
2. Click the term you want to edit, or hover and click **Edit**.
3. Scroll down to the **Featured Image** field.
4. Click **Set Image** to open the WordPress Media Library. Upload a new image or choose an existing one, then click **Use this image**.
5. Click **Update** to save.

To remove a featured image, click **Remove Image** and save the term.

The same **Featured Image** field is also available when creating a new term — fill it in on the Add Term form and it is saved along with the term.

## Where the image appears

### Product grid cards

When a product has a featured image, it is displayed inside its card in the product grid — above the product title. The image is sized to fill the card width with a 16:9 aspect ratio. This works automatically; no setting needs to be changed.

### Product and section archive page headers

On product and section archive pages, the featured image is displayed below the term description in the page header. This is controlled by the **Show featured image on archive pages** setting under **Knowledge Base → Settings → Styles**.

When this setting is enabled, the image is shown on both classic (PHP template) and block-based (FSE) themes:

- **Classic themes** — the image is rendered below the term description in the `.page-header` element of the `taxonomy-wzkb_product.php` and `taxonomy-wzkb_category.php` templates.
- **Block themes (FSE)** — the image is appended automatically after the `core/term-description` block wherever it appears in the template.

## CSS classes

| Class | Where it appears |
| --- | --- |
| `.wzkb-product-card-image` | Wrapper `<div>` around the image inside each product grid card. |
| `.wzkb-section-thumbnail` | Wrapper `<div>` around the image in archive page headers and FSE templates. |

Both wrappers constrain the image to a 16:9 aspect ratio with `object-fit: cover`, so images of any proportion display consistently. Customize them via **Appearance → Customize → Additional CSS** or your theme's stylesheet:

```css
/* Increase the card image border radius */
.wzkb-product-card-image {
    border-radius: 12px;
}

/* Limit the archive header image width */
.wzkb-section-thumbnail {
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
```

## Developer hooks

### `wzkb_product_card_image`

Filters the image HTML rendered inside a product grid card. Return an empty string to suppress the image for a specific product.

```php
add_filter(
    'wzkb_product_card_image',
    function ( string $html, \WP_Term $product_term ): string {
        // Use a different image size for a specific product.
        if ( 'my-product' === $product_term->slug ) {
            $image_id = (int) get_term_meta( $product_term->term_id, 'wzkb_term_image_id', true );
            if ( $image_id ) {
                return '<div class="wzkb-product-card-image">'
                    . wp_get_attachment_image( $image_id, 'medium' )
                    . '</div>';
            }
        }
        return $html;
    },
    10,
    2
);
```

| Parameter | Type | Description |
| --- | --- | --- |
| `$html` | string | Image HTML to render. Empty by default; populated by the Pro feature when an image is set. |
| `$product_term` | `\WP_Term` | The product term being rendered. |

### `wzkb_term_archive_header_image`

Filters the image HTML rendered in the header of product and section archive pages (classic templates only). Return an empty string to suppress the image.

```php
add_filter(
    'wzkb_term_archive_header_image',
    function ( string $html, \WP_Term $term ): string {
        // Suppress the header image on tag archive pages.
        if ( 'wzkb_tag' === $term->taxonomy ) {
            return '';
        }
        return $html;
    },
    10,
    2
);
```

| Parameter | Type | Description |
| --- | --- | --- |
| `$html` | string | Image HTML to render. Empty by default; populated by the Pro feature when an image is set and the setting is enabled. |
| `$term` | `\WP_Term` | The term whose archive page is being displayed. |

### `wzkb_get_term_thumbnail()`

A public helper function to retrieve the featured image `<img>` tag for any KB term. Useful in custom templates.

```php
wzkb_get_term_thumbnail( int|\WP_Term $term, string $size = 'thumbnail', array $attr = array() ): string
```

```php
// Display the featured image for the current term at medium size.
$term  = get_queried_object();
$image = wzkb_get_term_thumbnail( $term, 'medium', array( 'class' => 'my-term-image' ) );
if ( $image ) {
    echo '<div class="my-wrapper">' . $image . '</div>';
}
```

Returns an empty string if no image has been set for the term.
