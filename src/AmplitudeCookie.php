<?php

namespace AmplitudeExperiment;

use AmplitudeExperiment\Logger\DefaultLogger;
use AmplitudeExperiment\Logger\InternalLogger;
use AmplitudeExperiment\Logger\LogLevel;
use Exception;

require_once __DIR__ . '/Util.php';

class AmplitudeCookie
{
    /**
     * @param string $amplitudeApiKey The Amplitude API Key
     * @param bool $newFormat True if the cookie is in the Browser SDK 2.0 format
     * @return string The cookie name that Amplitude sets for the provided Amplitude API Key
     * @throws Exception
     */
    public static function cookieName(string $amplitudeApiKey, bool $newFormat = false): string
    {
        if ($newFormat) {
            if (strlen($amplitudeApiKey) < 10) {
                throw new Exception('Invalid Amplitude API Key');
            } else {
                return 'AMP_' . substr($amplitudeApiKey, 0, 10);
            }
        }

        if (strlen($amplitudeApiKey) < 6) {
            throw new Exception('Invalid Amplitude API Key');
        }

        return 'amp_' . substr($amplitudeApiKey, 0, 6);
    }

    /**
     * @param string $amplitudeCookie A string from the amplitude cookie
     * @param bool $newFormat True if the cookie is in the Browser SDK 2.0 format
     * @return array An array containing device_id and user_id (if available)
     */
    public static function parse(string $amplitudeCookie, bool $newFormat = false): array
    {
        if ($newFormat) {
            $decoding = base64_decode($amplitudeCookie);
            $decoded = urldecode($decoding);

            try {
                $userSession = json_decode($decoded, true);
                return [
                    'deviceId' => $userSession['deviceId'],
                    'userId' => $userSession['userId'] ?? null,
                ];
            } catch (\Exception $e) {
                $logger = new InternalLogger(new DefaultLogger(), LogLevel::INFO);
                $logger->error("Error parsing the Amplitude cookie: '{$amplitudeCookie}'. " . $e->getMessage());
                return [];
            }
        }

        $values = explode('.', $amplitudeCookie);
        $user_id = null;

        if (!empty($values[1])) {
            try {
                $user_id = base64_decode($values[1]);
            } catch (\Exception $e) {
                $user_id = null;
            }
        }

        return [
            'deviceId' => $values[0],
            'userId' => $user_id,
        ];
    }

    /**
     * Generates a cookie string to set for the Amplitude Browser SDK
     * @param string $deviceId A device id to set
     * @param bool $newFormat True if the cookie is in the Browser SDK 2.0 format
     * @return string A cookie string to set for the Amplitude Browser SDK to read
     */
    public static function generate(string $deviceId, bool $newFormat = false): string
    {
        if (!$newFormat) {
            return $deviceId . '..........';
        }

        $userSessionHash = [
            'deviceId' => $deviceId,
        ];

        $json_data = json_encode($userSessionHash);
        $encoded_json = urlencode($json_data);
        return base64_encode($encoded_json);
    }
}
