<?php
/**
 * Auto-setup for required pages and templates.
 */

function news_app_setup_required_pages() {
    $pages = array(
        'bookmarks' => array(
            'title'    => 'Signets',
            'template' => 'page-templates/bookmarks.php'
        ),
        'mes-articles' => array(
            'title'    => 'Mes Articles',
            'template' => 'page-templates/my-articles.php'
        ),
        'create-article' => array(
            'title'    => 'Créer un article',
            'template' => 'page-templates/create-article.php'
        ),
        'all-users' => array(
            'title'    => 'Tous les utilisateurs',
            'template' => 'page-templates/all-users.php'
        ),
        'connexion' => array(
            'title'    => 'Connexion',
            'template' => 'page-templates/login.php'
        ),
        'inscription' => array(
            'title'    => 'Inscription',
            'template' => 'page-templates/register.php'
        ),
    );

    foreach ( $pages as $slug => $page_data ) {
        $query = new WP_Query( array(
            'post_type'      => 'page',
            'name'           => $slug,
            'posts_per_page' => 1,
            'post_status'    => 'publish'
        ) );

        if ( ! $query->have_posts() ) {
            // Page doesn't exist, create it
            $page_id = wp_insert_post( array(
                'post_title'   => $page_data['title'],
                'post_content' => '',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_name'    => $slug
            ) );

            if ( ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
            }
        } else {
            // Page exists, ensure template is correct
            $page_id = $query->posts[0]->ID;
            update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
        }
        wp_reset_postdata();
    }

    // Always flush rewrite rules after creating pages to ensure permalinks work
    flush_rewrite_rules();
}

// Hook to theme activation and admin init for robustness
add_action( 'after_switch_theme', 'news_app_setup_required_pages' );
add_action( 'admin_init', 'news_app_setup_required_pages' );
