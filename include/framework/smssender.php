<?php
/**
 * Copyright (C) 2013 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2013 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * app class - setups up everything and sandboxes it
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class SMSSender implements SMSSending
{
    private $entify_factory;

    private $config;

    private $verified_config;

    private $service_user;

    private $service_password;

    /**
     * public constructor
     *
     * @param EntityFactory $entity_factory Entity factory
     * @param Config        $config         Config helper
     *
     * @access public
     * @return void
     */
    public function __construct(EntityFactory $entity_factory, Config $config)
    {
        $this->entity_factory  = $entity_factory;
        $this->config          = $config;

        $this->verified_config = false;
    }

    /**
     * checks if the number can be sent to
     *
     * @param string $number Phone number to check
     *
     * @access public
     * @return bool
     */
    public function checkNumber($number)
    {
        $trimmed = preg_replace('/[^\d]/', '', $number);
        $trimmed = (strlen($trimmed) == 10 && substr($trimmed, 0, 2) == '45') ? substr($trimmed, 2) : $trimmed;

        if (strlen($trimmed) != 8) {
            return false;
        }

        $trimmed = '45' . $trimmed;

        return $this->entity_factory->create('SMSLog')->safetyCheck($trimmed) ? $trimmed : false;
    }

    /**
     * checks that we are currently within the
     * period when messages should be sent
     *
     * @access public
     * @return bool
     */
    public function safetyCheck()
    {
        $start = new DateTime($this->config->get('con.start'));
        $end   = new DateTime($this->config->get('con.end'));

        return time() > $start->getTimestamp() && time() < $end->getTimestamp();
    }

    /**
     * method docblock
     *
     * @param
     *
     * @throws FrameworkException
     * @access protected
     * @return void
     */
    protected function verifyConfig()
    {
        $this->service_user     = $this->config->get('sms.user');
        $this->service_password = $this->config->get('sms.pass');

        if (empty($this->service_user) || empty($this->service_password)) {
            throw new FrameworkException('Lacking config values for SMS service');
        }

        $this->verified_config  = true;
    }

    /**
     * creates the url for sending the message
     *
     * @param DBObject $participant Participant to send to
     * @param string   $number      Number to send to
     * @param string   $message     Message to send
     *
     * @throws FrameworkException
     * @access protected
     * @return void
     */
    protected function createUrl(DBObject $participant, $number, $message)
    {
        return $this->config->get('sms.baseurl') . '?username=' . $this->service_user . '&from=Fastaval&apikey=' . $this->service_password . '&recipient=' . $number . '&utf8=1&message=' . urlencode($message);
    }

    /**
     * sends a message to a recipient
     *
     * @param DBObject $participant Participant to send to
     * @param string   $number      Number to send to
     * @param string   $message     Message to send
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function sendMessage(DBObject $participant, $number, $message)
    {
        if (!$this->verified_config) {
            $this->verifyConfig();
        }

        $message = iconv(mb_detect_encoding($message, mb_detect_order(), false), "UTF-8//IGNORE", $message);
        $url = $this->createUrl($participant, $number, $message);

        //$result = 'OK';
        $result = file_get_contents($url); // afsendelse

        return $result === '<succes>SMS succesfully sent to 1 recipient(s)</succes>';
    }
}
