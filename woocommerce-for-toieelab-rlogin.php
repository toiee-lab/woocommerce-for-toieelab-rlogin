<?php
/**
 * Plugin Name:     Woocommerce For toiee Lab rlogin
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          YOUR NAME HERE
 * Author URI:      YOUR SITE HERE
 * Text Domain:     woocommerce-for-toieelab-rlogin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Woocommerce_For_Toieelab_Rlogin
 */

global $wcr_rlogin;
$wcr_rlogin = new WooCommerce_for_toieeLab_RLogin();

class WooCommerce_for_toieeLab_RLogin {

    public $result;


    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'add_menu') );
        add_action( 'template_include', array($this, 'display_login'), 99 );

        /* 問い合わせ用の url を登録する */
        add_action( 'init', array( $this, 'add_route' ) );
        /* 問い合わせ用の url が使えるようにする */
        add_filter( 'query_vars', array( $this, 'routes_query_vars' ) );
        /* rewrite rule を更新する */
        register_activation_hook( __FILE__, array( $this, 'flush_application_rewrite_rules' ) );

        /* テンプレートが選ばれる前に実行 */
        add_action( 'template_redirect', array( $this, 'woocommerce_rlogin' ) );

	    /* ログアウト後は、トップページへ */
	    add_action('wp_logout',function (){
		    $url = site_url('', 'http');
		    wp_safe_redirect($url);
		    exit();
	    });

    }

    public function add_route(){
        add_rewrite_rule(
            '^woocommerce_rlogin/?',
            'index.php?woocommerce_rlogin=woocommerce_rlogin',
            'top'
        );
    }

    public function routes_query_vars( $query_vars ){
        $query_vars[] = 'woocommerce_rlogin';
        return $query_vars;
    }

    public function flush_application_rewrite_rules() {
        $this->add_route();
        flush_rewrite_rules();
    }

    public function woocommerce_rlogin()
    {
        global $wp_query;

        // woocommerce_login_check が指定されている場合、実行する
        $control_action = isset ($wp_query->query_vars['woocommerce_rlogin']) ? $wp_query->query_vars['woocommerce_rlogin'] : '';
        if ($control_action == 'woocommerce_rlogin'
            && isset($_POST['user']) && isset($_POST['password'])
        ) {

            // メインサイトに post する
            $wcr_login_url = get_option( 'wcr_login_url', '' ).'woocommerce_login_check/';
            $wcr_login_product_ids = get_option( 'wcr_login_product_ids', '');

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $wcr_login_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query([
                    'user'      => $_POST['user'],
                    'password'  => $_POST['password'],
                    'product'=> $wcr_login_product_ids
                ]),
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode( $response , true);

            // レスポンスから処理
            if( $data['status'] == 'success' ){
                $udata = $data['data'];

                // 既存ユーザーを検索
                $user_query = new WP_User_Query( array( 'meta_key' => 'wcrlogin_user_id', 'meta_value' => $udata['wcrlogin_user_id'] ) );
                if( empty( $user_query->results ) ){ //ユーザーがいなければ、作成する
                    //ユーザーを追加
                    $user_id = wp_create_user($udata['user_email'], wp_generate_password(), $udata['user_email']);
                    //メインサイトのIDを登録
                    update_user_meta( $user_id, 'wcrlogin_user_id', $udata['wcrlogin_user_id']);

                    var_dump($user_id);
                }
                else{
                    $user_id = $user_query->results[0]->ID;
                }

                //ユーザー情報を更新する
                $param = [
                    'ID' => $user_id,
                    'user_email' => $udata['user_email'],
                ];
                wp_update_user( $param );

                //ユーザーでログインしたことにする
                $user = get_user_by( 'id', $user_id );
                if( $user ) {
                    wp_set_current_user( $user_id, $user->user_login );
                    wp_set_auth_cookie( $user_id );
                    do_action( 'wp_login', $user->user_login, $user );
                }

                // リダイレクト
                //   - リダイレクトする
                $redirect_url = $_POST['redirect_url'];
                if( is_null( $redirect_url) ){
                    $redirect_url = bloginfo( 'siteurl' );
                }

                header("Location: ".$redirect_url);
                exit;
            }

            //login.php が呼び出される
            $this->result = $data;
        }
    }


    public function add_menu()
    {
        add_menu_page(
            'ログイン画面設定',
            'ログイン画面設定',
            'manage_options',
            'wcr_login_setting',
            function () {
                if( isset($_POST['do_action']) &&
                    isset( $_POST['_wpnonce'] ) &&
                    wp_verify_nonce( $_POST['_wpnonce'], 'update_options' )
                ){
                    update_option( 'wcr_login_url', rtrim( $_POST['wcr_login_url'], '/').'/' );
                    update_option( 'wcr_login_enable', $_POST['wcr_login_enable'] );
                    update_option( 'wcr_login_product_ids', $_POST['wcr_login_product_ids'] );
                }

                $wcr_login_url = get_option( 'wcr_login_url', '' );
                $wcr_login_product_ids = get_option( 'wcr_login_product_ids', '');
                $wcr_login_enable = get_option( 'wcr_login_enable', 0 );
                if($wcr_login_enable ){
                    $radio_enable = ' checked ';
                    $radio_disable = '';
                }
                else{
                    $radio_enable = '';
                    $radio_disable = ' checked ';
                }


                ?>
                <div class="wrap">
                    <h2>ログイン画面設定</h2>
                    <p>連動する先のメインサイト、商品ID、有効化を設定してください。</p>
                    <form method="post" action="<?php echo admin_url( 'admin.php?page=wcr_login_setting' ); ?>">
                        <?php wp_nonce_field('update_options'); ?>
                        <input type="hidden" name="do_action" value="update_msg">
                        <h3>メインサイトのURL</h3>
                        <p>WordPressのルートURL( https://example.com/ )を指定してください。指定されたサイトの /woocommerce_login_check/
                            に問い合わせします</p>
                        <input type="regular-text" name="wcr_login_url" value="<?php echo $wcr_login_url;?>">

                        <h3>商品ID</h3>
                        <p>認証したい商品ID（商品まとめのIDも可能）をカンマ区切り、半角で入力してください。
                        なお、ユーザーが存在すればログインさせたい場合は、 WCL_USER_CHECK を入れてください。</p>
                        <input type="regular-text" name="wcr_login_product_ids" value="<?php echo $wcr_login_product_ids;?>">

                        <h3>会員認証の設定</h3>
                        <label>有効にする <input type="radio" name="wcr_login_enable" value="1"<?php echo $radio_enable;?>></label><br>
                        <label>無効にする <input type="radio" name="wcr_login_enable" value="0"<?php echo $radio_disable;?>></label>


                        <?php submit_button( "説明文を保存する" ); ?>
                    </form>
                </div>
                <?php
            },
            'dashicons-admin-generic'
        );
    }

    public function display_login( $template ){

        /* 会員認証が有効で、ログインしていない場合 */
        $wcr_login_enable = get_option( 'wcr_login_enable', 0 );
        if( $wcr_login_enable && (!is_user_logged_in()) ){

            // テーマ内にファイルがあれば、使う
            $login_file_path = get_stylesheet_directory().'/wc-rlogin/login.php';
            if( file_exists( $login_file_path ) ) {
                return $login_file_path;
            }
            else{
                return plugin_dir_path( __FILE__ ).'wc-rlogin/login.php';
            }
        }

        return $template;
    }
}