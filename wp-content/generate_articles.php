<?php
$poles_names = ['Dev', 'Design', 'Market'];
$user = get_user_by('login', 'test_contributor');
if (!$user) {
    echo "Utilisateur non trouvé.\n";
    return;
}
$author_id = $user->ID;

$titles = [
    'Dev' => [
        'Les nouveautés de React 19', "Pourquoi Rust gagne du terrain", 'Optimiser ses requêtes SQL', 
        'Introduction à TypeScript', "Le futur de l'IA dans le code", 'Architecture Microservices',
        'Docker : Trucs et astuces', 'Kubernetes pour les débutants', 'Clean Code : Principes de base',
        "L'importance des tests unitaires", 'GraphQL vs REST', 'Performance Web en 2026',
        'Sécurité des API', 'Le langage Go en entreprise', 'CI/CD avec GitHub Actions',
        'Debugging avancé en JS', 'Frameworks CSS : Lequel choisir ?', 'Node.js et le multi-threading',
        'Développement Mobile avec Flutter', "WebAssembly : Pourquoi s'y intéresser"
    ],
    'Design' => [
        'Les tendances UI/UX pour 2026', "L'importance de l'accessibilité", 'Design System : Par où commencer',
        'Psychologie des couleurs', 'Typographie et lisibilité', "L'essor du Neumorphism",
        'Figma : Nouveaux raccourcis', 'Design de services publics', 'UX Writing : Le pouvoir des mots',
        'Design émotionnel', "L'impact de la 3D dans le web", 'Micro-interactions réussies',
        'Design inclusif', 'Audit UX : Méthodologie', 'Prototypes haute fidélité',
        "L'IA au service du designer", 'Branding et identité visuelle', 'Design Responsive vs Adaptatif',
        "L'importance du vide en design", 'Storytelling visuel'
    ],
    'Market' => [
        'Stratégies Growth Hacking', "L'avenir du SEO avec l'IA", 'Marketing d\'influence en 2026',
        'Optimiser son taux de conversion', "L'art du Copywriting", 'Réseaux sociaux : Nouveaux algorithmes',
        'E-mailing : Toujours efficace ?', 'Analyse de données marketing', 'Publicité programmatique',
        'Marketing automation', 'Le pouvoir du contenu vidéo', 'Personal Branding pour entrepreneurs',
        'Stratégie de prix efficace', 'Fidélisation client', 'Inbound vs Outbound Marketing',
        'Le podcast comme outil marketing', 'Vendre sur les marketplaces', 'Community Management avancé',
        "L'éthique dans le marketing", 'Web 3.0 et Marketing'
    ]
];

$lorem = "Le développement de solutions innovantes nécessite une approche holistique et une compréhension approfondie des besoins utilisateurs. En intégrant des méthodologies agiles, les équipes peuvent pivoter rapidement et s'adapter aux changements constants du marché technologique. L'importance de la collaboration interdisciplinaire ne peut être sous-estimée, car elle permet de fusionner des perspectives variées pour créer des produits robustes et évolutifs. Dans cet article, nous explorerons les différentes facettes de cette thématique, en mettant l'accent sur les meilleures pratiques et les pièges à éviter. La recherche constante d'excellence technique et esthétique guide chaque étape du processus créatif. En conclusion, rester à la pointe de l'innovation demande de la curiosité, de la rigueur et une volonté d'apprendre continuellement.";

foreach ($poles_names as $pole_name) {
    $term = get_term_by('name', $pole_name, 'poles');
    if (!$term) {
        $term_id = wp_insert_term($pole_name, 'poles')['term_id'];
    } else {
        $term_id = $term->term_id;
    }

    foreach ($titles[$pole_name] as $title) {
        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => str_repeat($lorem . "\n\n", 5), // Approx 300 words
            'post_status'  => 'publish',
            'post_author'  => $author_id,
            'post_type'    => 'articles'
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            wp_set_object_terms($post_id, (int)$term_id, 'poles');
            echo "Article créé : $title (Pôle: $pole_name)\n";
        }
    }
}
