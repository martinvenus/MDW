<?php

/**
 * Rally-Base
 *
 * @copyright  Copyright (c) 2010 Martin Venuš
 * @package    Rally-Base
 */

/**
 * Model pro správu uživatelů
 *
 * @author     Martin Venuš
 * @package    Rally-Base
 */
class UsersModel extends Object implements IAuthenticator {
    /*
     * @param  array
     * @return IIdentity
     * @throws AuthenticationException
     */

    public function authenticate(array $credentials) {

        $row = null;
        $roles = null;

        // Vybere z databáze uživatele se zadaným uživatelským jménem a heslem
        $result = dibi::query('SELECT * FROM `user` WHERE userName=%s AND password=%s AND active=1 LIMIT 1', $credentials['username'], $credentials['password']);

        // Je-li počet řádků 0 -> neexistuje v db uživatel s daným heslem -> vyhodíme vyjímku
        if (count($result) == 0) {
            throw new AuthenticationException('Authentication failed!');
        }

        foreach ($result as $n => $row

            );

        // Z výsledku odebereme heslo - kvůli bezpečnosti aplikace
        unset($row['password']);

        // Uvolníme prostředky
        unset($result);

        // Z databáze vybereme jednotlivé uživatelské role
        $result = dibi::query('SELECT roles.name AS role, user.userName as userName FROM `userRole` JOIN user ON (user.id=userRole.userId) JOIN roles ON (roles.id = userRole.roleId) WHERE userName=%s AND userRole.active=1', $row['userName']);

        $i = 0;
        foreach ($result as $n => $rowx) {

            // Role vypíšeme do pole
            $roles[$i] = $rowx['role'];
            $i++;
        }

        // Uvolníme prostředky
        unset($result);

        // Vrátíme identitu
        return new Identity($row['userName'], $roles, $row);
    }

    /*
     * Metoda vytvoří uživatele ze zadaných údajů ve formuláři
     * @param $form data z formuláře
     * @throws DibiException
     */

    public static function createUser(array $form) {

        $password = hash(HASH_TYPE, $form['password1']);

        dibi::query('INSERT INTO user ( `userName`,
`password`,
`time`,
`firstName`,
`surname`,
`title`,
`email`,
`icq`,
`skype`,
`mobile`) VALUES (%s, %s, %i, %s, %s, %s, %s, %iN, %sN, %s)',
                        $form['userName'],
                        $password,
                        time(),
                        $form['firstName'],
                        $form['surname'],
                        $form['title'],
                        $form['email'],
                        (int) $form['icq'],
                        $form['skype'],
                        $form['mobile']
        );

        //Zjistíme id nově přidaného uživatele
        $id = dibi::insertId();

