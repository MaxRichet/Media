jQuery(document).ready(function($) {
    let loading = false;
    let noMorePosts = false;
    const container = $('#articles-container');
    const sentinel = document.getElementById('load-more-sentinel');
    const modal = $('#article-modal');
    const modalContent = $('#modal-body-content');
    const modalLoader = $('#modal-loader');
    const liveSearch = $('#live-search');
    const sortOrder = $('#sort-order');
    const poleBtns = $('.pole-btn');
    const toggleUpvotes = $('#toggle-upvotes');

    let selectedPoles = [];

    /**
     * Infinite Scroll
     */
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading && !noMorePosts) {
            loadMorePosts();
        }
    }, { threshold: 0.1 });

    if (sentinel) {
        observer.observe(sentinel);
    }

    function loadMorePosts() {
        loading = true;
        const spinner = $('#loading-spinner');
        spinner.removeClass('hidden');
        $('#load-more-sentinel').removeClass('hidden');
        
        const currentPage = parseInt(container.attr('data-page'));
        const search = liveSearch.val();
        const orderby = container.attr('data-orderby');
        const pole = selectedPoles.join(',');
        const isBookmarks = container.attr('data-bookmarks') === 'true';
        const isMyArticles = container.attr('data-my-articles') === 'true';

        setTimeout(() => {
            $.ajax({
                url: newsApp.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_posts',
                    page: currentPage + 1,
                    pole: pole,
                    search: search,
                    orderby: orderby,
                    is_bookmarks: isBookmarks,
                    is_my_articles: isMyArticles,
                    nonce: newsApp.nonce
                },
                success: function(response) {
                    if (response.trim() === '') {
                        noMorePosts = true;
                        $('#load-more-sentinel').html('<p class="text-gray-400 text-sm font-bold uppercase tracking-widest">Tous les articles sont affichés</p>');
                    } else {
                        container.append(response);
                        container.attr('data-page', currentPage + 1);
                        $('#load-more-sentinel').removeClass('hidden');
                    }
                    loading = false;
                    spinner.addClass('hidden');
                },
                error: function() {
                    loading = false;
                    spinner.addClass('hidden');
                }
            });
        }, 500);
    }

    /**
     * Multi-Pôle Selection Logic (Filters)
     */
    $(document).on('click', '.pole-btn', function() {
        const btn = $(this);
        const pole = btn.data('pole');

        if (pole === 'all') {
            selectedPoles = [];
        } else {
            const index = selectedPoles.indexOf(pole);
            if (index > -1) {
                selectedPoles.splice(index, 1);
            } else {
                selectedPoles.push(pole);
            }

            if (selectedPoles.length === 3) {
                selectedPoles = [];
            }
        }

        updatePoleButtons();
        refreshArticles();
    });

    function updatePoleButtons() {
        poleBtns.removeClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700').addClass('bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50');
        
        if (selectedPoles.length === 0) {
            $('[data-pole="all"]').addClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700').removeClass('bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50');
        } else {
            selectedPoles.forEach(p => {
                $(`[data-pole="${p}"]`).addClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700').removeClass('bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50');
            });
        }
    }

    /**
     * Live Search & Filtering
     */
    let searchTimer;
    liveSearch.on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            refreshArticles();
        }, 400);
    });

    sortOrder.on('change', function() {
        refreshArticles();
    });

    toggleUpvotes.on('click', function() {
        $(this).toggleClass('bg-blue-50 border-blue-200 text-blue-600 bg-gray-50 border-gray-100 text-gray-500');
        refreshArticles();
    });

    function refreshArticles() {
        const search = liveSearch.val();
        const baseOrder = sortOrder.val();
        const isUpvote = toggleUpvotes.hasClass('text-blue-600');
        let orderby = isUpvote ? 'upvotes_' + baseOrder : baseOrder;
        const pole = selectedPoles.join(',');
        const isBookmarks = container.attr('data-bookmarks') === 'true';
        const isMyArticles = container.attr('data-my-articles') === 'true';

        container.attr('data-search', search);
        container.attr('data-orderby', orderby);
        container.attr('data-page', 1);
        noMorePosts = false;
        $('#load-more-sentinel').removeClass('hidden').html('<div id="loading-spinner" class="hidden animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>');

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_posts',
                page: 1,
                pole: pole,
                search: search,
                orderby: orderby,
                is_bookmarks: isBookmarks,
                is_my_articles: isMyArticles,
                nonce: newsApp.nonce
            },
            success: function(response) {
                if (response.trim() === '') {
                    const isBookmarks = container.attr('data-bookmarks') === 'true';
                    const isMyArticles = container.attr('data-my-articles') === 'true';
                    let msg = "Aucun article ne correspond à votre recherche.";
                    if (isBookmarks && search === '') msg = "Aucun article n'a été enregistré";
                    if (isMyArticles && search === '') msg = "Vous n'avez créé aucun article";
                    
                    container.html(`<div class="col-span-full py-40 text-center text-gray-400 font-bold uppercase tracking-widest bg-white rounded-3xl border border-dashed border-gray-200">${msg}</div>`);
                    noMorePosts = true;
                    $('#load-more-sentinel').addClass('hidden');
                } else {
                    container.html(response);
                }
            }
        });
    }

    /**
     * Modal Logic
     */
    $(document).on('click', '.article-card', function(e) {
        if ($(e.target).closest('.vote-btn').length || $(e.target).closest('.bookmark-btn').length || $(e.target).closest('.delete-article-btn').length || $(e.target).closest('a').length || $(e.target).hasClass('modal-trigger')) {
            if ($(e.target).hasClass('modal-trigger') || $(e.target).closest('.modal-trigger').length) {
                // proceed
            } else {
                return;
            }
        }
        const postID = $(this).data('id');
        openModal(postID);
    });

    function openModal(postID) {
        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        modalContent.empty();
        modalLoader.show();

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_article',
                post_id: postID,
                nonce: newsApp.nonce
            },
            success: function(response) {
                modalLoader.hide();
                modalContent.html(response);
            }
        });
    }

    $(document).on('click', '#article-modal', function(e) {
        if (e.target === this) closeModal();
    });

    $(document).on('click', '#close-modal', function() {
        closeModal();
    });

    function closeModal() {
        modal.addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    }

    /**
     * Voting Logic
     */
    $(document).on('click', '.vote-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!newsApp.isLoggedIn) {
            window.location.href = newsApp.loginUrl;
            return;
        }

        const btn = $(this);
        const postID = btn.data('id');
        const type = btn.data('type');

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'vote',
                post_id: postID,
                type: type,
                nonce: newsApp.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    const allUpBtns = $(`.vote-btn[data-id="${postID}"][data-type="upvote"]`);
                    const allDownBtns = $(`.vote-btn[data-id="${postID}"][data-type="downvote"]`);

                    allUpBtns.removeClass('text-blue-600').addClass('text-gray-300').find('svg').attr('fill', 'none');
                    allDownBtns.removeClass('text-red-600').addClass('text-gray-300').find('svg').attr('fill', 'none');

                    if (data.user_voted === 'upvote') {
                        allUpBtns.addClass('text-blue-600').removeClass('text-gray-300').find('svg').attr('fill', 'currentColor');
                    } else if (data.user_voted === 'downvote') {
                        allDownBtns.addClass('text-red-600').removeClass('text-gray-300').find('svg').attr('fill', 'currentColor');
                    }

                    $(`.vote-btn[data-id="${postID}"][data-type="upvote"] .upvote-count`).text(data.upvotes);
                }
            }
        });
    });

    /**
     * Bookmark Logic
     */
    $(document).on('click', '.bookmark-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!newsApp.isLoggedIn) {
            window.location.href = newsApp.loginUrl;
            return;
        }

        const btn = $(this);
        const postID = btn.data('id');

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_bookmark',
                post_id: postID,
                nonce: newsApp.nonce
            },
            success: function(response) {
                if (response.success) {
                    const status = response.data.status;
                    const allBookBtns = $(`.bookmark-btn[data-id="${postID}"]`);
                    
                    if (status === 'added') {
                        allBookBtns.addClass('text-yellow-500 bg-yellow-50').removeClass('text-gray-300');
                        allBookBtns.find('svg').attr('fill', 'currentColor');
                    } else {
                        allBookBtns.removeClass('text-yellow-500 bg-yellow-50').addClass('text-gray-300');
                        allBookBtns.find('svg').attr('fill', 'none');
                    }
                }
            }
        });
    });

    /**
     * Delete Article Logic (Admin Only)
     */
    $(document).on('click', '.delete-article-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const postID = $(this).data('id');
        const card = $(this).closest('.article-card');

        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        modalContent.html(`
            <div class="p-10 text-center">
                <h2 class="text-2xl font-black text-gray-900 mb-4">Êtes-vous sûr de vouloir supprimer cet article ?</h2>
                <p class="text-gray-500 mb-10">Cette action est irréversible et supprimera définitivement le contenu.</p>
                <div class="flex justify-center space-x-4">
                    <button id="cancel-delete" class="px-8 py-3 bg-gray-100 text-gray-500 rounded-xl text-sm font-bold hover:bg-gray-200 transition-all">Annuler</button>
                    <button id="confirm-delete" data-id="${postID}" class="px-8 py-3 bg-blue-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Supprimer</button>
                </div>
            </div>
        `);
        modalLoader.hide();

        $(document).one('click', '#confirm-delete', function() {
            const btn = $(this);
            btn.addClass('opacity-50 pointer-events-none').text('Suppression...');
            $.ajax({
                url: newsApp.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_article',
                    post_id: postID,
                    nonce: newsApp.nonce
                },
                success: function(response) {
                    if (response.success) {
                        closeModal();
                        card.fadeOut(400, function() { 
                            card.remove(); 
                            
                            // Show Delete Toast
                            const toast = $('#delete-toast');
                            const progress = $('#delete-toast-progress');
                            
                            toast.css('top', '40px');
                            progress.css('transition', 'none').css('width', '100%');
                            
                            setTimeout(() => {
                                progress.css('transition', 'width 3s linear').css('width', '0%');
                            }, 50);

                            setTimeout(() => {
                                toast.css('top', '-100px');
                            }, 3000);
                        });
                    }
                }
            });
        });

        $(document).one('click', '#cancel-delete', function() {
            closeModal();
        });
    });

    /**
     * Create Article Page Logic
     */
    const addBlockTrigger = $('#add-block-trigger');
    const addBlockMenu = $('#add-block-menu');
    const contentBlocksContainer = $('#content-blocks');
    let selectedCreatePoles = [];

    if (addBlockTrigger.length) {
        addBlockTrigger.on('click', function(e) {
            e.stopPropagation();
            addBlockMenu.toggleClass('hidden');
        });

        $(document).on('click', function() {
            addBlockMenu.addClass('hidden');
        });

        $('.add-type-btn').on('click', function() {
            const type = $(this).data('type');
            addBlock(type);
        });

        // Multi-category logic for creation
        $(document).on('click', '.create-pole-btn', function() {
            const btn = $(this);
            const pole = btn.data('pole');
            const index = selectedCreatePoles.indexOf(pole);

            if (index > -1) {
                selectedCreatePoles.splice(index, 1);
                btn.removeClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700 hover:text-white').addClass('bg-gray-50 text-gray-500 hover:bg-gray-200');
            } else {
                selectedCreatePoles.push(pole);
                btn.addClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700 hover:text-white').removeClass('bg-gray-50 text-gray-500 hover:bg-gray-200');
            }
            $('#selected-poles-input').val(selectedCreatePoles.join(','));
        });
    }

    function addBlock(type) {
        let html = '';
        if (type === 'text') {
            html = `
                <div class="block-wrapper group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm animate-in slide-in-from-bottom-4 duration-300">
                    <button type="button" class="remove-block absolute -right-3 -top-3 w-8 h-8 bg-red-500 text-white rounded-full hidden group-hover:flex items-center justify-center shadow-lg">✕</button>
                    <label class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 block">Bloc de texte</label>
                    <textarea name="content_blocks[]" placeholder="Continuez à rédiger..." class="article-editor w-full min-h-[150px] bg-gray-50 border border-gray-50 rounded-2xl p-6 text-gray-800 text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none"></textarea>
                </div>
            `;
        } else if (type === 'image') {
            const blockID = Date.now();
            html = `
                <div class="block-wrapper group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm animate-in slide-in-from-bottom-4 duration-300">
                    <button type="button" class="remove-block absolute -right-3 -top-3 w-8 h-8 bg-red-500 text-white rounded-full hidden group-hover:flex items-center justify-center shadow-lg">✕</button>
                    <label class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 block">Bloc Image</label>
                    <div id="dropzone-${blockID}" class="dropzone-area cursor-pointer w-full h-64 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 hover:bg-white hover:border-blue-400 transition-all flex flex-col items-center justify-center overflow-hidden relative">
                        <input type="file" class="hidden image-input" accept="image/*">
                        <div class="prompt text-center p-4">
                            <span class="text-blue-400 mb-2 block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </span>
                            <span class="text-xs font-medium text-gray-400">Glissez-déposez ou cliquez</span>
                        </div>
                        <img class="preview absolute inset-0 w-full h-full object-cover hidden">
                        <textarea name="content_blocks[]" class="hidden-content hidden"></textarea>
                    </div>
                </div>
            `;
        }
        contentBlocksContainer.append(html);
    }

    $(document).on('click', '.remove-block', function() {
        $(this).closest('.block-wrapper').remove();
    });

    $(document).on('click', '.dropzone-area', function() {
        $(this).find('.image-input').click();
    });

    $(document).on('change', '.image-input', function(e) {
        const file = e.target.files[0];
        if (file) handleImageBlock($(this).closest('.dropzone-area'), file);
    });

    function handleImageBlock(area, file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const base64 = e.target.result;
            area.find('.preview').attr('src', base64).removeClass('hidden');
            area.find('.prompt').addClass('hidden');
            area.find('.hidden-content').val(`<img src="${base64}" class="rounded-2xl my-8 w-full">`);
        };
        reader.readAsDataURL(file);
    }

    $('#create-article-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const title = $('#post_title').val().trim();
        const mainContent = $('#main_content').val().trim();
        const poles = $('#selected-poles-input').val();
        const errorMsg = $('#form-error-message');

        if (!title || !mainContent || !poles) {
            errorMsg.removeClass('hidden');
            return;
        }
        errorMsg.addClass('hidden');

        // Note: serialize includes the hidden 'news_app_nonce' field
        const data = form.serialize();
        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: data + '&action=create_article',
            success: function(response) {
                if (response.success) {
                    // Reset Form
                    form[0].reset();
                    // Clear dynamic blocks
                    contentBlocksContainer.find('.block-wrapper:not(:first-child)').remove();
                    // Clear categories
                    selectedCreatePoles = [];
                    $('.create-pole-btn').removeClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700 hover:text-white').addClass('bg-gray-50 text-gray-500 hover:bg-gray-200');
                    $('#selected-poles-input').val('');

                    // Show Toast
                    const toast = $('#success-toast');
                    const progress = $('#toast-progress');
                    
                    toast.css('top', '40px');
                    progress.css('transition', 'none').css('width', '100%');
                    
                    setTimeout(() => {
                        progress.css('transition', 'width 3s linear').css('width', '0%');
                    }, 50);

                    setTimeout(() => {
                        toast.css('top', '-100px');
                    }, 3000);
                }
            }
        });
    });

    /**
     * Tous les Utilisateurs Page Logic
     */
    const usersContainer = $('#users-container');
    let selectedUserRole = '';
    let currentUserOrder = 'ASC';

    if (usersContainer.length) {
        $(document).on('click', '.role-btn', function() {
            selectedUserRole = $(this).data('role');
            $('.role-btn').removeClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700').addClass('bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50');
            $(this).addClass('bg-blue-600 text-white shadow-lg shadow-blue-100 hover:bg-blue-700').removeClass('bg-gray-50 text-gray-500 hover:bg-gray-200 hover:text-gray-700 hover:shadow-lg hover:shadow-gray-200/50');
            refreshUsers();
        });

        $('#toggle-user-order').on('click', function() {
            currentUserOrder = currentUserOrder === 'ASC' ? 'DESC' : 'ASC';
            $(this).text(`Trier par Nom : ${currentUserOrder === 'ASC' ? 'A - Z' : 'Z - A'}`);
            refreshUsers();
        });

        $(document).on('click', '.user-row', function() {
            const userID = $(this).data('id');
            openUserModal(userID);
        });
    }

    function refreshUsers() {
        usersContainer.addClass('opacity-50 pointer-events-none');
        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_users',
                role: selectedUserRole,
                order: currentUserOrder,
                nonce: newsApp.nonce
            },
            success: function(response) {
                usersContainer.html(response).removeClass('opacity-50 pointer-events-none');
            }
        });
    }

    function openUserModal(userID) {
        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        modalContent.empty();
        modalLoader.show();

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_user_details',
                user_id: userID,
                nonce: newsApp.nonce
            },
            success: function(response) {
                modalLoader.hide();
                modalContent.html(response);
            }
        });
    }

    $(document).on('click', '#save-user-role', function() {
        const btn = $(this);
        const userID = btn.data('id');
        const newRole = $('#update-user-role-select').val();

        btn.addClass('opacity-50 pointer-events-none').text('Sauvegarde...');

        $.ajax({
            url: newsApp.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_user_role',
                user_id: userID,
                role: newRole,
                nonce: newsApp.nonce
            },
            success: function(response) {
                if (response.success) {
                    closeModal();
                    refreshUsers();
                } else {
                    alert('Erreur lors de la mise à jour.');
                    btn.removeClass('opacity-50 pointer-events-none').text('Sauvegarder');
                }
            }
        });
    });
});
