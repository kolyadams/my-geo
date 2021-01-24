<?php
/*
Plugin Name: My GEO
Plugin URI: http://страница_с_описанием_плагина_и_его_обновлений
Description: Краткое описание плагина.
Version: 1.0
Author: kms
Author URI: http://страница_автора_плагина
*/

/**
 * Check if WooCommerce is active
 **/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && my_is_request("frontend") ) {
    include "includes/MyGeo.php";
    $GLOBALS["mygeo"] = MyGeo::instance();
}

/*
Сессия Woocommerce открывается только после того, как в корзину добавлен товар
*/