<?php
/**
 * Template Name: Connexion
 */
get_header(); ?>

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-10 border border-gray-100">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2 text-center flex items-center justify-center gap-2">
            Bon retour ! 
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-yellow-500"><path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v0"/><path d="M14 10V4a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/></svg>
        </h1>
        <p class="text-gray-500 mb-8 text-center text-sm">Identifiez-vous pour accéder à vos articles et favoris.</p>

        <?php
        if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) {
            echo '<div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm mb-6 text-center">Identifiants incorrects. Veuillez réessayer.</div>';
        }

        wp_login_form( array(
            'echo' => true,
            'redirect' => home_url(),
            'form_id' => 'loginform',
            'label_username' => __( 'Utilisateur' ),
            'label_password' => __( 'Mot de passe' ),
            'label_remember' => __( 'Se souvenir de moi' ),
            'label_log_in' => __( 'Se connecter' ),
            'id_username' => 'user_login',
            'id_password' => 'user_pass',
            'id_remember' => 'rememberme',
            'id_submit' => 'wp-submit',
            'remember' => true,
            'value_username' => '',
            'value_remember' => false
        ) );
        ?>

        <style>
            #loginform label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
            #loginform input[type="text"], #loginform input[type="password"] { 
                width: 100%; border-radius: 0.75rem; background-color: #F9FAFB; border: 1px solid #E5E7EB; padding: 0.625rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem; 
            }
            #loginform input[type="text"]:focus, #loginform input[type="password"]:focus { outline: 2px solid #2563EB; border-color: transparent; }
            #wp-submit { 
                width: 100%; background-color: #2563EB; color: white; border-radius: 0.75rem; padding: 0.75rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s; border: none;
            }
            #wp-submit:hover { background-color: #1D4ED8; }
            .login-remember { margin-bottom: 1.25rem; display: flex; items-center: center; gap: 0.5rem; }
        </style>

        <p class="mt-8 text-center text-sm text-gray-500">
            Pas encore de compte ? 
            <a href="<?php echo home_url( '/inscription' ); ?>" class="text-blue-600 font-semibold hover:underline">S'inscrire</a>
        </p>
    </div>
</div>

<?php get_footer(); ?>
