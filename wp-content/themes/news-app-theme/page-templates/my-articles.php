<?php
/**
 * Template Name: Mes Articles
 */
get_header(); ?>

<!-- Sticky Header Wrapper -->
<div class="sticky top-0 z-40 w-full bg-gray-100/80 backdrop-blur-md px-4 lg:px-8 py-6">
    <header class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col xl:flex-row items-center justify-between gap-6">
        <div id="pole-filters" class="flex flex-wrap items-center gap-2">
            <?php $poles = array( 'Dev', 'Design', 'Market' ); ?>
            <button data-pole="all" class="pole-btn px-5 py-3 rounded-xl text-sm font-bold bg-blue-600 text-white shadow-lg shadow-blue-100 transition-all hover:bg-blue-700">Tous</button>
            <?php foreach ( $poles as $pole ) : ?>
                <button data-pole="<?php echo strtolower($pole); ?>" class="pole-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">
                    <?php echo $pole; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-4 w-full xl:w-auto">
            <div class="relative w-full md:w-96 group">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" id="live-search" value="<?php echo get_search_query(); ?>" placeholder="Rechercher dans mes articles..." class="w-full bg-gray-50 border border-gray-100 rounded-xl pl-11 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select id="sort-order" class="flex-1 md:flex-none bg-gray-50 border border-gray-100 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <option value="date">Plus récents</option>
                    <option value="title_asc">A - Z</option>
                    <option value="title_desc">Z - A</option>
                </select>
            </div>
        </div>
    </header>
</div>

<div class="w-full px-4 lg:px-8 pb-12">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-900">Mes Articles</h1>
            <p class="text-gray-500">Gérez les articles que vous avez publiés.</p>
        </div>
        <a href="<?php echo home_url('/create-article'); ?>" class="px-5 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Créer un article</a>
    </div>

    <!-- Articles Loop -->
    <div id="articles-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-8" data-page="1" data-pole="" data-search="" data-orderby="date" data-my-articles="true">
        <?php
        if ( is_user_logged_in() ) {
            $args = array(
                'post_type' => 'articles',
                'posts_per_page' => 20,
                'paged' => 1,
                'post_status' => array('publish', 'pending', 'draft'),
                'author' => get_current_user_id(),
                'orderby' => 'date',
                'order' => 'DESC'
            );

            $news_query = new WP_Query( $args );

            if ( $news_query->have_posts() ) :
                while ( $news_query->have_posts() ) : $news_query->the_post();
                    get_template_part( 'template-parts/content', 'article' );
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="col-span-full py-32 flex flex-col items-center justify-center text-center">
                    <p class="text-gray-400 font-bold uppercase tracking-widest">Vous n'avez créé aucun article</p>
                </div>
                <style>#load-more-sentinel { display: none; }</style>
                <?php
            endif;
        } else {
            echo '<script>window.location.href="' . home_url('/connexion') . '";</script>';
        }
        ?>
    </div>

    <!-- Infinite Scroll Sentinel -->
    <div id="load-more-sentinel" class="hidden h-20 flex items-center justify-center py-12">
        <div id="loading-spinner" class="hidden animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
</div>

<!-- Modal Structure -->
<div id="article-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm overflow-hidden">
    <div id="modal-content-wrapper" class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative shadow-2xl">
        <div id="modal-loader" class="p-20 flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <div id="modal-body-content"></div>
    </div>
</div>

<?php get_footer(); ?>
