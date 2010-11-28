<?php

/**
 * Request Tracking System
 * MI-MDW at CZECH TECHNICAL UNIVERSITY IN PRAGUE
 *
 * @copyright  Copyright (c) 2010
 * @package    RTS
 * @author     Andrey Chervinka, Jaroslav Líbal, Martin Venuš
 */

/**
 *
 * Model for bonus programe
 *
 */
class HolidayModel extends BaseModel {
    /*
     * Přidání bonusu pro uživatele
     */

    public static function addBonus($user, $zajezd, $objednavka) {

        dibi::query('INSERT INTO bonusAPI ( `userId`,
`zajezdId`,
`orderId`
) VALUES (%i, %s, %i)',
                        $user,
                        $zajezd,
                        $objednavka
        );
    }

    /*
     * Zrušení bonusu pro uživatele
     */

    public static function removeBonus($objednavka) {

        dibi::query('DELETE FROM bonusAPI WHERE orderId=%i', $objednavka);
        
    }

    public static function getOrderedZajezdyByUserId($userId) {

        $result = dibi::dataSource('SELECT * FROM bonusAPI WHERE userId = %i', $userId);

        return $result;
    }

}

?>
