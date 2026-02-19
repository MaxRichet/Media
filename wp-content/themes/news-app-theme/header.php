<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-gray-100 flex' ); ?>>

<?php
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$user_name = $is_logged_in ? ( $current_user->first_name ?: $current_user->display_name ) : 'Visiteur';
$user_photo_url = $is_logged_in ? news_app_get_user_photo_url( $current_user->ID ) : 'https://www.gravatar.com/avatar/0?d=mp&s=150';
?>

<!-- Sidebar -->
<aside class="w-64 fixed inset-y-0 left-0 bg-white shadow-lg flex flex-col p-6 z-50">
    <!-- Profile -->
    <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 mb-3 border-2 border-gray-100">
            <img src="<?php echo esc_url( $user_photo_url ); ?>" alt="Profile" class="w-full h-full object-cover">
        </div>
        <h2 class="text-lg font-semibold text-gray-800"><?php echo esc_html( $user_name ); ?></h2>
        <?php if ( $is_logged_in && ( $filiere = get_user_meta( $current_user->ID, 'filiere', true ) ) ) : ?>
            <p class="text-xs text-gray-500 uppercase tracking-wide"><?php echo esc_html( $filiere ); ?></p>
        <?php endif; ?>
    </div>

    <!-- Menu -->
    <nav class="flex-1 space-y-2">
        <?php
        $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
        $is_home = is_front_page() || is_home();
        ?>
        <a href="<?php echo home_url(); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo $is_home ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
            <span class="w-5 h-5 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            <span class="font-medium">Accueil</span>
        </a>

        <?php if ( $is_logged_in ) : ?>
            <a href="<?php echo home_url( '/bookmarks' ); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo strpos($current_url, '/bookmarks') !== false ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                <span class="w-5 h-5 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>
                </span>
                <span class="font-medium">Signets</span>
            </a>

            <?php if ( current_user_can( 'contributor' ) || current_user_can( 'administrator' ) ) : ?>
                <a href="<?php echo home_url( '/mes-articles' ); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo strpos($current_url, '/mes-articles') !== false ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                    </span>
                    <span class="font-medium">Mes articles</span>
                </a>
                <a href="<?php echo home_url( '/create-article' ); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo strpos($current_url, '/create-article') !== false ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                    </span>
                    <span class="font-medium">Créer un article</span>
                </a>
            <?php endif; ?>

            <?php if ( current_user_can( 'administrator' ) ) : 
                $users_page = get_page_by_path('all-users');
                $users_url = $users_page ? get_permalink($users_page->ID) : home_url('/all-users');
                $is_users = strpos($current_url, '/all-users') !== false;
            ?>
                <div class="pt-4 pb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider px-4">Admin</div>
                <a href="<?php echo esc_url($users_url); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo $is_users ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <span class="font-medium">Tous les users</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="mt-auto pt-4 border-t border-gray-200">
        <?php if ( $is_logged_in ) : ?>
            <a href="<?php echo wp_logout_url( home_url() ); ?>" class="flex items-center space-x-3 px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition">
                <span class="w-5 h-5 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </span>
                <span class="font-medium">Se déconnecter</span>
            </a>
        <?php else : 
            $login_page = get_page_by_path('connexion');
            $register_page = get_page_by_path('inscription');
            $login_url = $login_page ? get_permalink($login_page->ID) : home_url('/connexion');
            $register_url = $register_page ? get_permalink($register_page->ID) : home_url('/inscription');
            $is_login = strpos($current_url, '/connexion') !== false;
            $is_register = strpos($current_url, '/inscription') !== false;
        ?>
            <a href="<?php echo esc_url( $login_url ); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition mb-1 <?php echo $is_login ? 'bg-blue-50 text-blue-600' : 'text-blue-600 hover:bg-blue-50'; ?>">
                <span class="w-5 h-5 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <span class="font-medium">Se connecter</span>
            </a>
            <a href="<?php echo esc_url( $register_url ); ?>" class="flex items-center space-x-3 px-4 py-2 rounded-lg transition <?php echo $is_register ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'; ?>">
                <span class="w-5 h-5 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                </span>
                <span class="font-medium">Créer un compte</span>
            </a>
        <?php endif; ?>
    </div>
</aside>

<!-- Main Content Area Wrapper -->
<main class="ml-64 flex-1">
