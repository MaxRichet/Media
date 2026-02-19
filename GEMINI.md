# WordPress Blog Project - AI Orchestrated

## Project Overview
Projet d'application d'actualité pour 3 pôles (Market, Dev, Design).
Environnement WordPress dockerisé piloté par Gemini CLI.

### Stack & Architecture
- **WordPress**: Dernière version officielle.
- **Database**: MySQL 8.0.
- **Environment**: Docker Compose.
- **Custom Code Location**: `./wp-content/themes/my-agent-theme` et `./wp-content/plugins/`.

## AI Instructions (CRITICAL)
Tu es un agent de développement senior. Pour toute demande, tu dois utiliser l'un des deux workflows situés dans `.gemini/commands/` :
1. **PRP (Product Requirement Prompt)** : Pour toute nouvelle feature majeure ou création de structure (CPT, Nouveau Thème).
2. **EPCT (Explore, Plan, Code, Test)** : Pour les modifications mineures, le debug ou l'ajout de scripts (Hotjar, Analytics).

### Constraints
- Ne jamais modifier les fichiers dans `wp-admin` ou `wp-includes`.
- Toujours vérifier la compatibilité avec les WordPress Coding Standards (PHP).
- Le design doit être implémenté via un thème "Starter" vide en HTML/CSS pur pour un contrôle total.

## Commands
- Start: `docker-compose up -d`
- URL: `http://localhost:8080`
- WP-CLI (via Docker): `docker-compose exec wordpress wp [command]`