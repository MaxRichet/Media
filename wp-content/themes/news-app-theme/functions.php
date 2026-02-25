<?php
/**
 * News App Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Auto-setup Pages
require_once get_template_directory() . '/inc/setup-pages.php';

// Theme Support
function news_app_theme_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'news_app_theme_setup' );

// Enqueue styles and scripts
function news_app_theme_scripts() {
	// Tailwind via CDN
	wp_enqueue_script( 'tailwind-cdn', 'https://cdn.tailwindcss.com' );
	
	// Main JS for AJAX, Modal, Infinite Scroll
	wp_enqueue_script( 'news-app-main', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true );
	wp_localize_script( 'news-app-main', 'newsApp', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'news_app_nonce' ),
		'isLoggedIn' => is_user_logged_in(),
		'loginUrl' => home_url( '/connexion' )
	));
}
add_action( 'wp_enqueue_scripts', 'news_app_theme_scripts' );

/**
 * Filter to restrict search to post titles only and apply custom ranking
 */
function news_app_search_by_title_only( $search, $wp_query ) {
	if ( empty( $search ) || ! $wp_query->is_search() ) return $search;

	global $wpdb;
	$q = $wp_query->query_vars;
	$search_term = $q['s'];

	// Strictly filter: Only show if the title contains the exact string sequence
	$search = $wpdb->prepare(
		" AND ({$wpdb->posts}.post_title LIKE %s)",
		'%' . $wpdb->esc_like( $search_term ) . '%'
	);

	return $search;
}

/**
 * Filter to rank: 1. Exact word match, 2. Exact string match
 */
function news_app_search_orderby_custom( $orderby, $wp_query ) {
	if ( empty( $wp_query->query_vars['s'] ) || ! $wp_query->is_search() ) return $orderby;

	global $wpdb;
	$search_term = $wp_query->query_vars['s'];
	$esc_search_term = $wpdb->esc_like( $search_term );
	
	$custom_orderby = $wpdb->prepare(
		"CASE 
			WHEN ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title = %s) THEN 0
			WHEN {$wpdb->posts}.post_title LIKE %s THEN 1 
			ELSE 2 
		END ASC, ",
		$search_term . ' %',        // Start of title
		'% ' . $search_term . ' %', // Middle of title
		'% ' . $search_term,        // End of title
		$search_term,               // Exact title
		'%' . $esc_search_term . '%' // Exact sequence anywhere
	);

	return $custom_orderby . $orderby;
}

/**
 * AJAX: Load More Posts (Infinite Scroll & Filtering)
 */
function news_app_ajax_load_posts() {
	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$poles_str = isset($_POST['pole']) ? sanitize_text_field($_POST['pole']) : '';
	$search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
	$orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
	$is_bookmarks = isset($_POST['is_bookmarks']) ? ($_POST['is_bookmarks'] === 'true') : false;
	$is_my_articles = isset($_POST['is_my_articles']) ? ($_POST['is_my_articles'] === 'true') : false;

	$args = array(
		'post_type'      => 'articles',
		'posts_per_page' => 20,
		'paged'          => $page,
		'post_status'    => 'publish',
	);

	if ( $is_bookmarks && is_user_logged_in() ) {
		$bookmarks = get_user_meta( get_current_user_id(), '_bookmarked_posts', true ) ?: array(-1);
		$args['post__in'] = $bookmarks;
	}

	if ( $is_my_articles && is_user_logged_in() ) {
		$args['author'] = get_current_user_id();
		$args['post_status'] = array('publish', 'pending', 'draft');
	}

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
		add_filter( 'posts_search', 'news_app_search_by_title_only', 10, 2 );
		add_filter( 'posts_orderby', 'news_app_search_orderby_custom', 10, 2 );
	}

	// Multi-pôle filtering
	if ( ! empty( $poles_str ) ) {
		$poles_array = explode( ',', $poles_str );
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'poles',
				'field' => 'slug',
				'terms' => $poles_array,
				'operator' => 'IN',
			),
		);
	}

	// Dynamic Sorting Logic
	$is_upvote = ( strpos($orderby, 'upvotes') !== false );
	$secondary = str_replace('upvotes_', '', $orderby);

	if ( $is_upvote ) {
		$args['meta_query'] = array(
			'relation' => 'OR',
			'vote_clause' => array(
				'key' => '_upvotes',
				'type' => 'NUMERIC',
				'compare' => 'EXISTS',
			),
			'no_vote_clause' => array(
				'key' => '_upvotes',
				'compare' => 'NOT EXISTS',
			),
		);
		
		$sort_array = array( 'vote_clause' => 'DESC' );
		
		if ( $secondary === 'title_asc' ) {
			$sort_array['title'] = 'ASC';
		} elseif ( $secondary === 'title_desc' ) {
			$sort_array['title'] = 'DESC';
		} else {
			$sort_array['date'] = 'DESC';
		}
		
		$args['orderby'] = $sort_array;
	} else {
		switch ( $orderby ) {
			case 'title_asc':
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
				break;
			case 'title_desc':
				$args['orderby'] = 'title';
				$args['order'] = 'DESC';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order'] = 'DESC';
				break;
		}
	}

	$query = new WP_Query( $args );

	if ( ! empty( $search ) ) {
		remove_filter( 'posts_search', 'news_app_search_by_title_only', 10 );
		remove_filter( 'posts_orderby', 'news_app_search_orderby_custom', 10 );
	}

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content', 'article' );
		}
	}
	wp_reset_postdata();
	wp_die();
}
add_action( 'wp_ajax_load_posts', 'news_app_ajax_load_posts' );
add_action( 'wp_ajax_nopriv_load_posts', 'news_app_ajax_load_posts' );

