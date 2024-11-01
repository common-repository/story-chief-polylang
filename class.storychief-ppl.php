<?php

class Storychief_PPL
{

    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::$initiated = true;

            remove_action('storychief_save_categories_action', '\Storychief\Mapping\saveCategories');
            remove_action('storychief_save_tags_action', '\Storychief\Mapping\saveTags');

            add_action('storychief_after_publish_action', ['Storychief_PPL', 'linkTranslations'], 1);
            add_action('storychief_save_categories_action', ['Storychief_PPL', 'saveCategories'], 1);
            add_action('storychief_save_tags_action', ['Storychief_PPL', 'saveTags'], 1);
        }
    }

    public static function saveCategories($story)
    {
        $post_language = $story['language'];
        if (isset($story['categories']['data'])) {
            $categories = [];
            foreach ($story['categories']['data'] as $category) {
                if (!$cat_ID = self::findTermLocalized($category['name'],
                  $post_language, 'category')) {
                    // try to find the category ID for cat with name X in language Y
                    // if it does not exist. create that sucker
                    if (!function_exists('wp_insert_category')) {
                        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
                    }
                    $cat_ID = wp_insert_category([
                      'cat_name'          => $category['name'],
                      'category_nicename' => $category['name'] . ' ' . $post_language,
                    ]);
                    pll_set_term_language($cat_ID, $post_language);
                }

                $categories[] = $cat_ID;
            }

            wp_set_post_terms($story['external_id'], $categories, 'category',
              $append = false);
        }
    }

    public static function saveTags($story)
    {
        $post_language = $story['language'];
        if (isset($story['tags']['data'])) {
            $tags = [];
            foreach ($story['tags']['data'] as $tag) {
                if (!$tag_ID = self::findTermLocalized($tag['name'],
                  $post_language, 'post_tag')) {
                    // try to find the tag ID for tag with name X in language Y
                    // if it does not exist. create that sucker

                    if (!function_exists('wp_insert_term')) {
                        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
                    }
                    $tag = wp_insert_term($tag['name'], 'post_tag', [
                      'slug' => $tag['name'] . ' ' . $post_language,
                    ]);
                    $tag_ID = isset($tag['term_id']) ? $tag['term_id'] : null;
                    pll_set_term_language($tag_ID, $post_language);
                }
                $tags[] = $tag_ID;
            }

            wp_set_post_terms($story['external_id'], $tags, 'post_tag',
              $append = false);
        }
    }

    public static function linkTranslations($payload)
    {
        $post_ID = $payload['external_id'];
        $post_language = $payload['language'];
        $src_ID = isset($payload['source']['data']['external_id']) ? $payload['source']['data']['external_id'] : null;
        $src_language = isset($payload['source']['data']['external_id']) ? $payload['source']['data']['language'] : null;

        // Translate Post
        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post_ID, $post_language);
            if ($src_ID && $src_language && function_exists('pll_save_post_translations')) {
                $translations = pll_get_post_translations($src_ID);
                $translations[$post_language] = $post_ID;

                pll_save_post_translations($translations);
            }
        }
    }

    private static function findTermLocalized($name, $lang, $taxonomy)
    {
        $args = [
          'get'                    => 'all',
          'name'                   => $name,
          'number'                 => 0,
          'taxonomy'               => $taxonomy,
          'update_term_meta_cache' => false,
          'orderby'                => 'none',
          'suppress_filter'        => true,
          'lang'                   => $lang,
        ];
        $terms = get_terms($args);
        if (is_wp_error($terms) || empty($terms)) {
            return false;
        }
        $term = array_shift($terms);

        return $term->term_id;
    }

}
