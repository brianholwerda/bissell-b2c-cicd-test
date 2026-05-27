<?php
namespace Custom\Widgets\opa;

class OPAWidgetCP extends \RightNow\Libraries\Widget\Base
{
    function __construct($attrs)
    {
        parent::__construct($attrs);
    }

    function getData()
    {
        $this->CI->load->helper('opa');

        $this->data['url'] = getOPAURL($this->CI->session->getProfile(), $this->data['attrs']['web_determinations_url'], $this->data['attrs']['policy_model'], $this->data['attrs']['locale'], $this->data['attrs']['init_id']);
    }
}