        // Vložíme do tabulky userRole role nového uživatele
        foreach ($form['prava'] as $n => $row) {
            dibi::query('INSERT INTO userRole (userId, roleId, active) VALUES (%i, %i, 1)', $id, $row);
        }
    }

    /*
     * Metoda, která edituje záznamy uživatele v databázi
     * @param $form data z formuláře
     * @throws DibiException
     */

    public static function editUser(array $form) {

        $id = $form['id'];

        dibi::query('UPDATE user SET `firstName` = %s,
`surname` = %s,
`title` = %s,
`email` = %s,
`icq` = %iN,
`skype` = %sN,
`mobile` = %s WHERE id = %i LIMIT 1', $form['firstName'], $form['surname'], $form['title'], $form['email'], (int) $form['icq'], $form['skype'], $form['mobile'], $id);

        if (isset($form['prava'])) {
            dibi::query('DELETE FROM userRole WHERE userId=%i', $id);

            foreach ($form['prava'] as $n => $row) {
                dibi::query('INSERT INTO userRole (userId, roleId, active) VALUES (%i, %i, 1)', $id, $row);
            }
        }
    }

    /*
     * Metoda, která změní heslo zadaného uživatele
     * @param $form data z formuláře
     * @throws DibiException
     */

    public static function editPassword(array $form) {

        $id = $form['id'];
        $password = hash(HASH_TYPE, $form['password1']);

        dibi::query('UPDATE user SET `password` = %s WHERE id = %i LIMIT 1', $password, $id);
    }

    /*
     * Metoda, která smaže zadaného uživatele
     * @param $id id mazaného uživatele
     * @throws DibiException
     */

    public static function delUser($id) {

        dibi::query('UPDATE user SET active = 0 WHERE id = %i', $id);
        dibi::query('UPDATE userRole SET active = 0 WHERE userId = %i', $id);
    }

    /*
     * Metoda, která vrátí všechny dostupné role jako dvojici - id, role
     * @return $roles všechny dostupné role - id, role
     * @throws DibiException
     */

    public static function getRoles() {

        // Z databáze vybereme všechny role
        $result = dibi::query('SELECT roles.id AS id, roles.name AS role FROM roles ORDER BY roles.name');

        $roles = $result->fetchPairs('id', 'role');

        // uvolníme prostředky
        unset($result);

        return $roles;
    }

    /*
     * Metoda, která vrátí informace o daném uživateli - podle id
     * @param $id id uživatele
     * @return $user informace o uživateli v asociativním poli
     * @throws DibiException
     */

    public static function getUser($id) {

        $result = dibi::query('SELECT * FROM user WHERE id=%i LIMIT 1', $id);

        foreach ($result as $n => $row

            );

        unset($result);

        $result = dibi::query('SELECT roleId FROM userRole WHERE userId=%i', $id);

        $pom = null;

        $i = 0;
        foreach ($result as $n => $radek) {

            $pom[$i] = $radek['roleId'];

            $i++;
        }


        unset($result);

        $row['prava'] = $pom;

        return $row;
    }

    /*
     * Metoda, která vrátí informace o oddělení uživatele
     * TODO: Přepsat univerzálně aby mohl mít user více oddělení!
     */

    public static function getDepartment($staffId) {

        $result = dibi::query('SELECT departmentId FROM userDepartment WHERE staffId=%i', $staffId);

        $single = $result->fetchSingle();

        return $single;
    }

    /*
     * Metoda, která vrátí název oddělení
     */

    public static function getDepartmentName($departmentId) {

        $result = dibi::query('SELECT name FROM department WHERE id=%i', $departmentId);

        $single = $result->fetchSingle();

        return $single;
    }

    /*
     * Metoda, která vrátí dataSource všech uživatelů v systému
     * @throws DibiException
     */

    public static function getUsers() {

        $rowset = dibi::dataSource('SELECT * FROM user');

        return $rowset;
    }

    /*
     * Metoda, která vrátí logickou hodnotu, zda existuje uživatel s daným userName
     * Využívá se pro ověření, zda v systému již existuje uživatel se stejným uživ. jménem při vytváření uživatele
     * @param $userName uživatelské jméno
     * @return $vysledek logická hodnota vyjadřující exitenci daného uživatele
     * @throws DibiException
     */

    public static function getUserByUserName($userName) {

        $result = dibi::query('SELECT * FROM user WHERE userName=%s LIMIT 1', $userName);

        if (count($result) > 0) {
            $vysledek = true;
        } else {
            $vysledek = false;
        }

        // Uvolníme prostředky
        unset($result);

        return $vysledek;
    }

    /*
     * Metoda, která vygeneruje náhodné heslo jako návrh pro nového uživatele
     * @param $lenght délka hesla
     */

    public static function genPass($length=9) {

        $malaPismena = "abcdefghijklmnopqrstuvwxyz";
        $velkaPismena = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $cislice = "1234567890";

        $pass = '';

        for ($i = 0; $i < $length; $i++) {

            if ($i < ($length / 3)) {
                $chars = $malaPismena;
            } elseif (($i > ($length / 3)) && ($i < (2 * $length / 3))) {
                $chars = $velkaPismena;
            } else {
                $chars = $cislice;
            }

            $numChars = strlen($chars);

            $pass[$i] = substr($chars, mt_rand(1, $numChars) - 1, 1);
        }

        // Zamícháme pole
        shuffle($pass);

        $password = '';

        for ($i = 0; $i < count($pass); $i++) {
            $password.=$pass[$i];
        }

        return $password;
    }

    /*
     * Metoda, která ověří, zda má přihlášený uživatel přístup k danému zdroji
     * Přístup je povolen, je-li vystaveno povolení na úrovni ACL nebo je-li aktuálně přihlášený uživatel vlastníkem resource
     * @param $resource zdroj ke kterému je přistupováno
     * @privilege akce, která je požadována
     * @userId id uživatele, který je vlastníkem daného resource
     */

    public static function isAllowed($resource, $privilege, $userId) {

        if (Environment::getUser()->isAllowed($resource, $privilege)) {
            return true;
        }

        if (Environment::getUser()->getIdentity()->id == $userId) {
            return true;
        }

        return false;
    }

    /*
     * Metoda, která vrátí kolegy z oddělení
     * @throws DibiException
     */

    public static function getMyColleagues($id) {

        $result = dibi::query('SELECT user.id, CONCAT(user.firstName," ", user.Surname) FROM user, userDepartment WHERE (userDepartment.departmentId=%i) AND (user.id=userDepartment.staffId)', $id);

        $pairs = $result->fetchPairs();

        return $pairs;
    }

    /*
     * Metoda, která vrátí jména všech zaměstnanců
     * @throws DibiException
     */

    public static function getStaffNames() {

        $result = dibi::query('SELECT id, CONCAT(user.firstName," ", user.Surname) FROM user');

        $pairs = $result->fetchPairs();

        return $pairs;
    }

    /*
     * Metoda, která vrátí jméno konkrétního zaměstnance
     * @throws DibiException
     */

    public static function getStaffName($staffId) {

        $result = dibi::query('SELECT CONCAT(user.firstName," ", user.Surname) FROM user WHERE id=%i', $staffId);

        $single = $result->fetchSingle();

        return $single;
    }

    /*
     * Metoda, která vrátí názvy oddělení
     * @throws DibiException
     */

    public static function getAllDepartments() {

        $result = dibi::query('SELECT id, name FROM department');

        $pairs = $result->fetchPairs();

        return $pairs;
    }

}

?>