<?php
function price()
{
    add_menu_page(
        'Imprimer les prix',
        'Imprimer les prix',
        'manage_options',
        'print_price',
        'wp_print_main_html',
        'dashicons-download',
        20
    );
}

function wp_print_main_html()
{
    require_once "css/style.php";
    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    //Check groupe
    if (isset($_POST['groupe'])) {
        getPrice($_POST['groupe']);
    } else {
        getForm();
    }

}

function getForm(){
    //BD
    global $wpdb;
    $meta_value = $wpdb->get_results("SELECT meta_value FROM adp_posts INNER JOIN adp_postmeta ON adp_posts.ID = adp_postmeta.post_id WHERE adp_postmeta.meta_key = '_role_based_price'");
    $meta_value = unserialize($meta_value[0]->meta_value);
    ?>
    <div class="wrap">
        <h1>Imprimer les prix</h1>
        <form method="POST" action="">
            <label for="groupe">Quel prix voulez vous voir?</label>
            <select name="groupe" id="groupe">
                <option value="tous">Tous</option>
                <?php
                foreach ($meta_value as $key => $value) {
                    ?>
                    <option value="<?= $key ?>"><?= $key ?></option>
                    <?php
                }
                ?>
                <input type="submit" value="Valider">
            </select>
        </form>
    </div>
    <?php
}

function getPrice($groupe)
{
    //BD
    global $wpdb;
    $all = $wpdb->get_results("SELECT ID,post_title,meta_value FROM adp_posts INNER JOIN adp_postmeta ON adp_posts.ID = adp_postmeta.post_id WHERE adp_postmeta.meta_key = '_role_based_price'");

    //Create first row
    $list = [
        [
            'ID',
            'Titre',
        ],
    ];
    $label = unserialize($all[0]->meta_value);
    if ($groupe == "tous") {
        foreach ($label as $key => $value) {
            $list[0][] = 'Prix ' . $key;
            $list[0][] = 'Prix promo ' . $key;
        }
    } else {
        foreach ($label as $key => $value) {
            if ($key == $groupe) {
                $list[0][] = 'Prix ' . $key;
                $list[0][] = 'Prix promo ' . $key;
            }
        }
    }

//Display table
    ?>
    <div class="wrap">
        <h1><?= $groupe ?></h1>

        <table border="1">
            <tr>
                <?php
                foreach ($list[0] as $value) {
                    echo "<th>" . $value . "</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($all as $one): ?>
                <?php
                $array = [];
                foreach ($one as $key => $value) {
                    if ($key == "meta_value") {
                        $role_based_price = unserialize($value);
                        if ($groupe == "tous") {
                            foreach ($role_based_price as $groupe_x) {
                                foreach ($groupe_x as $x_price) {
                                    $array[] = $x_price;
                                }
                            }
                        } else {
                            foreach ($role_based_price as $key => $groupe_x) {
                                if ($key == $groupe) {
                                    foreach ($groupe_x as $x_price) {
                                        $array[] = $x_price;
                                    }
                                }
                            }
                        }
                    } else {
                        $array[] = $value;
                    }

                }
                $list [] = $array;
                ?>
                <tr>
                    <?php
                    foreach ($array as $value) {
                        echo "<td>" . $value . "</td>";
                    }
                    ?>
                </tr>
            <?php endforeach;
            $path = wp_upload_dir();   // or where ever you want the file to go
            wp_delete_file( $path['path'] . "/price_" . $groupe . ".csv" );
            $outstream = fopen($path['path'] . "/price_" . $groupe . ".csv", "w");  // the file name you choose

            foreach ($list as $line) {
                fputcsv($outstream, $line);
            }
            fclose($outstream);
            ?>
        </table>
        <?php
        echo '<p></p><a href="' . $path['url'] . '/price_' . $groupe . '.csv">Télécharger CSV</a></p>';  //make a link to the file so the user can download.
        ?>

    </div>
    <?php
}