# PRP: Frontend Setup - News App Theme

## Goal
L'objectif est de créer un thème WordPress sur mesure ('news-app-theme') avec une interface moderne utilisant Tailwind CSS. Le thème doit inclure une barre latérale dynamique gérant les profils utilisateurs, une navigation conditionnelle selon les rôles, et un système robuste d'upload de photos de profil ainsi que des fonctionnalités de filtrage d'articles.

## WP Architecture

### 1. Theme Structure
- **Location**: `./wp-content/themes/news-app-theme/`
- **Core Files**:
  - `style.css`: Définition du thème.
  - `functions.php`: Logique backend (upload, security, redirections, enqueue).
  - `header.php`: Inclusion de Tailwind CSS (CDN), début du wrapper, Sidebar fixe.
  - `footer.php`: Fermeture du wrapper, scripts JS.
  - `index.php`: Loop principale avec système de filtrage (Pôles, Date, Recherche).

### 2. Sidebar Logic (header.php)
- **Profile Section**:
  - Utilise `wp_get_current_user()`.
  - Image : Récupère l'ID du média via le meta `user_photo_id`. Si absent, affiche un placeholder SVG/Gris.
  - Nom : `user_firstname` ou "Visiteur".
- **Dynamic Menu**:
  - *Public*: Accueil (Lien vers index).
  - *Logged In*: Signets (Lien vers `/bookmarks`).
  - *Contributor*: Mes Articles (Lien vers `/mes-articles`), Créer (Lien vers `/create-article`).
  - *Admin*: Tous les articles (WP Admin), Tous les users (WP Admin).
- **Footer Sidebar**:
  - Séparateur `<hr>`.
  - Conditionnel `is_user_logged_in()`.

### 3. Home Logic & Filters (index.php)
- **Recherche Avancée**: Hook sur `pre_get_posts` pour inclure la recherche par nom d'auteur dans la requête principale si `s` est présent.
- **Filtres Pôles**: Liens utilisant les query vars `?pole=dev`, `?pole=design`, etc.
- **Tri**: Query var `?order=desc|asc`.

### 4. Photo & Upload (functions.php & Registration)
- **Transition**: Remplacement du champ texte `photo_url` par un système d'upload lié à la bibliothèque média.
- **Backend**: Fonction utilisant `media_handle_upload` déclenchée lors de la soumission du formulaire de profil ou d'inscription. Liaison de l'ID de l'attachment à l'utilisateur via `update_user_meta( $user_id, 'user_photo_id', $attachment_id )`.

### 5. Page Templates (`page-templates/`)
- **Login (`login.php`)**: Formulaire utilisant `wp_login_form()`.
- **Register (`register.php`)**: 
  - Champs : Nom, Prénom, Email, Mot de passe, Filière.
  - **Dropzone JS**: Zone interactive pour l'upload de photo avec prévisualisation via `FileReader API`.

### 6. Security & Redirections
- **Hook**: `template_redirect`.
- **Logique**: Si un utilisateur accède à `/create-article` sans être `contributor` ou `administrator`, redirection vers la page de connexion.

## Files to Modify/Create
- `wp-content/themes/news-app-theme/style.css`
- `wp-content/themes/news-app-theme/functions.php`
- `wp-content/themes/news-app-theme/header.php`
- `wp-content/themes/news-app-theme/footer.php`
- `wp-content/themes/news-app-theme/index.php`
- `wp-content/themes/news-app-theme/page-templates/login.php`
- `wp-content/themes/news-app-theme/page-templates/register.php`

## Validation
1. **Sidebar**: Vérifier l'affichage correct du profil et du menu selon le rôle (Admin vs Contributor vs Visiteur).
2. **Filtrage**: Tester les 3 filtres de pôles et la barre de recherche (recherche par auteur incluse).
3. **Upload**: Créer un compte avec une photo via la Dropzone et vérifier qu'elle s'affiche dans la sidebar.
4. **Sécurité**: Tenter d'accéder à la page de création sans être connecté et vérifier la redirection.
