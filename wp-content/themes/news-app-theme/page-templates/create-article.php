<?php
/**
 * Template Name: Créer un Article
 */
get_header(); ?>

<div class="w-full px-4 lg:px-8 py-12">
    <div class="mb-12">
        <h1 class="text-4xl font-black text-gray-900 mb-2">Créer un nouvel article</h1>
        <p class="text-gray-500 font-medium">Partagez votre expertise avec la communauté.</p>
    </div>

    <form id="create-article-form" class="space-y-10">
        <?php wp_nonce_field( 'news_app_nonce', 'news_app_nonce' ); ?>
        
        <!-- Header Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <div class="space-y-3">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Titre de l'article</label>
                <input type="text" id="post_title" name="post_title" placeholder="Ex: Les nouveautés de React 19..." class="w-full h-[58px] bg-gray-50 border border-gray-100 rounded-xl px-5 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div class="space-y-3">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Catégories</label>
                <div id="create-pole-filters" class="flex flex-wrap items-center gap-2">
                    <button type="button" data-pole="dev" class="create-pole-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">Dev</button>
                    <button type="button" data-pole="design" class="create-pole-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">Design</button>
                    <button type="button" data-pole="market" class="create-pole-btn px-5 py-3 rounded-xl text-sm font-bold bg-gray-50 text-gray-500 transition-all hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50">Market</button>
                    <input type="hidden" name="pole" id="selected-poles-input" value="">
                </div>
            </div>
        </div>

        <!-- Dynamic Content Blocks -->
        <div id="content-blocks" class="space-y-8">
            <!-- Initial Text Area -->
            <div class="block-wrapper group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                <label class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 block">Zone textuelle principale</label>
                <textarea id="main_content" name="content_blocks[]" placeholder="Rédigez votre contenu ici..." class="article-editor w-full min-h-[200px] bg-gray-50 border border-gray-50 rounded-2xl p-6 text-gray-800 text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none"></textarea>
            </div>
        </div>

        <!-- Add Button & Popup -->
        <div class="relative flex justify-center pt-4">
            <button type="button" id="add-block-trigger" class="flex items-center space-x-2 px-5 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Ajouter un bloc</span>
            </button>

            <!-- Popup Menu -->
            <div id="add-block-menu" class="hidden absolute top-full mt-4 left-1/2 -translate-x-1/2 w-64 bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden z-50 animate-in fade-in slide-in-from-top-4 duration-200">
                <button type="button" data-type="text" class="add-type-btn w-full flex items-center space-x-4 p-5 hover:bg-blue-50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 6.1H3"/><path d="M21 12.1H3"/><path d="M15.1 18.1H3"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-gray-900 text-sm">Texte</p>
                        <p class="text-xs text-gray-500">Ajouter un paragraphe</p>
                    </div>
                </button>
                <button type="button" data-type="image" class="add-type-btn w-full flex items-center space-x-4 p-5 border-t border-gray-50 hover:bg-blue-50 transition-colors group">
                    <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-gray-900 text-sm">Image</p>
                        <p class="text-xs text-gray-500">Glisser-déposer une photo</p>
                    </div>
                </button>
            </div>
        </div>

        <div class="pt-12 border-t border-gray-100 flex items-center justify-end">
            <div id="form-error-message" class="text-red-600 text-sm font-bold mr-[10px] hidden">les champs doivent être remplis</div>
            <button type="submit" class="px-5 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">
                Publier l'article
            </button>
        </div>
    </form>
</div>

<style>
    .article-editor a { color: #2563eb; text-decoration: underline; font-weight: 600; }
    #create-article-form input::placeholder, #create-article-form textarea::placeholder { color: #9ca3af; font-weight: 500; }
</style>

<?php get_footer(); ?>