/**
 * AJAX: Get Single Article for Modal
 */
function news_app_ajax_get_article() {
	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	if ( ! $post_id ) wp_die();

	$post = get_post( $post_id );
	if ( ! $post || $post->post_type !== 'articles' ) wp_die();

	$author_id = $post->post_author;
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_photo = news_app_get_user_photo_url( $author_id );
	$date = get_the_date( '', $post_id );
	$pole_list = get_the_terms( $post_id, 'poles' );
	$upvotes = get_post_meta( $post_id, '_upvotes', true ) ?: 0;
	$user_voted = is_user_logged_in() ? get_user_meta( get_current_user_id(), '_voted_post_' . $post_id, true ) : '';
	$can_delete = current_user_can( 'administrator' ) || (is_user_logged_in() && get_current_user_id() == $author_id);

	ob_start(); ?>
	<div class="flex flex-col h-full max-h-[90vh]">
		<!-- Sticky Header -->
		<header class="p-8 border-b bg-white sticky top-0 z-10 rounded-t-3xl">
			<div class="flex items-center justify-between mb-4">
				<div class="flex items-center space-x-4">
					<img src="<?php echo esc_url( $author_photo ); ?>" class="w-12 h-12 rounded-full border">
					<div>
						<h4 class="font-bold text-gray-900"><?php echo esc_html( $author_name ); ?></h4>
						<span class="text-sm text-gray-500"><?php echo esc_html( $date ); ?></span>
					</div>
				</div>
				<button id="close-modal" class="text-gray-400 hover:text-gray-600 text-3xl font-light">&times;</button>
			</div>
			
			<div class="flex flex-wrap gap-2 mb-4">
				<?php if ( $pole_list ) : foreach ( $pole_list as $p ) : ?>
					<span class="text-[10px] uppercase font-bold tracking-widest px-2 py-1 bg-blue-50 text-blue-600 rounded-md">
						<?php echo esc_html( $p->name ); ?>
					</span>
				<?php endforeach; endif; ?>
			</div>

			<h2 class="text-3xl font-black text-gray-900 leading-tight"><?php echo esc_html( $post->post_title ); ?></h2>
		</header>

		<!-- Scrollable Content -->
		<div class="p-8 overflow-y-auto flex-1 bg-white">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<div class="mb-8 rounded-2xl overflow-hidden">
					<?php echo get_the_post_thumbnail( $post_id, 'large', array( 'class' => 'w-full object-cover' ) ); ?>
				</div>
			<?php endif; ?>

			<div class="prose prose-blue max-w-none text-gray-800 text-lg leading-relaxed">
				<?php echo wpautop( $post->post_content ); ?>
			</div>
		</div>

		<!-- Sticky Footer (Voting) -->
		<footer class="p-6 border-t bg-gray-50 sticky bottom-0 z-10 rounded-b-3xl flex items-center justify-between">
			<div class="flex items-center space-x-8">
				<button class="vote-btn flex items-center space-x-2 transition <?php echo $user_voted === 'upvote' ? 'voted-up text-blue-600' : 'text-gray-300 hover:text-blue-500'; ?>" data-id="<?php echo $post_id; ?>" data-type="upvote">
					<svg class="arrow-svg transition-all duration-500" width="28" height="28" viewBox="0 0 24 24" fill="<?php echo $user_voted === 'upvote' ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 3c.3 0 .6.1.8.4l7 8c.5.6.1 1.6-.7 1.6H15v7c0 1.1-.9 2-2 2h-2c-1.1 0-2-.9-2-2v-7H4.9c-.8 0-1.2-1-.7-1.6l7-8c.2-.3.5-.4.8-.4Z" />
					</svg>
					<span class="text-lg font-black upvote-count"><?php echo $upvotes; ?></span>
				</button>
				<button class="vote-btn flex items-center space-x-2 transition <?php echo $user_voted === 'downvote' ? 'voted-down text-red-600' : 'text-gray-300 hover:text-red-500'; ?>" data-id="<?php echo $post_id; ?>" data-type="downvote">
					<svg class="arrow-svg transition-all duration-500" width="28" height="28" viewBox="0 0 24 24" fill="<?php echo $user_voted === 'downvote' ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M12 21c-.3 0-.6-.1-.8-.4l-7-8c-.5-.6-.1-1.6.7-1.6H9V5c0-1.1.9-2 2-2h2c1.1 0 2 .9 2 2v7h4.1c.8 0 1.2 1 .7 1.6l-7 8c-.2.3-.5.4-.8.4Z" />
					</svg>
				</button>
			</div>

			<?php $is_bookmarked = news_app_is_bookmarked($post_id); ?>
			<div class="flex items-center space-x-2">
				<?php if ( $can_delete ) : ?>
					<button class="delete-article-btn p-3 rounded-xl transition-all duration-300 text-gray-300 hover:text-red-500 hover:bg-red-50" data-id="<?php echo $post_id; ?>" title="Supprimer l'article">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M3 6h18m-2 0v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6m3 0V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2m-6 3v7m4-7v7"/>
						</svg>
					</button>
				<?php endif; ?>
				
				<button class="bookmark-btn p-3 rounded-xl transition-all duration-300 <?php echo $is_bookmarked ? 'text-yellow-500 bg-yellow-50' : 'text-gray-300 hover:text-yellow-500 hover:bg-yellow-50'; ?>" data-id="<?php echo $post_id; ?>">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="<?php echo $is_bookmarked ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
					</svg>
				</button>
			</div>
		</footer>
	</div>
	<?php
	echo ob_get_clean();
	wp_die();
}
add_action( 'wp_ajax_get_article', 'news_app_ajax_get_article' );
add_action( 'wp_ajax_nopriv_get_article', 'news_app_ajax_get_article' );

