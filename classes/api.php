<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event handler definition for local_tlconnect.
 *
 * @package local_tlconnect
 * @author Marcus Green
 * @copyright 2020 Titus Learning
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tlconnect;

defined('MOODLE_INTERNAL') || die;

use curl;
use stdClass;

require_once($CFG->libdir.'/filelib.php');

class api {

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const METHOD_POST_NOFORM = 'post_noform';
    const METHOD_POST_JSON = 'post_json';
    const METHOD_DELETE = 'delete';
    const METHOD_PUT = 'put';
    const METHOD_PATCH = 'patch';

    /**
     * @var authentication
     */
    private $authentication;

    /**
     * Private constructor - requires instance singleton accessor.
     * api constructor.
     */
    private function __construct() {
        $this->authentication = authentication::instance();
    }

    /**
     * Singleton accessor.
     * @return api
     */
    public static function instance() {
        static $api = null;
        if ($api !== null) {
            return $api;
        }
        $api = new api();
        return $api;
    }

    /**
     * Make API call.
     * @param string $resource
     * @param array $params
     * @param string $method
     * @param array|null $expectedreturnprops
     * @param int $relateduserid
     * @return mixed
     * @throws \moodle_exception
     */
    public function call(
        string $apiurl,
        array $params = [],
        string $method = 'get',
        string $loginfo = null) {
        $curl = new curl();

        // The token param is sentence cased in all the TBC examples.
        $token = $this->authentication->get_token();
        $curl->setHeader( 'Authorization: Bearer ' . $token);

        $logtext = $loginfo. ' ';

        if ($method === self::METHOD_GET) {
            $response = $curl->get($apiurl, $params);
        } else if ($method === self::METHOD_POST) {
            $response = $curl->post($apiurl, $params);
        } else if ($method === self::METHOD_PATCH) {
             $params = json_encode($params);
             $curl->setHeader('Content-Type: application/json');
             $response = $curl->patch($apiurl, $params);
        } else if ($method === self::METHOD_POST_NOFORM) {
            $urlparams = http_build_query($params, '', '&');
            $response = $curl->post($apiurl . '?' . $urlparams);
        } else if ($method === self::METHOD_POST_JSON) {
            $curl->setHeader('Content-Type: application/json');
            $response = $curl->post($apiurl, json_encode($params));
        } else if ($method === self::METHOD_DELETE) {
            $response = $curl->delete($apiurl, $params);
        } else if ($method === self::METHOD_PUT) {
            $response = $curl->put($apiurl, $params);
        }

        $info = $curl->getResponse();
        $respcode = $info['HTTP/1.1'];
        $codenumber = intval($respcode);
        if (!in_array($codenumber, [200, 201])) {
            $logtext .= ' Not 200 or 201: responsecode:'.$respcode. ' response :'.$response;
        }


        $decoded = json_decode($response);
        if (!$decoded instanceof stdClass && !is_array($decoded) ) {
            $logtext .= $loginfo.' Jsonnotdecoded:'.$response;
        }

        if (!empty($expectedprops)) {
            foreach ($expectedprops as $prop) {
                if (!isset($decoded->$prop)) {
                    $logtext .= $loginfo.' Missing property:'.$prop. ': '.$response;
                }
            }
        }

        $logtext .= $response;
        $context = \context_system::instance();
        $params = [
            'context' => $context,
            'other' => $logtext
        ];
        $calledevent = \local_tlconnect\event\api_called::create(
            $params
        );

        $calledevent->trigger();
        return $decoded;

    }
    /**
     * Requires a token to exist so is proxy
     * for successful connection.
     *
     */
    public function has_error() {
        $errorstate = $this->authentication->get_errorstate();
        if ($errorstate) {
            return $errorstate;
        } else {
            return false;
        }
    }
    /**
     * Make API call.
     * @param string $resource
     * @param array $params
     * @param string $method
     * @param array|null $expectedreturnprops
     * @return mixed
     * @throws \moodle_exception
     */
    public function call_no_die(
        string $resource,
        array $params = [],
        string $method = 'get',
        ?array $expectedreturnprops = null) {
        try {
            $decoded = $this->call($resource, $params, $method, $expectedreturnprops);
            return $decoded;
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Make a test call without authentication.
     * @return bool
     * @throws \moodle_exception
     */
    public function test_no_login() {
        $data = $this->call('data');
        return !empty($data) && isset(reset($data)->label);
    }
}
