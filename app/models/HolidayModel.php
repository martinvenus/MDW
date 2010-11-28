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

    public static function getOrderedZajezdyByUserId($userId){
        
        $result = dibi::dataSource('SELECT * FROM bonusAPI WHERE userId = %i', $userId);
        
        return $result;
        
    }

}

?>
