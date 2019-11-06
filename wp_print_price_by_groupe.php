<?php
/*
Plugin Name:        Imprimer les produits par groupe
Plugin URI:         https://github.com/atamj/wp_print_price_by_groupe
Description:        Plugin pour imprimer les différent prix des produit générer par le plugin 'Role Based Price For WooCommerce'
Version:            1
Author:             Jaël Atam
Author URI:         portfolio.jaelatam.com

*/

include_once 'price.php';
include_once 'products.php';


/**
 * Class PrintPrice
 */
class PrintPrice
{
    public function __construct()
    {
        add_action('admin_menu', 'price');
        add_action('admin_menu', 'products');
    }
}

new PrintPrice();