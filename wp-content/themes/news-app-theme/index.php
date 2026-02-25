<?php
/**
 * Main template file.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title(); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="container mx-auto mt-10">
        <h1 class="text-4xl font-bold text-center">Bienvenue sur News App - Staging</h1>
    </div>
    <?php wp_footer(); ?>
</body>
</html>
