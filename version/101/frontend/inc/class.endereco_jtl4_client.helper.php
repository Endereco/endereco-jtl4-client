<?php
/**
 * helper class for doing stuff
 *
 * @package     jtl_example_plugin
 * @author      Felix Moche <felix.moche@jtl-software.com
 * @copyright   2015 JTL-Software-GmbH
 */

/**
 * Class jtlExampleHelper
 */
class EndrecoJtl4ClientHelper
{
    /**
     * @var null|jtlExampleHelper
     */
    private static $_instance = null;

    /**
     * @var null|bool
     */
    private static $_isModern = null;

    /**
     * @var null|NiceDB
     */
    private $db = null;

    private $clientInfo;

    /**
     * @var null|Plugin
     */
    private $plugin = null;

    /**
     * constructor
     *
     * @param Plugin $oPlugin
     */
    public function __construct(Plugin $oPlugin)
    {
        $this->plugin = $oPlugin;
        //get database instance - do not do this, use Shop::DB()/$GLOBALS['DB'] instead
        if (self::isModern()) {
            $this->db = Shop::DB();
        } else {
            $this->db = $GLOBALS['DB'];
        }
        $this->clientInfo = 'Endereco JTL4 Client v' . $oPlugin->nVersion;
    }

    /**
     * singleton getter
     *
     * @param Plugin $oPlugin
     * @return jtlExampleHelper
     */
    public static function getInstance(Plugin $oPlugin)
    {
        return (self::$_instance === null) ? new self($oPlugin) : self::$_instance;
    }

