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
 * Authentication for local_tlconnect.
 *
 * @package local_datasender
 * @author Marcus Green
 * @copyright 2021 Titus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tlconnect;

defined('MOODLE_INTERNAL') || die;

use stdClass;

require_once($CFG->libdir.'/filelib.php');

class authentication {

    /**
     * @var stdClass
     */
    private $token;

    /**
     * @var stdClass
     */
    private $config;

    /**
     * @var null|string
     */
    private $errorstate;

    /**
     * authentication constructor.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    private function __construct() {
        $this->config = get_config('local_tlconnect');
        $this->validate_config();
        $this->token = $this->request_token();
    }

    /**
     * @return authentication, singleton
     */
    public static function instance(): authentication {
        static $auth = null;
        if ($auth !== null) {
            return $auth;
        }
        $auth = new authentication();
        return $auth;
    }

    /**
     * Request a token.
     * @return stdClass
     * @throws \moodle_exception
     */
    private function request_token(): stdClass {
        $conf = $this->config;
        $curl = new \curl();
        $post = [
            'grant_type' => 'password',
            'client_id' => $conf->apiclientid,
            'client_secret' => $conf->apiclientsecret,
            'username' => $conf->apiusername,
            'password' => $conf->apiuserpwd
        ];

        $response = $curl->post($conf->accesstokenurl, $post);
        $info = $curl->getResponse();
        if (intval($info['HTTP/1.1']) !== 200) {
            $this->errorstate = get_string('error:authunexpectedresponsecode', 'local_tlconnect', $info['HTTP/1.1']);
            print_error('error:authunexpectedresponsecode', 'local_tbc', '', $info['HTTP/1.1']);
        }
        $obj = json_decode($response);
        $expectedprops = [
            'access_token',
            'instance_url'
        ];
        foreach ($expectedprops as $prop) {
            if (!isset($obj->$prop)) {
                print_error('error:responsemissingexpectedproperty', 'local_tbc', '', $prop);
            }
        }
        if (!$obj instanceof stdClass) {
            print_error('error:responsejsonnotdecoded', 'local_tbc', '', $response);
        }
        return $obj;
    }

    /**
     * @return string
     */
    public function get_token(): string {
        return $this->token->access_token;
    }

    /**
     * @return string
     */
    public function get_apiurl(): string {
        return $this->token->instance_url;
    }
    /**
     * @return null|string
     */
    public function get_errorstate(): ?string {
        return $this->errorstate;
    }
    /**
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function validate_config() {
        $mandatoryprops = [
            'oauthurl',
            'apiclientid',
            'apiclientsecret',
            'accesstokenurl'
        ];

        $config = get_config('local_tlconnect');
        foreach ($mandatoryprops as $prop) {
            if (empty($config->$prop)) {
                print_error('error:configsetting', 'local_tlconnect', '', $prop);
            }
        }
    }
}