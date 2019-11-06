<?php
function products()
{
    add_menu_page(
        'Imprimer les produits',
        'Imprimer les produits',
        'manage_options',
        'print_products',
        'print_products_html',
        'dashicons-download',
        24
    );
}
function print_products_html()
{
    require_once "css/style.php";
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) )
    {
        return;
    }

    global $wpdb;
    $all = $wpdb->get_results( "SELECT ID,post_title FROM `adp_posts` WHERE post_type='product'" );
    $list = [
        [
            'ID',
            'Titre',
        ],
    ];
    ?>
    <div class ="wrap">
        <h1>Produits</h1>

        <table border="1">
            <tr>
                <th>ID</th>
                <th>Titre</th>
            </tr>
            <?php foreach ($all as $one): ?>
                <?php
                $list [] = [
                    $one->ID,
                    $one->post_title,
                ];
                ?>
                <tr>
                    <td><?= $one->ID  ?></td>
                    <td><?= $one->post_title  ?></td>
                </tr>
            <?php endforeach;
            $path = wp_upload_dir();   // or where ever you want the file to go
            wp_delete_file( $path['path'] . "/all_products.csv" );
            $outstream = fopen($path['path']."/all_products.csv", "w");  // the file name you choose

            foreach ($list as $line) {
                fputcsv($outstream, $line);
            }
            fclose($outstream);
            ?>
        </table>
        <?php
        echo '<p></p><a href="'.$path['url'].'/all_products.csv">Télécharger CSV</a></p>';  //make a link to the file so the user can download.
        ?>

    </div>
    <?php
}