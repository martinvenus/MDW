<?php
/*
 * Připojení pro vývoj
 */
if (strcmp($_SERVER['HTTP_HOST'], 'localhost') == 0) {
    /*
     * Uživatelské jméno pro připojení k db
     */
    $user_mysql = "mdw_wsolution_cz";

    /*
     * Heslo pro připojení k db
     */
    $pass_mysql = "WnPP6aJVD85pQqKu";

    /*
     * Databáze
     */
    $db_mysql = "mdw_wsolution_cz";   

/*
 * Připojení pro produkční nasazení
 */
} else {
    /*
     * Uživatelské jméno pro připojení k db
     */
    $user_mysql = "mdw_wsolution_cz";

    /*
     * Heslo pro připojení k db
     */
    $pass_mysql = "WnPP6aJVD85pQqKu";

    /*
     * Databáze
     */
    $db_mysql = "mdw_wsolution_cz";
}
?>