<?php
/**
 * Template Name: Inscription
 */
get_header(); ?>

<div class="min-h-screen flex items-center justify-center p-6 bg-gray-50">
    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl p-12 border border-gray-100">
        <h1 class="text-4xl font-black text-gray-900 mb-2 text-center flex items-center justify-center gap-3">
            Bienvenue ! 
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
        </h1>
        <p class="text-gray-500 mb-10 text-center">Créez votre compte pour rejoindre la communauté.</p>

        <?php
        if ( isset( $_GET['register_error'] ) ) {
            $error_code = sanitize_text_field( $_GET['register_error'] );
            echo '<div class="bg-red-50 border border-red-100 text-red-600 px-6 py-4 rounded-xl text-sm mb-8 text-center font-medium">Erreur lors de l\'inscription: ' . $error_code . '. Veuillez réessayer.</div>';
        }
        ?>

        <form method="post" enctype="multipart/form-data" class="space-y-8">
            <?php wp_nonce_field( 'news_app_register_action', 'news_app_register_nonce' ); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User photo dropzone -->
                <div class="md:row-span-2 flex flex-col items-center">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 text-center">Photo de profil</label>
                    <div id="dropzone" class="relative group cursor-pointer w-48 h-48 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-white hover:border-blue-400 transition-all flex flex-col items-center justify-center overflow-hidden">
                        <input type="file" name="user_photo" id="user_photo" class="hidden" accept="image/*">
                        <div id="dropzone-prompt" class="text-center p-4">
                            <span class="text-blue-400 mb-2 block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </span>
                            <span class="text-xs font-medium text-gray-400 group-hover:text-blue-500">Cliquez ou déposez votre photo</span>
                        </div>
                        <img id="photo-preview" src="" class="absolute inset-0 w-full h-full object-cover hidden">
                        <div id="remove-photo" class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full text-[10px] hidden hover:bg-red-600">✕</div>
                    </div>
                </div>

                <!-- Info fields -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Utilisateur (ID)</label>
                    <input type="text" name="username" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Prénom</label>
                    <input type="text" name="first_name" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pôle / Filière</label>
                    <select name="filiere" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                        <option value="Dev">Dev</option>
                        <option value="Design">Design</option>
                        <option value="Market">Market</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all hover:scale-[1.01] active:scale-100">
                S'inscrire gratuitement
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-500 font-medium">
            Déjà inscrit ? 
            <a href="<?php echo home_url( '/connexion' ); ?>" class="text-blue-600 font-bold hover:underline">Se connecter</a>
        </p>
    </div>
</div>

<script>
    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('user_photo');
    const prompt = document.getElementById('dropzone-prompt');
    const preview = document.getElementById('photo-preview');
    const removeBtn = document.getElementById('remove-photo');

    dropzone.addEventListener('click', () => input.click());

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) handleFile(file);
    });

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('dragleave', () => {
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        const file = e.dataTransfer.files[0];
        if (file) {
            input.files = e.dataTransfer.files;
            handleFile(file);
        }
    });

    function handleFile(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            prompt.classList.add('hidden');
            removeBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    removeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        input.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        prompt.classList.remove('hidden');
        removeBtn.classList.add('hidden');
    });
</script>

<?php get_footer(); ?>
