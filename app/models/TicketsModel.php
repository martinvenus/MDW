<?php

/**
 * Model pro správu ticketů
 *
 * @author     Jaroslav Líbal
 */
class TicketsModel
{


     /*
     * Metoda, která vrátí dataSource uživatelových ticketů v systému
     * @throws DibiException
     */
    public static function getMyTickets($id){

        $rowset = dibi::dataSource('SELECT id, ticketId, priority, name, subject, status, updated FROM ticket WHERE staffId=%i ORDER BY closed ASC, updated DESC', $id);

        return $rowset;
    }

    /*
     * Metoda, která vrátí deaily ticketu
     * @throws DibiException
     */
    public static function getTicketDetails($id){

        $result = dibi::query('SELECT * FROM ticket WHERE id=%i LIMIT 1', $id);
        $all = $result->fetchAll();

        return $all[0];

    }

    /*
     * Metoda, která vrátí všechny zprávy k tiketu
     * @throws DibiException
     */
    public static function getTicketMessages($id){

        $result = dibi::query('SELECT * FROM ticketMessage WHERE ticketId=%i ORDER BY date DESC', $id);
        $all = $result->fetchAll();

        return $all;

    }

}
?>