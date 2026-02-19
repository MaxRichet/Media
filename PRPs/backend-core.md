# PRP: Backend Core Features - News App

## Goal
L'objectif est de mettre en place les fondations backend pour l'application d'actualité, incluant la structure des contenus (sans commentaires), la gestion enrichie des utilisateurs, les permissions d'upload, la sécurité de la bibliothèque média et les outils de tracking marketing.

## WP Architecture

### 1. Structure (CPT & Taxonomie)
- **Custom Post Type**: `articles`
  - *Supports*: `title`, `editor`, `thumbnail`, `excerpt`, `author`, `custom-fields`. (Note: `comments` is explicitly excluded).
  - *Public*: true.
  - *Has Archive*: true.
  - *Menu Icon*: `dashicons-welcome-write-blog`.
- **Taxonomie**: `poles` (liée à `articles`)
  - *Hiérarchique*: true.
  - *Default Terms*: `Dev`, `Design`, `Market`.

### 2. Discussion (Commentaires)
- **Action**: Désactiver globalement les commentaires pour le type `articles`.
- **Hooks**: 
  - `comments_open` filter: retourner false.
  - `pings_open` filter: retourner false.

### 3. Users (User Meta)
- **Fields**:
  - `filiere`: Texte simple pour identifier le pôle d'appartenance de l'utilisateur.
  - `photo_url`: URL pour une image de profil personnalisée.
- **Hooks**:
  - `show_user_profile` / `edit_user_profile`: Affichage des champs.
  - `personal_options_update` / `edit_user_profile_update`: Sauvegarde des données.

### 4. Droits (Capabilities)
- **Action**: Donner le droit `upload_files` aux rôles `contributor` et `subscriber`.
- **Méthode**: Exécution via `admin_init` (vérifier si le rôle possède déjà la capacité pour éviter les écritures DB redondantes).

### 5. Sécurité (Media Library Isolation)
- **Logique**: Restreindre l'affichage des médias à l'auteur actuel dans l'admin WordPress.
- **Hooks**:
  - `ajax_query_attachments_args`: Pour la vue "grille" (Modal Media).
  - `pre_get_posts`: Pour la vue "liste" (upload.php).
- **Condition**: Si l'utilisateur n'est pas `administrator`.

### 6. Marketing (Post Meta)
- **Field**: `tracking_scripts`
- **Type**: Textarea (permet l'insertion de code JS/HTML).
- **Affichage**: Metabox dédiée sur l'écran d'édition des `articles`.
- **Sécurité**: `wp_kses_post` ou `stripslashes` lors de la sauvegarde (selon le besoin d'exécution brute).

## Files to Modify
Pour garantir la portabilité et la séparation des responsabilités, ces modifications seront implémentées dans un plugin de "Core" plutôt que dans le thème :
- **Nouveau fichier**: `./wp-content/plugins/news-app-core/news-app-core.php`

## Validation
1. **Structure**: Vérifier la présence du menu "Articles" (CPT) et de la taxonomie "Pôles" dans l'admin.
2. **Commentaires**: Vérifier que la section "Commentaires" est absente sur l'écran d'édition et sur le front-end pour les `articles`.
3. **Users**: Modifier un profil utilisateur et vérifier que les champs `filiere` et `photo_url` persistent.
4. **Rights**: Se connecter avec un compte `Contributor` et tenter d'uploader une image via Médias > Ajouter.
5. **Sécurité**: Uploader une image avec "User A" et vérifier qu'elle n'est pas visible par "User B" dans la bibliothèque.
6. **Marketing**: Ajouter un script fictif dans la metabox d'un article et vérifier sa présence en base de données.
