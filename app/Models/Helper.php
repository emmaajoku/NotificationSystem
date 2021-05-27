<?php

namespace App\Models;

use Carbon\Carbon;
use Collective\Html\FormFacade as Form;
use Collective\Html\HtmlFacade as Html;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Pusher\Pusher;
use ReCaptcha\ReCaptcha;

class Helper
{
    public static $pusher = null;
    public static $lsr = null;
    public static $tzs = null;
    public static $db_ip_html_ips_info = [];
    public static $dot_env_file = null;

    /**
     * @return array
     */
    public static function getPusherOptions()
    {
        return [
            'cluster'   => self::getDotEnvFileVar('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        ];
    }

    /**
     * @param $route
     * @param $text_content
     * @param array $route_args
     * @param array $html_attrs
     * @return HtmlString
     */
    public static function linkRoute($route, $text_content, $route_args = [], $html_attrs = [])
    {
        $html_string = Html::linkRoute($route, $text_content, $route_args, $html_attrs);

        if (self::isSecureRequest()) {
            $html_string = str_replace('href="http:', 'href="https:', $html_string);
        }

        return new HtmlString($html_string);
    }

    /**
     * @param $route
     * @param array $route_arguments
     * @param array $form_arguments
     * @return mixed
     */
    public static function openForm($route, $route_arguments = [], $form_arguments = [])
    {
        return Form::open(array_merge(
            [
                'autocomplete' => 'off',
                'name'         => $route,
            ],
            is_array($form_arguments) ? $form_arguments : [],
            [
                'url' => URL::to(
                    self::urlRemoveDomain(route($route, $route_arguments)),
                    [],
                    self::isSecureRequest()
                ),
            ]
        ));
    }

    /**
     * @return mixed
     */
    public static function closeForm()
    {
        return Form::close();
    }

    /**
     * @param $url
     * @return array|string|string[]|null
     */
    public static function urlRemoveDomain($url)
    {
        return is_string($url) ? preg_replace('#^https?://[^/]+(.*)#', '$1', $url) : '';
    }

    /**
     * @param $route
     * @return array|string|string[]|null
     */
    public static function route($route)
    {
        $url = route($route);

        return self::isSecureRequest() ? preg_replace('#^http(://.*)$#', 'https$1', $url) : $url;
    }

    /**
     * @return bool
     */
    public static function isSecureRequest()
    {
        $secure_by_header = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'];

        $mocked_secure_by_header = self::getTestEnvMockVar('isSecureRequest', $secure_by_header);

        return Request::secure() || $mocked_secure_by_header;
    }

    /**
     * @return mixed
     */
    public static function isGoogleReCaptchaEnabled()
    {
        $recaptcha_enabled_by_env = self::getDotEnvFileVar('GOOGLE_RECAPTCHA_ENABLED') === 'true';

        return self::getTestEnvMockVar('isGoogleReCaptchaEnabled', $recaptcha_enabled_by_env);
    }

    /**
     * @return bool
     */
    public static function isPusherEnabled()
    {
        return self::getDotEnvFileVar('PUSHER_ENABLED') === 'true';
    }

    /**
     * @param $status
     * @return bool
     */
    public static function isValidHTTPStatus($status)
    {
        return is_int($status) && $status > 99 && $status < 600;
    }

    /**
     * @param $status
     * @return bool
     */
    public static function isSuccessHTTPStatus($status)
    {
        return self::isValidHTTPStatus($status) && $status > 199 && $status < 300;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        $string = '';
        $length = is_numeric($length) && $length > 0 && $length < 32 ? $length : 10;
        while (strlen($string) < $length) {
            preg_match('/\w|[-$!@+=]/', chr(rand(32, 128)), $match) && (
            $string .= $match[0]
            );
        }

        return $string;
    }

    /**
     * @param $channel
     * @param $event
     * @param $message
     */
    public static function broadcast($channel, $event, $message)
    {
        if (self::isPusherEnabled()) {
            if (!self::$pusher) {
                self::$pusher = new Pusher(
                    self::getDotEnvFileVar('PUSHER_APP_KEY'),
                    self::getDotEnvFileVar('PUSHER_APP_SECRET'),
                    self::getDotEnvFileVar('PUSHER_APP_ID'),
                    self::getPusherOptions()
                );
            }

            self::$pusher->trigger($channel, $event, $message);
        } else {
            Sse::trigger($channel, $event, $message);
        }
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isPositiveInteger($value)
    {
        return is_numeric($value) && $value >= 0 && ~~$value === $value;
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function createCarbonRfc1123String($date)
    {
        return self::createCarbonDate($date)->toRfc1123String();
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function createCarbonDiffForHumans($date)
    {
        return self::createCarbonDate($date)->diffForHumans();
    }

    /**
     * @param $date
     * @return Carbon
     */
    public static function createCarbonDate($date)
    {
        return new Carbon($date); //, 'America/Sao_Paulo');
    }

    /**
     * @param $dot_env_file_configs
     * @return bool
     */
    public static function arePusherConfigsPresent($dot_env_file_configs)
    {
        if ($dot_env_file_configs['PUSHER_ENABLED'] !== 'true') {
            return true;
        }

        $is_app_id_present = isset($dot_env_file_configs['PUSHER_APP_ID']) && !empty($dot_env_file_configs['PUSHER_APP_ID']);
        $is_app_key_present = isset($dot_env_file_configs['PUSHER_APP_KEY']) && !empty($dot_env_file_configs['PUSHER_APP_KEY']);
        $is_app_secret_present = isset($dot_env_file_configs['PUSHER_APP_SECRET']) && !empty($dot_env_file_configs['PUSHER_APP_SECRET']);
        $is_app_cluster_present = isset($dot_env_file_configs['PUSHER_APP_CLUSTER']) && !empty($dot_env_file_configs['PUSHER_APP_CLUSTER']);

        return $is_app_id_present && $is_app_key_present && $is_app_secret_present && $is_app_cluster_present;
    }

    public static function arePusherConfigsValid($auth_key, $app_id, $cluster, $secret)
    {
        $auth_timestamp = time();
        $auth_version = '1.0';
        $querystring = implode('&', [
            'auth_key='.$auth_key,
            'auth_timestamp='.$auth_timestamp,
            'auth_version='.$auth_version,
        ]);
        $path = '/apps/'.$app_id.'/channels';
        $method = 'GET';
        $signature_payload = implode("\n", [$method, $path, $querystring]);
        $signature = hash_hmac('sha256', $signature_payload, $secret);
        $querystring .= '&auth_signature='.$signature;
        $api = 'http://api-'.$cluster.'.pusher.com'.$path.'?'.$querystring;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $json = json_decode($response);

        curl_close($ch);

        return $json instanceof \stdClass &&
            property_exists($json, 'channels') &&
            $http_code === 200;
    }

}
