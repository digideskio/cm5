<?php
/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *
 *  Copyright (c) 2010 Sque.
 *
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Contributors:
 *      Sque - initial API and implementation
 */

//require_once 'Zend/Log/Writer/Abstract.php';

/**
 * Singleton Wrapper for the actuall mailer
 */
class CM5_Mailer
{
	/**
	 * Instance of the mailer object
	 * @var Zend_Mail
	 */
	private static $mailer = null;

	/**
	 * The transport mail engine.
	 * @var Zend_Mail_Transport_Abstract
	 */
	private $transport;

	/**
	 * Construct a new mailer object
	 * @param $transport The transoport engine that will be used to send mails
	 */
	private function __construct(Zend_Mail_Transport_Abstract $transport) {
		$this->transport = $transport;
	}

	/**
	 * Get the current transport
	 */
	public function getTransport()
	{
		return $this->transport;
	}

	/**
	 * Send a mail with current transport
	 * @param $mail The mail to be send.
	 */
	public function send(Zend_Mail $mail)
	{
		return $mail->send($this->transport);
	}

	/**
	 * Get the instance of this Mailer
	 * @return CM5_Mailer The actual mailing engine.
	 */
	public static function getInstance()
	{
		if (self::$mailer !== null)
			return self::$mailer;

		if (@CM5_Config::getInstance()->email->transport->protocol == 'smtp') {
			$options = CM5_Config::getInstance()->email->transport->toArray();
			foreach($options as $optname => $optval)
				if ($options[$optname] == '')
					unset($options[$optname]);
			$transport = new Zend_Mail_Transport_Smtp(CM5_Config::getInstance()->email->transport->host, $options);
		} else {
			$transport = new Zend_Mail_Transport_Sendmail();
		}
		
		self::$mailer = new self($transport);
		Zend_Mail::setDefaultTransport($transport);
		Zend_Mail::setDefaultFrom(CM5_Config::getInstance()->email->sender);
		return self::$mailer;
	}
}
