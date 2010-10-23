<!DOCTYPE html><link rel="stylesheet" href="data/style.css">

<h1>Using DateTime | dibi</h1>

<?php

require_once 'Nette/Debug.php';
require_once '../dibi/dibi.php';

date_default_timezone_set('Europe/Prague');



// CHANGE TO REAL PARAMETERS!
dibi::connect(array(
	'driver'   => 'sqlite',
	'database' => 'data/sample.sdb',
	'formatDate' => "'Y-m-d'",
	'formatDateTime' => "'Y-m-d H-i-s'",
));



// generate and dump SQL
dibi::test("
	INSERT INTO [mytable]", array(
		'id'    => 123,
		'date'  => new DateTime('12.3.2007'),
		'stamp' => new DateTime('23.1.2007 10:23'),
	)
);
// -> INSERT INTO [mytable] ([id], [date], [stamp]) VALUES (123, '2007-03-12', '2007-01-23 10-23-00')
