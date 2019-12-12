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

function getForm()
{
    $label_price = getLabel();

    /*Generate form*/
    ?>
    <div class="wrap">
        <h1>Imprimer les prix</h1>
        <form method="POST" action="">
            <label for="groupe">Quel prix voulez vous voir?</label>
            <select name="groupe" id="groupe">
                <option value="tous">Tous</option>
                <?php
                foreach ($label_price as $key => $value) {
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
    //Create first row
    $list = [
        [
            'ID',
            'Titre',
        ],
    ];
    $label =  getLabel();

    /*If groupe 'tous' is selected*/
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

    /*Display table*/
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

            query_posts(['post_type' => 'product']);

            /*Defined number of column of price for the table*/
            $case = 0;
            while (have_posts()){
                the_post();
                $based_price = get_post_meta(get_the_ID(), '_role_based_price', true);
                $enable_based_price = get_post_meta(get_the_ID(), '_enable_role_based_price', true);
                if ($enable_based_price == 1 && count($based_price) > $case){
                    $case = count($based_price);
                }

            }

            /*Generate row for the table*/
            while (have_posts()) : the_post();

                $based_price = get_post_meta(get_the_ID(), '_role_based_price', true);
                $enable_based_price = get_post_meta(get_the_ID(), '_enable_role_based_price', true);
                if ($enable_based_price == 1):
                    $current_case = count($based_price);
                    echo "<tr><td>".get_the_ID()."</td><td>" . get_the_title() . "</td>";
                    if ($groupe == 'tous'){

                        foreach ($based_price as $price) {
                            echo "<td>" . $price["regular_price"] . "</td><td>" . $price["selling_price"] . "</td>";
                        }
                        if ($current_case < $case){
                            $boucle = $case - $current_case;
                            for ($i = 1; $i <= $boucle; $i++ ){
                                echo "<td></td><td></td>";
                            }
                        }

                    }
                    else{

                        foreach ($based_price as $key => $price){
                            if ($key == $groupe){
                                echo "<td>" . $price["regular_price"] . "</td><td>" . $price["selling_price"] . "</td>";
                            }
                        }
                    }


                    echo "</tr>";

                endif;

            endwhile;

            /*Générate CSV*/
            $path = wp_upload_dir();   // or where ever you want the file to go
            wp_delete_file($path['path'] . "/price_" . $groupe . ".csv");
            $outstream = fopen($path['path'] . "/price_" . $groupe . ".csv", "w");  // the file name you choose

            foreach ($list as $line) {
                fputcsv($outstream, $line);
            }
            fclose($outstream);
            ?>
        </table>
        <?php
        /*Generate link to download CSV*/
        echo '<p></p><a href="' . $path['url'] . '/price_' . $groupe . '.csv">Télécharger CSV</a></p>';  //make a link to the file so the user can download.
        ?>

    </div>
    <?php
}

/*Get Label price*/
function getLabel(){
    query_posts(['post_type' => 'product', 'meta_key' => '_role_based_price']);
    $nb_price = 0;
    $label_price =[];

    while (have_posts()){
        the_post();
        $based_price = get_post_meta(get_the_ID(), '_role_based_price', true);
        $enable_based_price = get_post_meta(get_the_ID(), '_enable_role_based_price', true);
        if ($enable_based_price == 1){
            if (count($based_price) > $nb_price){
                $nb_price = count($based_price);
                foreach ($based_price as $key => $value){
                    $label_price[$key] = $value;
                }
            }
        }
    }
    return $label_price;
}