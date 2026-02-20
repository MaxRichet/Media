<?php
/**
 * Plugin Name: News App Core
 * Description: Backend foundation for the News App, including CPT, Taxonomies, and User Meta.
 * Version: 1.0.0
 * Author: Gemini CLI Architect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Structure (CPT & Taxonomie)
 */
function news_app_register_cpt_articles() {
	$labels = array(
		'name'               => 'Articles',
		'singular_name'      => 'Article',
		'menu_name'          => 'Articles App',
		'name_admin_bar'     => 'Article',
		'add_new'            => 'Ajouter un Article',
		'add_new_item'       => 'Ajouter un nouvel Article',
		'new_item'           => 'Nouvel Article',
		'edit_item'          => "Modifier l'Article",
		'view_item'          => "Voir l'Article",
		'all_items'          => 'Tous les Articles',
		'search_items'       => 'Rechercher des Articles',
		'parent_item_colon'  => 'Articles Parents:',
		'not_found'          => 'Aucun article trouvé.',
		'not_found_in_trash' => 'Aucun article trouvé dans la corbeille.'
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'articles' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-welcome-write-blog',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields' ), // No comments
		'show_in_rest'       => true,
	);

	register_post_type( 'articles', $args );
}
add_action( 'init', 'news_app_register_cpt_articles' );

function news_app_register_taxonomy_poles() {
	$labels = array(
		'name'              => 'Pôles',
		'singular_name'     => 'Pôle',
		'search_items'      => 'Rechercher des Pôles',
		'all_items'         => 'Tous les Pôles',
		'parent_item'       => 'Pôle Parent',
		'parent_item_colon' => 'Pôle Parent:',
		'edit_item'         => 'Modifier le Pôle',
		'update_item'       => 'Mettre à jour le Pôle',
		'add_new_item'      => 'Ajouter un nouveau Pôle',
		'new_item_name'     => 'Nom du nouveau Pôle',
		'menu_name'         => 'Pôles',
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'poles' ),
		'show_in_rest'      => true,
	);

	register_taxonomy( 'poles', array( 'articles' ), $args );

	// Default terms
	if ( ! term_exists( 'Dev', 'poles' ) ) wp_insert_term( 'Dev', 'poles' );
	if ( ! term_exists( 'Design', 'poles' ) ) wp_insert_term( 'Design', 'poles' );
	if ( ! term_exists( 'Market', 'poles' ) ) wp_insert_term( 'Market', 'poles' );
}
add_action( 'init', 'news_app_register_taxonomy_poles' );


/**
 * 2. Discussion (Commentaires Désactivés)
 */
function news_app_disable_comments_on_articles( $open, $post_id ) {
	$post = get_post( $post_id );
	if ( $post->post_type == 'articles' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'news_app_disable_comments_on_articles', 10, 2 );
add_filter( 'pings_open', 'news_app_disable_comments_on_articles', 10, 2 );


/**
 * 3. Users (User Meta)
 */
function news_app_user_profile_fields( $user ) { ?>
	<h3>Informations de Pôle</h3>
	<table class="form-table">
		<tr>
			<th><label for="filiere">Filière</label></th>
			<td>
				<input type="text" name="filiere" id="filiere" value="<?php echo esc_attr( get_the_author_meta( 'filiere', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Entrez votre pôle (ex: Dev, Design, Market).</span>
			</td>
		</tr>
		<tr>
			<th><label for="photo_url">Photo de profil (URL)</label></th>
			<td>
				<input type="url" name="photo_url" id="photo_url" value="<?php echo esc_attr( get_the_author_meta( 'photo_url', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description">Entrez l'URL de votre photo de profil.</span>
			</td>
		</tr>
	</table>
<?php }
add_action( 'show_user_profile', 'news_app_user_profile_fields' );
add_action( 'edit_user_profile', 'news_app_user_profile_fields' );

function news_app_save_user_profile_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) return false;
	update_user_meta( $user_id, 'filiere', $_POST['filiere'] );
	update_user_meta( $user_id, 'photo_url', $_POST['photo_url'] );
}
add_action( 'personal_options_update', 'news_app_save_user_profile_fields' );
add_action( 'edit_user_profile_update', 'news_app_save_user_profile_fields' );


/**
 * 4. Droits (Capabilities)
 */
function news_app_add_media_capabilities() {
	$roles = array( 'contributor', 'subscriber' );
	foreach ( $roles as $role_name ) {
		$role = get_role( $role_name );
		if ( $role && ! $role->has_cap( 'upload_files' ) ) {
			$role->add_cap( 'upload_files' );
		}
	}
}
add_action( 'admin_init', 'news_app_add_media_capabilities' );


/**
 * 5. Sécurité (Media Library Isolation)
 */
function news_app_restrict_media_library( $query ) {
	if ( ! is_admin() ) return;
	$user_id = get_current_user_id();
	if ( ! current_user_can( 'administrator' ) && $user_id ) {
		$query['author'] = $user_id;
	}
	return $query;
}
add_filter( 'ajax_query_attachments_args', 'news_app_restrict_media_library' );

function news_app_restrict_media_list_view( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( $query->get( 'post_type' ) !== 'attachment' ) return;

	$user_id = get_current_user_id();
	if ( ! current_user_can( 'administrator' ) && $user_id ) {
		$query->set( 'author', $user_id );
	}
}
add_action( 'pre_get_posts', 'news_app_restrict_media_list_view' );


/**
 * 6. Marketing (Post Meta)
 */
function news_app_add_tracking_meta_box() {
	add_meta_box(
		'news_app_tracking_scripts',
		'Tracking Scripts (Hotjar / Analytics)',
		'news_app_render_tracking_meta_box',
		'articles',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'news_app_add_tracking_meta_box' );

function news_app_render_tracking_meta_box( $post ) {
	$value = get_post_meta( $post->ID, '_tracking_scripts', true );
	?>
	<label for="news_app_tracking_scripts_field">Scripts à insérer :</label>
	<textarea name="news_app_tracking_scripts_field" id="news_app_tracking_scripts_field" style="width:100%; height:150px;"><?php echo esc_textarea( $value ); ?></textarea>
	<p class="description">Le code sera inséré avant la fermeture du body.</p>
	<?php
}

function news_app_save_tracking_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( isset( $_POST['news_app_tracking_scripts_field'] ) ) {
		// Caution: raw insertion for scripts. Ensure user has appropriate permissions.
		update_post_meta( $post_id, '_tracking_scripts', $_POST['news_app_tracking_scripts_field'] );
	}
}
add_action( 'save_post', 'news_app_save_tracking_meta_box' );

function news_app_inject_tracking_scripts() {
	if ( is_singular( 'articles' ) ) {
		$scripts = get_post_meta( get_the_ID(), '_tracking_scripts', true );
		if ( ! empty( $scripts ) ) {
			echo $scripts;
		}
	}
}
add_action( 'wp_footer', 'news_app_inject_tracking_scripts' );


/**
 * 7. Sécurité Avancée
 */

// Désactiver l'édition de fichiers via l'admin (Thèmes & Plugins)
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Masquer la version de WordPress
function news_app_hide_wp_version() {
	return '';
}
add_filter( 'the_generator', 'news_app_hide_wp_version' );
remove_action( 'wp_head', 'wp_generator' );

// Désactiver l'API XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );

// Supprimer les liens superflus du header (RSD, WLW, Shortlinks)
function news_app_cleanup_header() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
}
add_action( 'init', 'news_app_cleanup_header' );
