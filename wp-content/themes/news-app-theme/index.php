<?php
/**
 * Main template file for News App Theme.
 */

get_header(); ?>

<main class="flex-1 p-6 bg-gray-50">
    <!-- Header with Search & Filters -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-gray-800">Dernières Actualités</h2>
            
            <form role="search" method="get" class="flex gap-2" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <input type="search" 
                       class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm w-full md:w-64" 
                       placeholder="Rechercher un article ou auteur..." 
                       value="<?php echo get_search_query(); ?>" 
                       name="s" />
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm">
                    Rechercher
                </button>
            </form>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 mt-6">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" 
               class="px-4 py-2 rounded-full text-sm font-medium <?php echo !isset($_GET['pole']) ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm'; ?>">
                Tous
            </a>
            <?php 
            $poles = get_terms( array('taxonomy' => 'poles', 'hide_empty' => false) );
            foreach ( $poles as $pole ) : 
                $active = (isset($_GET['pole']) && $_GET['pole'] == $pole->slug);
            ?>
                <a href="<?php echo esc_url( add_query_arg( 'pole', $pole->slug, home_url( '/' ) ) ); ?>" 
                   class="px-4 py-2 rounded-full text-sm font-medium <?php echo $active ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm'; ?>">
                    <?php echo esc_html( $pole->name ); ?>
                </a>
            <?php endforeach; ?>

            <!-- Sorting -->
            <div class="ml-auto flex items-center gap-2">
                <span class="text-xs text-gray-500 uppercase font-semibold">Trier par :</span>
                <a href="<?php echo esc_url( add_query_arg( 'order', 'desc' ) ); ?>" class="text-sm <?php echo (!isset($_GET['order']) || $_GET['order'] == 'desc') ? 'font-bold text-blue-600' : 'text-gray-500'; ?>">Récent</a>
                <span class="text-gray-300">|</span>
                <a href="<?php echo esc_url( add_query_arg( 'order', 'asc' ) ); ?>" class="text-sm <?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'font-bold text-blue-600' : 'text-gray-500'; ?>">Ancien</a>
            </div>
        </div>
    </div>

    <!-- Articles Grid -->
    <?php if ( have_posts() ) : ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition border border-gray-100 flex flex-col">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="aspect-video overflow-hidden">
                            <?php the_post_thumbnail( 'medium_large', array( 'class' => 'w-full h-full object-cover transform hover:scale-105 transition duration-500' ) ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                            <?php
                            $post_poles = get_the_terms( get_the_ID(), 'poles' );
                            if ( $post_poles && ! is_wp_error( $post_poles ) ) :
                                foreach ( $post_poles as $p ) : ?>
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded bg-blue-50 text-blue-600">
                                        <?php echo esc_html( $p->name ); ?>
                                    </span>
                                <?php endforeach;
                            endif;
                            ?>
                            <span class="text-xs text-gray-400 ml-auto"><?php echo get_the_date(); ?></span>
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <div class="text-gray-600 text-sm line-clamp-3 mb-4 flex-1">
                            <?php the_excerpt(); ?>
                        </div>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-50 mt-auto">
                            <?php 
                            $author_id = get_the_author_meta('ID');
                            $photo_url = get_the_author_meta('photo_url', $author_id);
                            if ($photo_url) : ?>
                                <img src="<?php echo esc_url($photo_url); ?>" class="w-8 h-8 rounded-full object-cover border border-gray-200" alt="">
                            <?php else : ?>
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-[10px] text-gray-500 font-bold uppercase">
                                    <?php echo esc_html(substr(get_the_author_meta('display_name'), 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700"><?php the_author(); ?></span>
                                <span class="text-[10px] text-gray-400"><?php echo get_the_author_meta('filiere'); ?></span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            <?php
            echo paginate_links( array(
                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'total'        => $wp_query->max_num_pages,
                'current'      => max( 1, get_query_var( 'paged' ) ),
                'format'       => '?paged=%#%',
                'show_all'     => false,
                'type'         => 'plain',
                'end_size'     => 2,
                'mid_size'     => 1,
                'prev_next'    => true,
                'prev_text'    => '« Précédent',
                'next_text'    => 'Suivant »',
            ) );
            ?>
        </div>

    <?php else : ?>
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-gray-100">
            <div class="text-gray-300 mb-4">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2v4a2 2 0 002 2h4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Aucun article trouvé</h3>
            <p class="text-gray-500 mt-2">Essayez d'ajuster vos filtres ou votre recherche.</p>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
