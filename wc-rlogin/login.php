<?php
/**
 * Created by PhpStorm.
 * User: takame
 * Date: 2018-12-26
 * Time: 18:56
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="profile" href="https://gmpg.org/xfn/11" />
    <title><?php echo wp_get_document_title(); ?></title>
    <?php
        wp_site_icon();
    ?>
    <!-- UIkit CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/css/uikit.min.css" />

    <!-- UIkit JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/js/uikit.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-rc.25/js/uikit-icons.min.js"></script>
    <style>
    </style>
</head>
<body>
<div class="uk-position-center">
    <div class="uk-card uk-card-default uk-card-body">
        <?php echo get_custom_logo(); ?>
        <h1 class="uk-h2"><?php echo bloginfo( 'name' ); ?></h1>
        <p class="uk-text-meta"><?php echo bloginfo( 'description' );?></p>

        <?php require_once( dirname( __FILE__ ).'/login_form.php' ); ?>

    </div>
</div>
</body>
</html>

