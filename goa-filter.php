<?php
/*
Plugin Name: Simple Product Category and Price Filter
Description: WooCommerce Product Filter - Goa Digital
Version: 1.0
Author: Ali Ozgenc
*/

// Admin Sekmesi Oluşturma
function my_category_filter_admin_tab()
{
    add_menu_page(
        'Category Filter Custom', // Sayfa başlığı
        'Category Filter Custom', // Menü adı
        'manage_options', // Gereken yetki düzeyi
        'my_category_filter_admin_page', // Sayfa slug
        'my_category_filter_admin_page_content', // İçerik fonksiyonu
        'dashicons-admin-generic', // Menü ikonu (isteğe bağlı)
        30 // Menü sırası
    );
}

// Admin Sayfa İçeriği
function my_category_filter_admin_page_content()
{
?>
    <div class="wrap">
        <h2>Category Filter</h2>
        <p>Shortcode: [category_price_filter_shortcode]</p>
    </div>
<?php
}

// Filtreleme Formu Shortcode
function my_category_price_filter_shortcode()
{
    ob_start();

    // Sadece "Shop" sayfasında görünecek
    if (is_shop()) {
        // Önceki seçimleri al
        $selected_category = isset($_GET['product_cat']) ? sanitize_text_field($_GET['product_cat']) : '';
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;

        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'parent'   => 0,
            'fields'   => 'id=>name',
        ));

        echo '<div class="category-filter-sidebar">';
        echo '<form id="filter-form" method="get">';
        echo '<div class="category-radio-group">';

        foreach ($categories as $category_id => $category_name) {
            $category_slug = sanitize_title($category_name);

            echo '<input type="radio" id="cat-' . $category_id . '" name="product_cat" value="' . $category_slug . '" ' . checked($category_slug, $selected_category, false) . '>';
            echo '<label for="cat-' . $category_id . '">' . $category_name . '</label>';

            $subcategories = get_terms(array(
                'taxonomy' => 'product_cat',
                'parent'   => $category_id,
                'fields'   => 'id=>name',
            ));

            if ($subcategories) {
                echo '<ul class="subcategories">';

                foreach ($subcategories as $subcategory_id => $subcategory_name) {
                    $subcategory_slug = sanitize_title($subcategory_name);

                    echo '<li>';
                    echo '<input type="radio" id="cat-' . $subcategory_id . '" name="product_cat" value="' . $subcategory_slug . '" class="metro-subcategory" ' . checked($subcategory_slug, $selected_category, false) . '>';
                    echo '<label for="cat-' . $subcategory_id . '">' . $subcategory_name . '</label>';
                    echo '</li>';
                }

                echo '</ul>';
            }
        }

        echo '</div>';

        // Fiyat filtresi için input alanları
        echo '<label for="price-min">Min Price:</label>';
        echo '<input type="number" id="price-min" name="min_price" value="' . $min_price . '">';

        echo '<label for="price-max">Max Price:</label>';
        echo '<input type="number" id="price-max" name="max_price" value="' . $max_price . '">';

        echo '<input type="submit" value="Filtrele">';
        echo '</form>';

        // Seçilen kategori ve fiyat aralığına bağlı olarak ürünleri listeleyen kod
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
        );

        // Kategori filtresi ekle
        if ($selected_category) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $selected_category,
            );
        }

        // Fiyat filtresi ekle
        if ($min_price > 0 || $max_price > 0) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_price',
                    'value'   => array($min_price, $max_price),
                    'type'    => 'NUMERIC',
                    'compare' => 'BETWEEN',
                ),
            );
        }

        $products = new WP_Query($args);



        echo '</div>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('category_price_filter_shortcode', 'my_category_price_filter_shortcode');

// Admin sekmesi eklenirken fonksiyonu çağır
add_action('admin_menu', 'my_category_filter_admin_tab');
?>

<style>
    .category-filter-sidebar {
        max-width: 300px;
        margin: 0 auto;
        padding: 15px;
        background-color: #f4f4f4;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .category-radio-group {
        margin-bottom: 15px;
    }

    .category-radio-group label {
        display: inline;
        align-items: center;
        margin-bottom: 5px;
        font-size: 16px;
    }

    .category-radio-group input[type="radio"] {
        margin-right: 5px;
    }

    .category-radio-group .main-category {
        font-size: 16px;
        font-weight: bold;
    }

    .subcategories {
        list-style: none;
        padding-left: 20px;
        margin-top: 10px;
    }

    .subcategories li {
        margin-bottom: 5px;
        font-size: 14px;
    }

    #price-min,
    #price-max {
        width: 48%;
        display: inline-block;
        margin-bottom: 10px;
    }

    #filter-form input[type="submit"] {
        background-color: #4caf50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    #product-container {
        margin-top: 20px;
    }

    #product-container ul {
        list-style: none;
        padding: 0;
    }

    #product-container li {
        margin-bottom: 10px;
        font-size: 16px;
    }
</style>