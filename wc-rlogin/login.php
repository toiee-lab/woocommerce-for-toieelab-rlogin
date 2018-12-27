<?php
/**
 * Created by PhpStorm.
 * User: takame
 * Date: 2018-12-26
 * Time: 18:56
 */


$description_login = get_option( 'wcr_login_description', null);
$redirect_url = ( isset($_POST['redirect_url']) && filter_var($_POST['redirect_url'], FILTER_VALIDATE_URL))
    ? $_POST['redirect_url'] : (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

global $wcr_rlogin;
$res = $wcr_rlogin->result;

$user = (isset($_POST['user'])) ? $_POST['user'] : '';

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

        <?php
            if( isset($res) && $res['status'] == 'error' ) { // エラーなら
                $err_msg = '';

                switch( $res['kind'] ){
                    case 'user_not_found':
                        $err_msg = 'ユーザーが見つかりません';
                        break;
                    case 'password_not_match':
                        $err_msg = 'パスワードが違います';
                        break;
                    case 'not_access':
                        $err_msg = 'アクセス権がありません';
                        break;
                    default:
                        $err_msg = '予期せぬエラーです';

                }
                ?>
                <div class="uk-alert-danger" uk-alert>
                    <p><?php echo $err_msg; ?></p>
                </div>
        <?php
            }
        ?>

        <form class="uk-form-stacked" method="post" action="<?php echo bloginfo( 'siteurl' ).'/woocommerce_rlogin/' ?>">
            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">ユーザー</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="user" name="user" type="text" placeholder="email or username" value="<?php echo $user;?>">
                </div>
            </div>

            <div class="uk-margin">
                <label class="uk-form-label" for="form-stacked-text">パスワード</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="password" name="password" type="password" >
                </div>
            </div>

            <div class="uk-margin">
                    <input class="uk-button uk-button-primary" id="form-stacked-text" type="submit" value="ログイン">
            </div>

            <input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>">

        </form>
    </div>
</div>
</body>
</html>