    /**
     * Converts hex color code to rgb.
     *
     * @param string $colorCode color code as hex.
     *
     * @return array color code as rgb..
     */
    public function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return [$r, $g, $b];
    }

    /**
     * check if there is a current shop version installed
     *
     * @return bool
     */
    public static function isModern()
    {
        if (self::$_isModern === null) {
            //cache the actual value as class variable
            self::$_isModern = version_compare(JTL_VERSION, 400, '>=') && class_exists('Shop');
        }

        return self::$_isModern;
    }

    public function findSessions() {
        $accountableSessionIds = array();
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            foreach ($_POST as $sVarName => $sVarValue) {
                if ((strpos($sVarName, '_session_counter') !== false) && 0 < intval($sVarValue)) {
                    $sSessionIdName = str_replace('_session_counter', '', $sVarName) . '_session_id';
                    $accountableSessionIds[$_POST[$sSessionIdName]] = true;
                }
            }
            $accountableSessionIds = array_keys($accountableSessionIds);
        }
        return $accountableSessionIds;
    }

    public function doAccountings($sessionIds) {

        // Get sessionids.
        if (!$sessionIds) {
            return;
        }

        $anyDoAccounting = false;

        foreach ($sessionIds as $sessionId) {
            try {
                $message = array(
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'doAccounting',
                    'params' => array(
                        'sessionId' => $sessionId
                    )
                );
                $newHeaders = array(
                    'Content-Type' => 'application/json',
                    'X-Auth-Key' => $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key'],
                    'X-Transaction-Id' => $sessionId,
                    'X-Transaction-Referer' => $_SERVER['HTTP_REFERER'],
                    'X-Agent' => $this->clientInfo,
                );
                $this->sendRequest($message, $newHeaders);
                $anyDoAccounting = true;

            } catch(\Exception $e) {
                $this->logger->addError($e->getMessage());
            }
        }

        if ($anyDoAccounting) {
            try {
                $message = array(
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'doConversion',
                    'params' => array()
                );
                $newHeaders = array(
                    'Content-Type' => 'application/json',
                    'X-Auth-Key' => $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key'],
                    'X-Transaction-Id' => 'not_required',
                    'X-Transaction-Referer' => $_SERVER['HTTP_REFERER'],
                    'X-Agent' => $this->clientInfo,
                );
                $this->sendRequest($message, $newHeaders);
            } catch(\Exception $e) {
                // Do nothing.
            }
        }
    }

    public function sendRequest($body, $headers) {
        $serviceUrl = $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_remote_url'];
        $ch = curl_init(trim($serviceUrl));
        $dataString = json_encode($this->utf8_encode_array($body));

        $parsedHeaders = array();
        foreach ($headers as $headerName=>$headerValue) {
            $parsedHeaders[] = $headerName . ': ' . $headerValue;
        }
        $parsedHeaders[] = 'Content-Length: ' . strlen($dataString);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            $parsedHeaders
        );

        $result = json_decode(curl_exec($ch), true);
        return $this->utf8_decode_array($result);
    }

    public function generateSesionId() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function utf8_encode_array($array) {
        if (!is_array($array)) {
            return [];
        }
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $array[$key] = $this->utf8_encode_array($value);
            }
            else {
                $array[$key] = utf8_encode($value);
            }
        }
        return $array;
    }

    public function utf8_decode_array($array) {
        if (!is_array($array)) {
            return [];
        }
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $array[$key] = $this->utf8_decode_array($value);
            }
            else {
                $array[$key] = utf8_decode($value);
            }
        }
        return $array;
    }

    public function checkAddress($address) {
        // Do addresscheck
        $sessionIds = [];
        $meta = [];
        try {
            $sessionId = $this->generateSesionId();
            if (empty(trim($address->cHausnummer))) {
                $message = array(
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'addressCheck',
                    'params' => array(
                        'language' => 'de',
                        'country' => strtolower($address->cLand),
                        'postCode' => $address->cPLZ,
                        'cityName' => $address->cOrt,
                        'streetFull' => $address->cStrasse
                    )
                );
            } else {
                $message = array(
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'addressCheck',
                    'params' => array(
                        'language' => 'de',
                        'country' => strtolower($address->cLand),
                        'postCode' => $address->cPLZ,
                        'cityName' => $address->cOrt,
                        'street' => $address->cStrasse,
                        'houseNumber' => $address->cHausnummer,
                    )
                );
            }

            $newHeaders = array(
                'Content-Type' => 'application/json',
                'X-Auth-Key' => $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key'],
                'X-Transaction-Id' => $sessionId,
                'X-Transaction-Referer' => $_SERVER['HTTP_REFERER'],
                'X-Agent' => $this->clientInfo,
            );
            $result = $this->sendRequest($message, $newHeaders);

            // Save status and predictions
            if (array_key_exists('result', $result)) {
                $sessionIds[] = $sessionId;

                $meta = [
                    'ts' => time(),
                    'status' => $result['result']['status'],
                    'predictions' => [],
                ];

                foreach ($result['result']['predictions'] as $prediction) {
                    $tempAddress = array(
                        'countryCode' => !empty($prediction['countryCode'])?$prediction['countryCode']:strtolower($address->cLand),
                        'postalCode' => $prediction['postCode'],
                        'locality' => $prediction['cityName'],
                        'streetName' => $prediction['street'],
                        'buildingNumber' => $prediction['houseNumber']
                    );
                    $meta['predictions'][] = $tempAddress;
                }

            }
        } catch(\Exception $e) {
            // Do nothing.
        }

        $this->doAccountings($sessionIds);

        return $meta;
    }

    public function checkCustomersAddresses($customerId) {
        $sessionIds = [];
        if ($customerId) {
            // Get address details
            $tkunde = Shop::DB()->queryPrepared(
                "SELECT `tkunde`.*
FROM `tkunde`
LEFT JOIN `xplugin_endereco_jtl4_client_tams` ON `xplugin_endereco_jtl4_client_tams`.`kKunde` = `tkunde`.`kKunde`
                WHERE `tkunde`.`kKunde` = :id AND `xplugin_endereco_jtl4_client_tams`.`kKunde` IS NULL",
                [':id' => $customerId],
                1
            );

            if ($tkunde) {
                // Do addresscheck
                try {
                    $sessionId = $this->generateSesionId();
                    $message = array(
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => 'addressCheck',
                        'params' => array(
                            'language' => 'de',
                            'country' => strtolower($tkunde->cLand),
                            'postCode' => $tkunde->cPLZ,
                            'cityName' => $tkunde->cOrt,
                            'street' => trim(entschluesselXTEA($tkunde->cStrasse)),
                            'houseNumber' => $tkunde->cHausnummer,
                        )
                    );
                    $newHeaders = array(
                        'Content-Type' => 'application/json',
                        'X-Auth-Key' => $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key'],
                        'X-Transaction-Id' => $sessionId,
                        'X-Transaction-Referer' => $_SERVER['HTTP_REFERER'],
                        'X-Agent' => $this->clientInfo,
                    );
                    $result = $this->sendRequest($message, $newHeaders);
                    // Save status and predictions
                    if (array_key_exists('result', $result)) {

                        // Create an array of predictions.
                        $predictions = array();
                        $maxPredictions = 3;
                        $counter = 0;
                        foreach ($result['result']['predictions'] as $prediction) {
                            $tempAddress = array(
                                'countryCode' => !empty($prediction['countryCode'])?$prediction['countryCode']:strtolower($tkunde->cLand),
                                'postalCode' => $prediction['postCode'],
                                'locality' => $prediction['cityName'],
                                'streetName' => $prediction['street'],
                                'buildingNumber' => $prediction['houseNumber']
                            );
                            if (array_key_exists('additionalInfo', $prediction)) {
                                $tempAddress['additionalInfo'] = $prediction['additionalInfo'];
                            }

                            $predictions[] = $tempAddress;
                            $counter++;
                            if ($counter >= $maxPredictions) {
                                break;
                            }
                        }

                        Shop::DB()->queryPrepared(
                            "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                            (`kKunde`,`kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                         VALUES 
                            (:kKunde, NULL, NULL, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                        ON DUPLICATE KEY UPDATE    
                           `kKunde`=:kKunde, `enderecoamsts`=:enderecoamsts, `enderecoamsstatus`=:enderecoamsstatus, `enderecoamspredictions`=:enderecoamspredictions, `last_change_at`=now()
                        ",
                            [
                                ':kKunde' => $customerId,
                                ':enderecoamsts' => time(),
                                ':enderecoamsstatus' => implode(',', $this->calculateStatuses($result['result']['status'])),
                                ':enderecoamspredictions' => json_encode($this->utf8_encode_array($predictions)),
                            ],
                            1
                        );

                        $sessionIds[] = $sessionId;
                    }
                } catch(\Exception $e) {
                    // Do nothing.
                }
            }

            // Get address details
            $tlieferadressen = Shop::DB()->queryPrepared(
                "SELECT `tlieferadresse`.*
                FROM `tlieferadresse`
                LEFT JOIN `xplugin_endereco_jtl4_client_tams` ON `xplugin_endereco_jtl4_client_tams`.`kLieferadresse` = `tlieferadresse`.`kLieferadresse`
                WHERE `tlieferadresse`.`kKunde` = ? AND `xplugin_endereco_jtl4_client_tams`.`kKunde` IS NULL
                ORDER BY `tlieferadresse`.`kLieferadresse` DESC",
                [$customerId],
                9
            );

            foreach ($tlieferadressen as $tlieferadresse) {

                // Do addresscheck
                try {
                    $sessionId = $this->generateSesionId();
                    $message = array(
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => 'addressCheck',
                        'params' => array(
                            'language' => 'de',
                            'country' => strtolower($tlieferadresse['cLand']),
                            'postCode' => $tlieferadresse['cPLZ'],
                            'cityName' => $tlieferadresse['cOrt'],
                            'street' => trim(entschluesselXTEA($tlieferadresse['cStrasse'])),
                            'houseNumber' => $tlieferadresse['cHausnummer'],
                        )
                    );
                    $newHeaders = array(
                        'Content-Type' => 'application/json',
                        'X-Auth-Key' => $this->plugin->oPluginEinstellungAssoc_arr['endereco_jtl4_client_api_key'],
                        'X-Transaction-Id' => $sessionId,
                        'X-Transaction-Referer' => $_SERVER['HTTP_REFERER'],
                        'X-Agent' => $this->clientInfo,
                    );
                    $result = $this->sendRequest($message, $newHeaders);

                    // Save status and predictions
                    if (array_key_exists('result', $result)) {

                        // Create an array of predictions.
                        $predictions = array();
                        $maxPredictions = 3;
                        $counter = 0;
                        foreach ($result['result']['predictions'] as $prediction) {
                            $tempAddress = array(
                                'countryCode' => !empty($prediction['countryCode'])?$prediction['countryCode']:strtolower($tlieferadresse['cLand']),
                                'postalCode' => $prediction['postCode'],
                                'locality' => $prediction['cityName'],
                                'streetName' => $prediction['street'],
                                'buildingNumber' => $prediction['houseNumber']
                            );
                            if (array_key_exists('additionalInfo', $prediction)) {
                                $tempAddress['additionalInfo'] = $prediction['additionalInfo'];
                            }

                            $predictions[] = $tempAddress;
                            $counter++;
                            if ($counter >= $maxPredictions) {
                                break;
                            }
                        }

                        Shop::DB()->queryPrepared(
                            "INSERT INTO `xplugin_endereco_jtl4_client_tams` 
                            (`kKunde`,`kRechnungsadresse`, `kLieferadresse`, `enderecoamsts`, `enderecoamsstatus`, `enderecoamspredictions`, `last_change_at`)
                         VALUES 
                            (NULL, NULL, :kLieferadresse, :enderecoamsts, :enderecoamsstatus, :enderecoamspredictions, now())
                        ON DUPLICATE KEY UPDATE    
                           `kLieferadresse`=:kLieferadresse, `enderecoamsts`=:enderecoamsts, `enderecoamsstatus`=:enderecoamsstatus, `enderecoamspredictions`=:enderecoamspredictions, `last_change_at`=now()
                        ",
                            [
                                ':kLieferadresse' => $tlieferadresse['kLieferadresse'],
                                ':enderecoamsts' => time(),
                                ':enderecoamsstatus' => implode(',', $this->calculateStatuses($result['result']['status'])),
                                ':enderecoamspredictions' =>  json_encode($this->utf8_encode_array($predictions)),
                            ],
                            1
                        );

                        $sessionIds[] = $sessionId;
                    }
                } catch(\Exception $e) {
                    // Do nothing.
                }
            }
        }

        // Save the sessionid for doAccounting
        $this->doAccountings($sessionIds);
    }


    public function calculateStatuses($statusCodes) {
        if (
            in_array('A1000', $statusCodes) &&
            !in_array('A1100', $statusCodes)
        ) {
            if (!in_array('address_correct', $statusCodes)) {
                $statusCodes[] = 'address_correct';
            }
        }
        if (
            in_array('A1000', $statusCodes) &&
            in_array('A1100', $statusCodes)
        ) {
            if (!in_array('address_needs_correction', $statusCodes)) {
                $statusCodes[] = 'address_needs_correction';
            }
        }
        if (
        in_array('A2000', $statusCodes)
        ) {
            if (!in_array('address_multiple_variants', $statusCodes)) {
                $statusCodes[] = 'address_multiple_variants';
            }
        }
        if (
        in_array('A3000', $statusCodes)
        ) {
            if (!in_array('address_not_found', $statusCodes)) {
                $statusCodes[] = 'address_not_found';
            }
        }
        if (
        in_array('A3100', $statusCodes)
        ) {
            if (!in_array('address_is_packstation', $statusCodes)) {
                $statusCodes[] = 'address_is_packstation';
            }
        }

        return $statusCodes;
    }
}
