<?php
$post_id = get_the_ID();
$author_id = get_the_author_meta( 'ID' );
$author_photo = news_app_get_user_photo_url( $author_id );
$pole_list = get_the_terms( $post_id, 'poles' );
$upvotes = intval(get_post_meta( $post_id, '_upvotes', true ) ?: 0);
$downvotes = intval(get_post_meta( $post_id, '_downvotes', true ) ?: 0);
$user_voted = is_user_logged_in() ? get_user_meta( get_current_user_id(), '_voted_post_' . $post_id, true ) : '';
?>

<article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group h-full flex flex-col article-card cursor-pointer" data-id="<?php echo $post_id; ?>">
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="h-48 overflow-hidden bg-gray-200">
            <?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-full object-cover transform group-hover:scale-105 transition duration-500' ) ); ?>
        </div>
    <?php endif; ?>

    <div class="p-6 flex flex-col flex-grow">
        <!-- Profile, Name, Date Row -->
        <header class="flex items-center justify-between mb-2">
            <span class="text-xs text-gray-400"><?php echo get_the_date(); ?></span>
            <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-700"><?php the_author(); ?></span>
                <img src="<?php echo esc_url( $author_photo ); ?>" alt="Author" class="w-8 h-8 rounded-full border border-gray-100">
            </div>
        </header>

        <!-- Poles (Below Profile) -->
        <div class="flex flex-wrap gap-1 mb-4">
            <?php if ( $pole_list ) : foreach ( $pole_list as $p ) : ?>
                <span class="text-[10px] uppercase font-bold tracking-widest px-2 py-1 bg-blue-50 text-blue-600 rounded-md">
                    <?php echo esc_html( $p->name ); ?>
                </span>
            <?php endforeach; endif; ?>
        </div>

        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition line-clamp-2 min-h-[3.5rem]">
            <a href="<?php the_permalink(); ?>" class="modal-trigger" onclick="event.preventDefault();"><?php the_title(); ?></a>
        </h3>

        <div class="text-gray-600 text-sm line-clamp-3 mb-4">
            <?php the_excerpt(); ?>
        </div>

        <div class="mt-auto flex justify-end mb-[10px]">
            <button class="text-blue-600 text-[10px] uppercase tracking-tighter font-black hover:bg-blue-600 hover:text-white px-3 py-2 rounded-lg transition-all border border-blue-600 modal-trigger">LIRE LA SUITE</button>
        </div>

        <footer class="pt-4 border-t border-gray-50 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button class="vote-btn relative group transition-all duration-300 <?php echo $user_voted === 'upvote' ? 'voted-up text-blue-600' : 'text-gray-300 hover:text-blue-500'; ?>" data-id="<?php echo $post_id; ?>" data-type="upvote">
                    <div class="flex items-center space-x-1 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                        <svg class="arrow-svg transition-all duration-500" width="22" height="22" viewBox="0 0 24 24" fill="<?php echo $user_voted === 'upvote' ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3c.3 0 .6.1.8.4l7 8c.5.6.1 1.6-.7 1.6H15v7c0 1.1-.9 2-2 2h-2c-1.1 0-2-.9-2-2v-7H4.9c-.8 0-1.2-1-.7-1.6l7-8c.2-.3.5-.4.8-.4Z" />
                        </svg>
                        <span class="text-xs font-black upvote-count"><?php echo $upvotes; ?></span>
                    </div>
                </button>
                <button class="vote-btn relative group transition-all duration-300 <?php echo $user_voted === 'downvote' ? 'voted-down text-red-600' : 'text-gray-300 hover:text-red-500'; ?>" data-id="<?php echo $post_id; ?>" data-type="downvote">
                    <div class="flex items-center px-2 py-1 rounded-lg hover:bg-red-50 transition-colors">
                        <svg class="arrow-svg transition-all duration-500" width="22" height="22" viewBox="0 0 24 24" fill="<?php echo $user_voted === 'downvote' ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21c-.3 0-.6-.1-.8-.4l-7-8c-.5-.6-.1-1.6.7-1.6H9V5c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2v7h4.1c.8 0 1.2 1 .7 1.6l-7 8c-.2.3-.5.4-.8.4Z" />
                        </svg>
                    </div>
                </button>
            </div>
            
            <div class="flex items-center space-x-2">
                <?php 
                $can_delete = current_user_can( 'administrator' ) || (is_user_logged_in() && get_current_user_id() == $author_id);
                if ( $can_delete ) : ?>
                    <button class="delete-article-btn p-2 rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition-all duration-300" data-id="<?php echo $post_id; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    </button>
                <?php endif; ?>

                <?php $is_bookmarked = news_app_is_bookmarked($post_id); ?>
                <button class="bookmark-btn p-2 rounded-lg transition-all duration-300 <?php echo $is_bookmarked ? 'text-yellow-500 bg-yellow-50' : 'text-gray-300 hover:text-yellow-500 hover:bg-yellow-50'; ?>" data-id="<?php echo $post_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_bookmarked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
                    </svg>
                </button>
            </div>
        </footer>
    </div>
</article>
