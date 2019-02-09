<?php
/**
 * Copyright (C) 2015  Peter Lind
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
 * PHP version 5
 *
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

require_once LIB_FOLDER . 'swift/lib/swift_required.php';

/**
 * wrapper for SwiftMailer
 *
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 */
class Mail
{
    /**
     * stores the message to send
     *
     * @var object
     */
    private $message;

    /**
     * cache of attached entities
     *
     * @var array
     */
    private $attachments = array();

    /**
     * config service
     *
     * @var Config
     */
    private $config;

    /**
     * set up a message for sending
     *
     * @throws MailException
     * @access public
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        try {
            $this->message = Swift_Message::newInstance();

        } catch (Exception $e) {
            throw new MailException("Failed to create new message using SwiftMailer. Exception message: {$e->getMessage()}");
        }
    }

    /**
     * sets the sender address
     *
     * @param string $address From address
     * @param string $alias   From alias, optional 
     *
     * @throws MailException
     * @access public
     * @return $this
     */
    public function setFrom($address, $alias = '')
    {
        if (!is_string($address) || !$this->validateEmail($address)) {
            throw new MailException("From address is invalid: " . gettype($address));
        }

        $this->message->setFrom($alias ? [$address => $alias] : $address);

        return $this;
    }

    /**
     * sets the recipient address
     *
     * @param string $address To address
     *
     * @throws MailException
     * @access public
     * @return $this
     */
    public function setRecipient($address)
    {
        if (!is_string($address) || !$this->validateEmail($address)) {
            throw new MailException("Recipient address is invalid: " . gettype($from));
        }

        $this->message->setTo($address);

        return $this;
    }

    /**
     * sets the subject of the mail
     *
     * @param string $subject Subject line of the email
     *
     * @access public
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->message->setSubject((string) $subject);

        return $this;
    }

    /**
     * sets a plain text body for the email
     *
     * @param string $message Plain text message to set
     *
     * @access public
     * @return $this
     */
    public function setPlainTextBody($message)
    {
        $this->message->setBody((string) $message, 'text/plain');

        return $this;
    }

    /**
     * adds a html part to the message
     *
     * @param string $message Html message to set
     *
     * @access public
     * @return $this
     */
    public function setHtmlBody($message) {
        $this->message->addPart((string) $message, 'text/html');

        return $this;
    }

    /**
     * uses a Page object to set the body, both plain text and html
     *
     * @param Page $page Page object to render
     *
     * @access public
     * @return $this
     */
    public function setBodyFromPage(Page $page)
    {
        $html = $page->render();

        return $this->setPlainTextBody(strip_tags($html))
            ->setHtmlBody($html);
    }

    public function addAttachmentFile($filename, $content_type = null)
    {
        if (!is_file($filename)) {
            throw new MailException('Cannot access file to add as attachment');
        }

        $this->attachments[] = $attachment = Swift_Attachment::newInstance(file_get_contents($filename), basename($filename), $content_type);
        $this->message->attach($attachment);

        return $this;
    }

    /**
     * returns the source for embedding the file
     *
     * @param string $filename Path of file
     *
     * @access public
     * @return string
     */
    public function embedImage($filename)
    {
        if (!is_file($filename)) {
            throw new MailException('Cannot access file to add as attachment');
        }

        return $this->message->embed(Swift_Image::fromPath($filename));
    }

    /**
     * sends the previously prepared email
     *
     * @throws MailException
     * @access public
     * @return bool
     */
    public function send()
    {
        if (!isset($this->message)) {
            throw new MailException("No message set");
        }

        $transport = Swift_SmtpTransport::newInstance($this->config->get('email.host'), $this->config->get('email.port'));
        return !!Swift_Mailer::newInstance($transport)->send($this->message);
    }

    /**
     * attempts to validate one or more email addresses
     *
     * @param string $email Address to validate
     *
     * @access public
     * @return bool
     */
    protected function validateEmail($email)
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
