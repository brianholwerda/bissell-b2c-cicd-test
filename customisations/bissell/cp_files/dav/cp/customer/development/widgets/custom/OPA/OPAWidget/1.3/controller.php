<?php
namespace Custom\Widgets\opa;
use RightNow\Utils\Config;

class OPAWidget extends \RightNow\Libraries\Widget\Base {

    function __construct($attrs) {
        parent::__construct($attrs);
        $this->CI->load->helper('opa_v3');
    }

    function getData() {
        if (OPA_APP_BASE_URL === "OPA_APP_BASE_URL") {
            $this->data['error_text'] = "Configuration parameter OPA_APP_BASE_URL cannot be found.";

        } elseif (OPA_APP_SECRET === "OPA_APP_SECRET") {
            $this->data['error_text'] = "Configuration parameter OPA_APP_SECRET cannot be found.";

        } else {
            $baseUrl = Config::getConfig(OPA_APP_BASE_URL);
            $secret = Config::getConfig(OPA_APP_SECRET);

            if ($baseUrl == '') {
                $this->data['error_text'] = "Configuration parameter OPA_APP_"."BASE_URL is not set.";

            } elseif ($secret == '') {
                $this->data['error_text'] = "Configuration parameter OPA_APP_"."SECRET is not set.";

            } else {
                $this->data['userToken'] = generateUserToken($this->CI->session->getProfile(), $secret);
                $this->data['opaWdUrl'] = getWebDeterminationsUrl($baseUrl);
                if ($this->data['attrs']['seed_data'] != '') {
                    $this->data['seedData'] = trim($this->data['attrs']['seed_data'], " ");
                    if (substr($this->data['seedData'], 0, 1) !== "{") {
                        $this->data['seedData'] = "{".$this->data['seedData']."}";
                    }
                }
                    

                if ($this->data['attrs']['disable_checkpoint_resuming']) {
                    $this->data['checkpointAction'] = "ignore";
                } else if ($this->data['attrs']['resume_without_prompt']) {
                    $this->data['checkpointAction'] = "resume";
                } else {
                    $this->data['checkpointAction'] = "check";
                }
            }
        }
    }
}