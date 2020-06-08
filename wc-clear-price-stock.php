<?php
/**
 * Plugin Name: WC Clear Price Stock
 * Description: With this plugin you can clean the prices, offers and stocks of all the products available in woocommerce
 * Plugin URI: https://github.com/dhenriquez/wc-clear-price-stock
 * Author: Daniel Henriquez Sandoval
 * Author URI: https://dhenriquez.cl
 * Version: 1.0.0
 * WC requires at least: 4.0.0
 * WC tested up to: 4.2.0
 */

if ( !defined( 'ABSPATH' )) { die; }
define('WCCPS_PATH', plugin_dir_path(__FILE__));
define('WCCPS_URL', plugin_dir_url(__FILE__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once WCCPS_PATH . '/libs/tgm-plugin-activation/class-tgm-plugin-activation.php';
require_once WCCPS_PATH . '/class/class-wccps-plugins-requeridos.php';
require_once WCCPS_PATH . '/class/class-wccps-cmb2.php';

class WC_Clear_Price_Stock {

    public function __construct() {
        $plugin = get_plugin_data( __FILE__ );
        if (is_plugin_active( 'woocommerce/woocommerce.php' ) && is_plugin_active('cmb2/init.php')) {
            new WCCPS_CMB2($plugin['Version']);
        } else {
            new WCCPS_Plugins_Requeridos();
        }
        add_action( 'admin_menu', array($this, 'fn_menu'), 99);
    }

    public function fn_menu(){
        add_submenu_page( 'wccps_options', 'Clear Offer Price', 'Clear Offer Price', 'manage_options', "clear-offer-price-".basename(__FILE__), array( $this, 'fn_menu_clear_offer_price'));
        add_submenu_page( 'wccps_options', 'Clear Regular Price', 'Clear Regular Price', 'manage_options', "clear-regular-price-".basename(__FILE__), array( $this, 'fn_menu_clear_regular_price'));
        add_submenu_page( 'wccps_options', 'Clear Stock', 'Clear Stock', 'manage_options', "clear-stock-".basename(__FILE__), array( $this, 'fn_menu_clear_stock'));

        $debug = cmb2_get_option( 'wccps_options', 'debug_text_wccps', false);
        if ( $debug ) {
            add_submenu_page( 'wccps_options', 'Log', 'Log', 'manage_options', "log-".basename(__FILE__), array( $this, 'fn_menu_log'));
        }
    }

    private function fn_menu_clear_offer_price() {
        echo '<div class="wrap">';
        $plugin = get_plugin_data( __FILE__ );
        echo "<h1>Clear Offer Price <small>" . $plugin['Version'] . "</small></h1>";
        if ( isset($_GET['action']) && $_GET['action'] == 'clear_offer_price' ) {
            $this->wccps_save_log("== Clear Offer Price ==");
            if ( $products = $this->getAllProductId() ) {
                echo "<p>This is a summary of the products that were updated:</p>";
                echo '<table class="widefat fixed">';
                echo '<thead><tr>';
                echo '<th>SKU</th>';
                echo '<th>Name</th>';
                echo '<th>Status</th>';
                echo '</tr></thead>';
                foreach ( $products as $product ) {
                    $objProduct = new WC_Product( $product->post_id );
                    if ( $objProduct->get_sale_price() != "" ) {
                        $objProduct->set_price( $objProduct->get_regular_price() );
                        $objProduct->set_sale_price( "" );
                    }
                    if ($product_id = $objProduct->save() ){
                        $status = "Clean Offer Price";
                    } else {
                        $status = "Error";
                    }
                    $sku = $objProduct->get_sku();
                    $name = $objProduct->get_name();
                    echo "<tr>";
                    echo "<td>" . $sku . "</td>";
                    echo "<td>" . $name . "</td>";
                    echo "<td>" . $status . "</td>";
                    echo "</tr>";
                    $this->wccps_save_log("SKU: " . $sku . " Name: " . $name . " Status: " . $status);
                }
                $objProduct = null;
                unset($objProduct);
                echo '</table>';
                $this->wccps_save_log("== End Clear Offer Price ==");
            } else {
                echo "<p>I think there are no products in your store</p>";
                $this->wccps_save_log("== No Products in Store ==");
            }
        } else {
            echo "<p>All prices of products on sale will be eliminated, are you sure?</p>";
            $boton_texto = "I'm ready. Let's do this!";
            $boton_link = add_query_arg(
                array(
                    'action' => 'clear_offer_price',
                    'page' => $_GET['page'],
                ),
                admin_url('admin.php')
            );
            echo '<form id="descargar" method="post" action="' . $boton_link . '">';
            submit_button( $boton_texto ,'primary');
            echo '</form>';
        }
        echo '</div>';
    }

    private function fn_menu_clear_regular_price() {
        echo '<div class="wrap">';
        $plugin = get_plugin_data( __FILE__ );
        echo "<h1>Clear Regular Price <small>" . $plugin['Version'] . "</small></h1>";
        if ( isset($_GET['action']) && $_GET['action'] == 'clear_regular_price' ) {
            $this->wccps_save_log("== Clear Regular Price ==");
            if ( $products = $this->getAllProductId() ) {
                echo "<p>This is a summary of the products that were updated:</p>";
                echo '<table class="widefat fixed">';
                echo '<thead><tr>';
                echo '<th>SKU</th>';
                echo '<th>Name</th>';
                echo '<th>Status</th>';
                echo '</tr></thead>';
                foreach ( $products as $product ) {
                    $objProduct = new WC_Product( $product->post_id );
                    $objProduct->set_price("");
                    $objProduct->set_regular_price("");
                    if ($product_id = $objProduct->save() ){
                        $status = "Clean Regular Price";
                    } else {
                        $status = "Error";
                    }
                    $sku = $objProduct->get_sku();
                    $name = $objProduct->get_name();
                    echo "<tr>";
                    echo "<td>" . $sku . "</td>";
                    echo "<td>" . $name . "</td>";
                    echo "<td>" . $status . "</td>";
                    echo "</tr>";
                    $this->wccps_save_log("SKU: " . $sku . " Name: " . $name . " Status: " . $status);
                }
                $objProduct = null;
                unset($objProduct);
                echo '</table>';
                $this->wccps_save_log("== End Clear Regular Price ==");
            } else {
                echo "<p>I think there are no products in your store</p>";
                $this->wccps_save_log("== No Products in Store ==");
            }
        } else {
            echo "<p>All prices of products will be eliminated, are you sure?</p>";
            $boton_texto = "I'm ready. Let's do this!";
            $boton_link = add_query_arg(
                array(
                    'action' => 'clear_regular_price',
                    'page' => $_GET['page'],
                ),
                admin_url('admin.php')
            );
            echo '<form id="descargar" method="post" action="' . $boton_link . '">';
            submit_button( $boton_texto ,'primary');
            echo '</form>';
        }
        echo '</div>';
    }

    private function fn_menu_clear_stock() {
        echo '<div class="wrap">';
        $plugin = get_plugin_data( __FILE__ );
        echo "<h1>Clear Stock <small>" . $plugin['Version'] . "</small></h1>";
        if ( isset($_GET['action']) && $_GET['action'] == 'clear_stock' ) {
            $this->wccps_save_log("== Clear Stock ==");
            if ( $products = $this->getAllProductId() ) {
                echo "<p>This is a summary of the products that were updated:</p>";
                echo '<table class="widefat fixed">';
                echo '<thead><tr>';
                echo '<th>SKU</th>';
                echo '<th>Name</th>';
                echo '<th>Status</th>';
                echo '</tr></thead>';
                foreach ( $products as $product ) {
                    $objProduct = new WC_Product( $product->post_id );
                    $objProduct->set_stock_quantity( 0 );
                    $objProduct->set_stock_status( "outofstock" );
                    if ($product_id = $objProduct->save() ){
                        $status = "Clean stock";
                    } else {
                        $status = "Error";
                    }
                    $sku = $objProduct->get_sku();
                    $name = $objProduct->get_name();
                    echo "<tr>";
                    echo "<td>" . $sku . "</td>";
                    echo "<td>" . $name . "</td>";
                    echo "<td>" . $status . "</td>";
                    echo "</tr>";
                    $this->wccps_save_log("SKU: " . $sku . " Name: " . $name . " Status: " . $status);
                }
                $objProduct = null;
                unset($objProduct);
                echo '</table>';
                $this->wccps_save_log("== End Clear Stock ==");
            } else {
                echo "<p>I think there are no products in your store</p>";
                $this->wccps_save_log("== No Products in Store ==");
            }
        } else {
            echo "<p>All stock of products will be eliminated, are you sure?</p>";
            $boton_texto = "I'm ready. Let's do this!";
            $boton_link = add_query_arg(
                array(
                    'action' => 'clear_stock',
                    'page' => $_GET['page'],
                ),
                admin_url('admin.php')
            );
            echo '<form id="descargar" method="post" action="' . $boton_link . '">';
            submit_button( $boton_texto ,'primary');
            echo '</form>';
        }
        echo '</div>';
    }

    private function fn_menu_log() {
        $plugin = get_plugin_data( __FILE__ );
        $debug = cmb2_get_option( 'wccps_options', 'debug_text_wccps', false);
        echo '<div class="wrap">';
        echo "<h1>Log <small>" . $plugin['Version'] . "</small></h1>";
        if ( $debug ) {
            echo '<textarea readonly style="width:100%; height:600px;">';
            if ( file_exists( WCCPS_PATH . "/wccps.log" ) ){
                echo file_get_contents( WCCPS_PATH . "/wccps.log" );
            }
            echo '</textarea>';
        }
        echo '</div>';
    }

    private function getAllProductId(){
        global $wpdb;
        $products = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku'");
        if ( $products ) return $products;
        return false;
    }

    private function wccps_save_log( $message ) {
        $plugin = get_plugin_data( __FILE__ );
        $debug = cmb2_get_option( 'wccps_options', 'debug_text_wccps', false);
        if ($debug) {
            if(is_array($message)) { 
                $message = json_encode($message); 
            } 
            $file = fopen( WCCPS_PATH . "/wccps.log","a"); 
            fwrite($file, "\n[" . current_time('Y-m-d h:i:s') . "] [PV: " . $plugin['Version'] . "] " . $message); 
            fclose($file);
        }
    }
}
new WC_Clear_Price_Stock();
?>