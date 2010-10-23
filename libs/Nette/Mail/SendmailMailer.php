<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Mail
 */



/**
 * Sends e-mails via the PHP internal mail() function.
 *
 * @author     David Grudl
 */
class NSendmailMailer extends NObject implements IMailer
{

	/**
	 * Sends e-mail.
	 * @param  NMail
	 * @return void
	 */
	public function send(NMail $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(NMail::EOL . NMail::EOL, $tmp->generateMessage(), 2);

		NTools::tryError();
		$res = mail(
			str_replace(NMail::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
			str_replace(NMail::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
			str_replace(NMail::EOL, PHP_EOL, $parts[1]),
			str_replace(NMail::EOL, PHP_EOL, $parts[0])
		);

		if (NTools::catchError($msg)) {
			throw new InvalidStateException($msg);

		} elseif (!$res) {
			throw new InvalidStateException('Unable to send email.');
		}
	}

}
