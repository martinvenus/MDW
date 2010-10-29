<?php

/**
 * Model pro správu ticketů
 *
 * @author     Jaroslav Líbal
 */
class TicketsModel {
    /*
     * Metoda, která vrátí dataSource uživatelových ticketů v systému
     * @throws DibiException
     */

    public static function getMyTickets($id) {

        $rowset = dibi::dataSource('SELECT id, ticketId, priority, name, subject, status, updated FROM ticket WHERE staffId=%i ORDER BY closed ASC, updated DESC', $id);

        return $rowset;
    }

    /*
     * Metoda, která vrátí dataSource nových ticketů v systému
     * @throws DibiException
     */

    public static function getNewTickets($id) {

        $rowset = dibi::dataSource('SELECT id, ticketId, priority, name, subject, status, updated FROM ticket WHERE (staffId IS NULL) AND (departmentId=%i) AND (closed=%i) ORDER BY updated DESC', $id, 0);

        return $rowset;
    }

        /*
     * Metoda, která vrátí dataSource uzavřených tiketů v systému
     * @throws DibiException
     */

    public static function getClosedTickets($id) {

        $rowset = dibi::dataSource('SELECT id, ticketId, priority, name, subject, status, updated FROM ticket WHERE (departmentId=%i) AND (closed=%i) ORDER BY updated DESC', $id, 1);

        return $rowset;
    }

    /*
     * Metoda, která vrátí deaily ticketu
     * @throws DibiException
     */

    public static function getTicketDetails($id) {

        $result = dibi::query('SELECT * FROM ticket WHERE id=%i LIMIT 1', $id);
        $all = $result->fetchAll();

        return $all[0];
    }

    /*
     * Metoda, která vrátí všechny zprávy k tiketu
     * @throws DibiException
     */

    public static function getTicketMessages($id) {

        $result = dibi::query('SELECT * FROM ticketMessage WHERE ticketID=%i ORDER BY date DESC', $id);
        $all = $result->fetchAll();

        return $all;
    }

    /*
     * Prideleni tiketu zamestnanci
     * @throws DibiException
     */

    public static function setTicketStaff($id, $staff, $form) {

        dibi::query('UPDATE ticket SET `staffId`=%i, `updated`=%i WHERE `id` = %i', $staff, $form['time'], $id);

        dibi::query('INSERT INTO ticketMessage ( `ticketId`,
`name`,
`message`,
`date`,
`type`
) VALUES (%i, %s, %s, %i, %i)',
                        $id,
                        $form['name'],
                        $form['comment'],
                        $form['time'],
                        $form['type']
        );
    }

    public static function forwardTicket($form) {

        dibi::query('UPDATE ticket SET `staffId` = %i, `updated` = %i WHERE id = %i LIMIT 1', $form['colleague'], $form['time'], $form['tiket']);

        dibi::query('INSERT INTO ticketMessage ( `ticketId`,
`name`,
`message`,
`date`,
`type`
) VALUES (%i, %s, %s, %i, %i)',
                        $form['tiket'],
                        $form['name'],
                        $form['comment'],
                        $form['time'],
                        $form['type']
        );
    }

    public static function changeDepartment($form) {

        dibi::query('UPDATE ticket SET `staffId` = NULL, `departmentId` = %i, `updated` = %i WHERE id = %i LIMIT 1', $form['department'], $form['time'], $form['tiket']);

        dibi::query('INSERT INTO ticketMessage ( `ticketId`,
`name`,
`message`,
`date`,
`type`
) VALUES (%i, %s, %s, %i, %i)',
                        $form['tiket'],
                        $form['name'],
                        $form['comment'],
                        $form['time'],
                        $form['type']
        );
    }

    public static function addReply($form) {

        dibi::query('UPDATE ticket SET `updated` = %i WHERE id = %i LIMIT 1', $form['time'], $form['tiket']);

        dibi::query('INSERT INTO ticketMessage ( `ticketId`,
`name`,
`message`,
`date`,
`type`
) VALUES (%i, %s, %s, %i, %i)',
                        $form['tiket'],
                        $form['name'],
                        $form['message'],
                        $form['time'],
                        $form['type']
        );
    }

    /*
     * Metoda, která otevře/uzavře tiket
     * @throws DibiException
     */

    public static function changeClosed($data) {

        if ($data['closed'] == 1) {
            dibi::query('UPDATE ticket SET `staffId` = NULL, `closed` = %i, `status`=%s, `updated` = %i WHERE id = %i LIMIT 1', $data['closed'], $data['status'], $data['time'], $data['tiket']);
        } else {
            dibi::query('UPDATE ticket SET `closed` = %i, `status`=%s, `updated` = %i WHERE id = %i LIMIT 1', $data['closed'], $data['status'], $data['time'], $data['tiket']);
        }

        dibi::query('INSERT INTO ticketMessage ( `ticketId`,
`name`,
`message`,
`date`,
`type`
) VALUES (%i, %s, %s, %i, %i)',
                        $data['tiket'],
                        $data['name'],
                        $data['comment'],
                        $data['time'],
                        $data['type']
        );
    }

}

?>