<?php
class FirebaseMessage
{
    const SEND_SUCCESS = 1;
    const SEND_FAIL    = -1;

    private $server_key;

    private $device_key;
    
    /**
     * public constructor
     *
     * @param string $apikey Public API key for server
     *
     * @access public
     */
    public function __construct($server_key, $device_key)
    {
        $this->server_key = $server_key;
        $this->device_key = $device_key;
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
        $payload = [
            'notification' => [
                'title' => $title,
                'body'  => $message,
            ],
            'data' => [
                'EXTRA_NOTIFICATION_TITLE'   => $title,
                'EXTRA_NOTIFICATION_MESSAGE' => $message,
            ],
            'to' => $this->device_key,
        ];
 
        $headers = [
            'Authorization: key=' . $this->server_key,
            'Content-Type: application/json'
        ];
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);

        curl_close($ch);

        if ($result) {
            $result_data = json_decode($result, true);

            if (isset($result_data['failure']) && $result_data['failure'] === 0 && isset($result_data['canonical_ids']) && $result_data['canonical_ids'] === 0) {
                return self::SEND_SUCCESS;
            }

            if (isset($result_data['results'])) {
                $replacements = array();

                foreach ($result_data['results'] as $index => $set) {
                    if (isset($set['message_id']) && !empty($set['registration_id'])) {
                        return self::SEND_FAIL;
                    }

                    if (!empty($set['error'])) {
                        if ($set['error'] === 'Unavailable') {
                            return self::SEND_FAIL;
                        }

                        return self::SEND_FAIL;

                    }

                }

            }


        }

        return self::SEND_FAIL;
    }
}
