<?php
/*
    Class to send push notifications using Google Cloud Messaging for Android

    Example usage
    -----------------------
    $an = new GCMPushMessage($apiKey);
    $an->setDevices($devices);
    $response = $an->send($message);
    -----------------------
    
    $apiKey Your GCM api key
    $devices An array or string of registered device tokens
    $message The mesasge you want to push out

    @author Matt Grundy

    Adapted from the code available at:
    http://stackoverflow.com/questions/11242743/gcm-with-php-google-cloud-messaging

*/
class GcmPushMessage
{
    const SEND_SUCCESS        = 1;
    const SEND_TEMPORARY_FAIL = 2;
    const SEND_ABSOLUTE_FAIL  = 3;
    const SEND_REPLACE_ID     = 4;

    private $url = 'https://android.googleapis.com/gcm/send';
    private $serverApiKey = '';
    private $devices = array();
    
    /**
     * public constructor
     *
     * @param string $apikey Public API key for server
     *
     * @access public
     */
    public function __construct($apikey)
    {
        $this->serverApiKey = $apikey;
    }

    /**
     * sets the device ids to send message to
     *
     * @param array $device_ids Array of device ids
     *
     * @access public
     * @return $this
     */
    public function setDevices(array $device_ids)
    {
        $this->devices = $device_ids;

        return $this;
    }

    /**
     * sends the message to the set devices
     *
     * @param string $message Message to send
     * @param array  $data    Extra data, like title
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function send($message, $data = false)
    {
        if (!is_array($this->devices) || count($this->devices) == 0) {
            throw new Exception('No devices set');
        }
        
        if (empty($this->serverApiKey)) {
            throw new Exception('Server API Key not set');
        }
        
        $fields = array(
            'registration_ids'  => $this->devices,
            'data'              => array('message' => $message),
        );
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $fields['data'][$key] = $value;
            }
        }

        $headers = array( 
            'Authorization: key=' . $this->serverApiKey,
            'Content-Type: application/json'
        );

        // Open connection
        $ch = curl_init();
        
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $this->url);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        // Avoids problem with https certificate
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        // Execute post
        $result = curl_exec($ch);
        
        // Close connection
        curl_close($ch);

        if ($result) {
            $result_data = json_decode($result, true);

            if (isset($result_data['failure']) && $result_data['failure'] === 0 && isset($result_data['canonical_ids']) && $result_data['canonical_ids'] === 0) {
                return array(self::SEND_SUCCESS, null, $result);
            }

            if (isset($result_data['results'])) {
                $replacements = array();

                foreach ($result_data['results'] as $index => $set) {
                    if (isset($set['message_id']) && !empty($set['registration_id'])) {
                        return array(self::SEND_REPLACE_ID, $set['registration_id'], $result);
                    }

                    if (!empty($set['error'])) {
                        if ($set['error'] === 'Unavailable') {
                            return array(self::SEND_TEMPORARY_FAIL, null, $result);
                        }

                        return array(self::SEND_ABSOLUTE_FAIL, null, $result);

                    }

                }

            }


        }

        return array(self::SEND_ABSOLUTE_FAIL, null, $result);
    }
}
