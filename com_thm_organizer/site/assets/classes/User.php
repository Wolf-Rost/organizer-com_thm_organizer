<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		User
 * @description User file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/auth.php';

/**
 * Class User for component com_thm_organizer
 *
 * Class provides methods to authenticate a user
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class User
{
	/**
	 * Username
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_username = null;

	/**
	 * Token
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_token = null;

	/**
	 * Password
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_passwd = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->username = $this->JDA->getRequest("username");
		$this->token    = $this->JDA->getRequest("token");
		$this->passwd   = $this->JDA->getRequest("passwd");
		$this->cfg = $CFG->getCFG();
	}

	/**
	 * Method to authenticate a user
	 *
	 * @return Array An array with information if the user is authenticated
	 */
	public function auth()
	{
		if (isset($this->cfg['AUTH_TEST_MODE']))
		{
			if ($this->cfg['AUTH_TEST_MODE'])
			{
				// HgNummer des Users - Ist die Id zum speichern des Stundenplans
				// Jede weitere Verarbeitung wird abgebrochen
				$_REQUEST  = array();

				// Rolle des Users - bestimmt mit welchen Rechten der User die Plaene sieht
				$role = 'registered';

				// Hier koennen doz, room, clas spezifische Rechte gesetzt werden - Alle angaben ergaenzen die RollenRechte
				$addRights = array(
							'doz' => array(
							'knei',
							'igle'
						)
				);

				// ALLES OK
				return array("success" => true, "data" => array(
						'username' => $this->username,
						'role' => $role, // User, registered, author, editor, publisher
						'additional_rights' => $addRights // 'doz' => array('knei', 'igle'), ...
				));
			}
		}

		// Nur Anfragen ueber HTTPS werden zugelassen -
		if (isset($this->cfg['REQUIRE_HTTPS']))
		{
			if ($this->cfg['REQUIRE_HTTPS'] && !strstr(strtolower($_SERVER['SERVER_PROTOCOL']), 'https'))
			{
				return array("success" => true, "data" => array(
						'error' => "Schwerer Fehler: Keine Verschl&Atilde;&frac14;sselte Verbindung vorhanden!"
				));
			}
		}

		// Nur Token Verifikation - Token ist die SessionId von Joomla und wird mit der DB verglichen
		if ($this->token)
		{
			/*
			* Ueberpruefung ob Token korrekt sind
			*/
			$auth = new Auth($this->JDA, $this->cfg);
			return array("data" => $auth->joomla($this->token));

			// Hier werden die Logindaten des Users gecheckt
		}
		elseif ($this->username && $this->passwd)
		{
			/*
			* Ueberpruefung ob Angaben korrekt sind
			*/
			$auth = new Auth($this->JDA, $this->cfg);
			return array("data" => $auth->ldap($this->username, $this->passwd));
		}

	}
}
