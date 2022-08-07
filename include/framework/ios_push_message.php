<?php
class IosPushMessage
{
    const SEND_SUCCESS = 1;
    const SEND_FAIL    = -1;

    private $certificate_path;

    private $device_key;
    
    /**
     * public constructor
     *
     * @param string $apikey Public API key for server
     *
     * @access public
     */
    public function __construct($certificate_path, $device_key)
    {
        $this->certificate_path = $certificate_path;
        $this->device_key       = $device_key;
    }

    /**
     * sends the message to the set devices
     *
     * @param string $title   Title of message
     * @param string $message Message to send
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function send($title, $message)
    {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->certificate_path);
        stream_context_set_option($ctx, 'ssl', 'passphrase', '');

        // Open a connection to the APNS server
        $fp = stream_socket_client(
        'ssl://gateway.push.apple.com:2195', $err,
        $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
            throw new Exception('Could not create file handle for sending iOS message. ' . $err . ' ' . $errstr);
        }

        // Create the payload body
        $body['aps'] = array(
            'alert'    => $title . PHP_EOL . $message,
            'sound'    => 'default',
            'link_url' => 'https://infosys.fastaval.dk',
        );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $this->device_key) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        // Close the connection to the server
        fclose($fp);

        return $result ? self::SEND_SUCCESS : self::SEND_FAIL;
    }
}