/**
 * AJAX: Vote Logic (Toggle & Exclusive)
 */
function news_app_ajax_vote() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Not logged in' ) );
	}

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$new_type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : ''; // 'upvote' or 'downvote'
	$user_id = get_current_user_id();

	if ( ! $post_id || ! in_array( $new_type, ['upvote', 'downvote'] ) ) wp_die();

	$user_voted = get_user_meta( $user_id, '_voted_post_' . $post_id, true ); // Stores 'upvote' or 'downvote'
	$upvotes = intval( get_post_meta( $post_id, '_upvotes', true ) ?: 0 );
	$downvotes = intval( get_post_meta( $post_id, '_downvotes', true ) ?: 0 );

	if ( $user_voted === $new_type ) {
		// Toggle OFF: User clicked the same button again
		delete_user_meta( $user_id, '_voted_post_' . $post_id );
		if ( $new_type === 'upvote' ) $upvotes--; else $downvotes--;
		$current_voted = '';
	} else {
		// Toggle ON or Change:
		if ( $user_voted === 'upvote' ) $upvotes--;
		if ( $user_voted === 'downvote' ) $downvotes--;

		if ( $new_type === 'upvote' ) $upvotes++;
		if ( $new_type === 'downvote' ) $downvotes++;

		update_user_meta( $user_id, '_voted_post_' . $post_id, $new_type );
		$current_voted = $new_type;
	}

	update_post_meta( $post_id, '_upvotes', max(0, $upvotes) );
	update_post_meta( $post_id, '_downvotes', max(0, $downvotes) );

	wp_send_json_success( array( 
		'upvotes' => $upvotes, 
		'downvotes' => $downvotes, 
		'user_voted' => $current_voted 
	) );
}
add_action( 'wp_ajax_vote', 'news_app_ajax_vote' );

