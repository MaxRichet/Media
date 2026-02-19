<?php
/**
 * Template Name: Tous les utilisateurs
 */
if ( ! current_user_can( 'administrator' ) ) {
    wp_redirect( home_url() );
    exit;
}
get_header(); ?>

<!-- Sticky Header Wrapper -->
<div class="sticky top-0 z-40 w-full bg-gray-100/80 backdrop-blur-md px-4 lg:px-8 py-6">
    <header class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col xl:flex-row items-center justify-between gap-6">
        <!-- Role Filters -->
        <div id="user-role-filters" class="flex flex-wrap items-center gap-2">
            <button data-role="" class="role-btn px-5 py-3 rounded-xl text-sm font-bold bg-blue-600 text-white shadow-lg shadow-blue-100 transition-all hover:bg-blue-700">Tous</button>
            <button data-role="administrator" class="role-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">Admin</button>
            <button data-role="contributor" class="role-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">Editeur</button>
            <button data-role="subscriber" class="role-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">User</button>
        </div>

        <!-- Sorting Toggle -->
        <div class="flex items-center gap-3 w-full xl:w-auto">
            <button id="toggle-user-order" data-order="ASC" class="flex-1 md:flex-none px-5 py-3 rounded-xl text-sm font-bold transition-all border bg-gray-50 border-gray-100 text-gray-500 hover:bg-gray-100">
                Trier par Nom : A - Z
            </button>
        </div>
    </header>
</div>

<div class="w-full px-4 lg:px-8 pb-12">
    <div class="mb-8">
        <h1 class="text-3xl font-black text-gray-900">Tous les utilisateurs</h1>
        <p class="text-gray-500 text-sm">Visualisez et gérez les permissions de tous les membres de la plateforme.</p>
    </div>

    <!-- Users List Container -->
    <div id="users-container" class="space-y-4">
        <?php
        // Initial Load handled by AJAX logic for consistency, or call the same function
        $users = get_users( array( 'orderby' => 'display_name', 'order' => 'ASC' ) );
        if ( ! empty( $users ) ) {
            foreach ( $users as $user ) {
                $photo = news_app_get_user_photo_url( $user->ID );
                $roles = $user->roles;
                $role_label = 'User';
                if ( in_array( 'administrator', $roles ) ) $role_label = 'Admin';
                elseif ( in_array( 'contributor', $roles ) ) $role_label = 'Editeur';
                ?>
                <div class="user-row group flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-lg hover:border-blue-100 transition-all cursor-pointer" data-id="<?php echo $user->ID; ?>">
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
                            <div>
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
        }
        ?>
    </div>
</div>

<!-- Modal Logic shared via main.js -->
<div id="article-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black bg-opacity-60 backdrop-blur-sm overflow-hidden">
    <div id="modal-content-wrapper" class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-y-auto relative shadow-2xl">
        <div id="modal-loader" class="p-20 flex items-center justify-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
        <div id="modal-body-content"></div>
    </div>
</div>

<?php get_footer(); ?>
