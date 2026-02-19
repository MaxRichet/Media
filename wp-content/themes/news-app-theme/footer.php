</main>

<!-- Success Toast Notification -->
<div id="success-toast" class="fixed top-[-100px] left-1/2 -translate-x-1/2 z-[200] transition-all duration-500 ease-in-out pointer-events-none">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden min-w-[300px] border border-gray-100">
        <div class="px-8 py-5 flex items-center justify-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><polyline points="20 6 9 17 4 12"/></svg>
            <p class="text-green-600 text-sm font-normal">Votre article a bien été créé.</p>
        </div>
        <!-- Progress Bar -->
        <div class="h-[2px] bg-green-500 w-full origin-left" id="toast-progress"></div>
    </div>
</div>

<!-- Delete Success Toast Notification -->
<div id="delete-toast" class="fixed top-[-100px] left-1/2 -translate-x-1/2 z-[200] transition-all duration-500 ease-in-out pointer-events-none">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden min-w-[300px] border border-gray-100">
        <div class="px-8 py-5 flex items-center justify-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
            <p class="text-red-600 text-sm font-normal">L'article a bien été supprimé.</p>
        </div>
        <!-- Progress Bar -->
        <div class="h-[2px] bg-red-500 w-full origin-left" id="delete-toast-progress"></div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