/**
 * AJAX: Toggle Bookmark
 */
function news_app_ajax_toggle_bookmark() {
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => 'Not logged in' ) );
	}

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	$user_id = get_current_user_id();

	if ( ! $post_id ) wp_die();

	$bookmarks = get_user_meta( $user_id, '_bookmarked_posts', true ) ?: array();
	
	if ( ($key = array_search($post_id, $bookmarks)) !== false ) {
		unset($bookmarks[$key]);
		$status = 'removed';
	} else {
		$bookmarks[] = $post_id;
		$status = 'added';
	}

	update_user_meta( $user_id, '_bookmarked_posts', array_values($bookmarks) );

	wp_send_json_success( array( 'status' => $status ) );
}
add_action( 'wp_ajax_toggle_bookmark', 'news_app_ajax_toggle_bookmark' );

/**
 * Helper: Check if post is bookmarked
 */
function news_app_is_bookmarked( $post_id ) {
	if ( ! is_user_logged_in() ) return false;
	$bookmarks = get_user_meta( get_current_user_id(), '_bookmarked_posts', true ) ?: array();
	return in_array( $post_id, $bookmarks );
}

/**
 * AJAX: Create Article
 */
function news_app_ajax_create_article() {
	check_ajax_referer( 'news_app_nonce', 'news_app_nonce' );

	if ( ! is_user_logged_in() || ! ( current_user_can( 'contributor' ) || current_user_can( 'administrator' ) ) ) {
		wp_send_json_error( array( 'message' => 'Non autorisé' ) );
	}

	$title = sanitize_text_field( $_POST['post_title'] );
	$poles_str = sanitize_text_field( $_POST['pole'] );
	$blocks = $_POST['content_blocks']; // Array of strings (HTML or text)
	
	$content = '';
	if ( ! empty( $blocks ) ) {
		foreach ($blocks as $block) {
			$content .= $block . "\n\n";
		}
	}

	$post_id = wp_insert_post( array(
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_author'  => get_current_user_id(),
		'post_type'    => 'articles'
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Erreur lors de la création' ) );
	}

	if ( ! empty( $poles_str ) ) {
		$poles_array = explode( ',', $poles_str );
		wp_set_object_terms( $post_id, $poles_array, 'poles' );
	}

	wp_send_json_success( array( 'redirect' => home_url( '/mes-articles' ) ) );
}
add_action( 'wp_ajax_create_article', 'news_app_ajax_create_article' );

/**
 * AJAX: Load Users
 */
function news_app_ajax_load_users() {
	if ( ! current_user_can( 'administrator' ) ) wp_die();

	$role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';
	$order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC';

	$args = array(
		'orderby' => 'display_name',
		'order'   => $order,
	);

	if ( ! empty( $role ) ) {
		$args['role'] = $role;
	}

	$users = get_users( $args );

	if ( ! empty( $users ) ) {
		foreach ( $users as $user ) {
			$photo = news_app_get_user_photo_url( $user->ID );
			$roles = $user->roles;
			$role_label = 'User';
			if ( in_array( 'administrator', $roles ) ) $role_label = 'Admin';
			elseif ( in_array( 'contributor', $roles ) ) $role_label = 'Editeur';
			?>
			                        <div class="user-row group flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-lg hover:border-blue-100 transition-all cursor-pointer mb-3" data-id="<?php echo $user->ID; ?>">
			                                <div class="flex items-center space-x-6 flex-1">
			                                        <img src="<?php echo esc_url( $photo ); ?>" class="w-12 h-12 rounded-full border border-gray-100 object-cover">
			                                        <div class="grid grid-cols-5 gap-4 flex-1">
			                                                <div>
			                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nom</p>
			                                                        <p class="text-sm font-bold text-gray-900"><?php echo esc_html( $user->last_name ?: '-' ); ?></p>
			                                                </div>
			                                                <div>
			                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Prénom</p>
			                                                        <p class="text-sm font-bold text-gray-900"><?php echo esc_html( $user->first_name ?: '-' ); ?></p>
			                                                </div>
			                                                <div>
			                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Filière</p>
			                                                        <p class="text-sm font-bold text-gray-900"><?php echo esc_html( get_user_meta( $user->ID, 'filiere', true ) ?: '-' ); ?></p>
			                                                </div>
			                                                <div class="col-span-1">
			                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</p>
			                                                        <p class="text-sm text-gray-600 truncate"><?php echo esc_html( $user->user_email ?: '-' ); ?></p>
			                                                </div>
			                                                <div>
			                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rôle</p>
			                                                        <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase bg-gray-50 text-gray-500"><?php echo $role_label; ?></span>
			                                                </div>
			                                        </div>
			                                </div>
			
				<button class="flex items-center space-x-2 text-blue-600 font-bold text-xs group-hover:translate-x-1 transition-transform pl-4">
					<span>Voir toutes les informations</span>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
				</button>
			</div>
			<?php
		}
	} else {
		echo '<div class="py-20 text-center text-gray-400 font-bold uppercase">Aucun utilisateur trouvé.</div>';
	}
	wp_die();
}
add_action( 'wp_ajax_load_users', 'news_app_ajax_load_users' );

/**
 * AJAX: Get User Details for Modal
 */
function news_app_ajax_get_user_details() {
	if ( ! current_user_can( 'administrator' ) ) wp_die();

	$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
	if ( ! $user_id ) wp_die();

	$user = get_userdata( $user_id );
	$photo = news_app_get_user_photo_url( $user_id );
	$filiere = get_user_meta( $user_id, 'filiere', true ) ?: '-';
	$current_role = reset($user->roles);

	ob_start(); ?>
	<div class="p-8">
		<header class="flex items-center justify-between mb-10">
			<h2 class="text-3xl font-black text-gray-900">Détails de l'utilisateur</h2>
			<button id="close-modal" class="text-gray-400 hover:text-gray-600 text-3xl font-light">&times;</button>
		</header>

		<div class="flex items-start space-x-8 mb-10">
			<img src="<?php echo esc_url( $photo ); ?>" class="w-32 h-32 rounded-3xl border-4 border-gray-50 shadow-sm object-cover">
			<div class="space-y-6 flex-1">
				<div class="grid grid-cols-2 gap-6">
					<div>
						<label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-1">Nom complet</label>
						<p class="text-sm font-bold text-gray-900"><?php echo esc_html( ($user->first_name || $user->last_name) ? $user->first_name . ' ' . $user->last_name : '-' ); ?></p>
					</div>
					<div>
						<label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-1">Email</label>
						<p class="text-sm font-medium text-gray-700"><?php echo esc_html( $user->user_email ?: '-' ); ?></p>
					</div>
					<div>
						<label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-1">Filière / Pôle</label>
						<p class="text-sm font-medium text-gray-700"><?php echo esc_html( get_user_meta( $user->ID, 'filiere', true ) ?: '-' ); ?></p>
					</div>
					<div>
						<label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-1">Date d'inscription</label>
						<p class="text-sm font-medium text-gray-700"><?php echo $user->user_registered ? date('d/m/Y', strtotime($user->user_registered)) : '-'; ?></p>
					</div>
				</div>

				<div class="pt-6 border-t border-gray-50">
					<label class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 block">Changer le rôle</label>
					<select id="update-user-role-select" class="w-full h-[58px] bg-gray-50 border border-gray-100 rounded-xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
						<option value="subscriber" <?php selected($current_role, 'subscriber'); ?>>User (Subscriber)</option>
						<option value="contributor" <?php selected($current_role, 'contributor'); ?>>Editeur (Contributor)</option>
						<option value="administrator" <?php selected($current_role, 'administrator'); ?>>Admin (Administrator)</option>
					</select>
				</div>
			</div>
		</div>

		<footer class="flex justify-end space-x-4 pt-8 border-t border-gray-100">
			<button id="close-modal" class="px-8 py-3 bg-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">Annuler</button>
			<button id="save-user-role" data-id="<?php echo $user_id; ?>" class="px-8 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Sauvegarder</button>
		</footer>
	</div>
	<?php
	echo ob_get_clean();
	wp_die();
}
add_action( 'wp_ajax_get_user_details', 'news_app_ajax_get_user_details' );

/**
 * AJAX: Delete Article
 */
function news_app_ajax_delete_article() {
	check_ajax_referer( 'news_app_nonce', 'nonce' );

	$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	if ( ! $post_id ) wp_die();

	$post = get_post( $post_id );
	if ( ! $post ) wp_send_json_error( array( 'message' => 'Article non trouvé' ) );

	$can_delete = current_user_can( 'administrator' ) || (is_user_logged_in() && get_current_user_id() == $post->post_author);

	if ( ! $can_delete ) {
		wp_send_json_error( array( 'message' => 'Non autorisé' ) );
	}

	$result = wp_delete_post( $post_id, true ); // Force delete

	if ( $result ) {
		wp_send_json_success();
	} else {
		wp_send_json_error( array( 'message' => 'Erreur lors de la suppression' ) );
	}
}
add_action( 'wp_ajax_delete_article', 'news_app_ajax_delete_article' );
/**
 * AJAX: Update User Role
 */
function news_app_ajax_update_user_role() {
	if ( ! current_user_can( 'administrator' ) ) wp_die();

	$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
	$new_role = isset($_POST['role']) ? sanitize_text_field($_POST['role']) : '';

	if ( ! $user_id || ! in_array( $new_role, ['subscriber', 'contributor', 'administrator'] ) ) wp_die();

	$user = new WP_User( $user_id );
	$user->set_role( $new_role );

	wp_send_json_success();
}
add_action( 'wp_ajax_update_user_role', 'news_app_ajax_update_user_role' );

/**
 * 2. User Photo Upload Logic
 */
function news_app_handle_user_photo_upload( $user_id ) {
	if ( ! empty( $_FILES['user_photo']['name'] ) ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$attachment_id = media_handle_upload( 'user_photo', 0 ); // 0 means no post parent

		if ( ! is_wp_error( $attachment_id ) ) {
			update_user_meta( $user_id, 'user_photo_id', $attachment_id );
		}
	}
}

/**
 * 3. Security & Redirections
 */
function news_app_security_redirections() {
	if ( is_page( 'create-article' ) || is_page( 'mes-articles' ) ) {
		if ( ! is_user_logged_in() || ! ( current_user_can( 'contributor' ) || current_user_can( 'administrator' ) ) ) {
			wp_redirect( home_url( '/connexion' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'news_app_security_redirections' );

/**
 * 4. Helper to get user photo URL
 */
function news_app_get_user_photo_url( $user_id ) {
	$photo_id = get_user_meta( $user_id, 'user_photo_id', true );
	if ( $photo_id ) {
		$url = wp_get_attachment_url( $photo_id );
		if ( $url ) return $url;
	}
	return 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( get_userdata($user_id)->user_email ) ) ) . '?d=mp&s=150';
}

/**
 * 5. Handle Registration
 */
function news_app_handle_registration() {
	if ( isset( $_POST['news_app_register_nonce'] ) && wp_verify_nonce( $_POST['news_app_register_nonce'], 'news_app_register_action' ) ) {
		$username = sanitize_user( $_POST['username'] );
		$email = sanitize_email( $_POST['email'] );
		$password = $_POST['password'];
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$filiere = sanitize_text_field( $_POST['filiere'] );

		$user_id = wp_create_user( $username, $password, $email );

		if ( ! is_wp_error( $user_id ) ) {
			wp_update_user( array( 'ID' => $user_id, 'first_name' => $first_name ) );
			update_user_meta( $user_id, 'filiere', $filiere );
			news_app_handle_user_photo_upload( $user_id );
			
			// Auto login
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
			wp_redirect( home_url() );
			exit;
		} else {
			wp_redirect( add_query_arg( 'register_error', $user_id->get_error_code(), home_url( '/inscription' ) ) );
			exit;
		}
	}
}
add_action( 'init', 'news_app_handle_registration' );
