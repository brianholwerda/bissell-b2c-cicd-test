<?php
namespace Custom\Widgets\input;
use RightNow\Utils\Url;

class CustomAllInputDynamic extends \RightNow\Widgets\CustomAllInput {
    function __construct($attrs) {
        parent::__construct($attrs);
    }
    function getData() {
        $mapping = array('contacts' => 'Contact', 'incidents' => 'Incident', 'answers' => 'Answer');
        if ($mappedTableName = $mapping[$this->data['attrs']['table']]) {
            $this->data['attrs']['table'] = $mappedTableName;
        }

        switch($this->data['attrs']['table'])
        {
            case 'Contact':
                if($this->data['attrs']['chat_visible_only'])
                    $visibility = VIS_LIVE_CHAT;
                else
                    $visibility = VIS_ENDUSER_EDIT_RW|VIS_ENDUSER_EDIT_RO;
                $table = TBL_CONTACTS;
                break;
            case 'Incident':
                if($this->data['attrs']['chat_visible_only'])
                    $visibility = VIS_LIVE_CHAT;
                else
                    $visibility = VIS_ENDUSER_DISPLAY;
                $table = TBL_INCIDENTS;
                break;
            case 'Answer':
                $visibility = VIS_ENDUSER_DISPLAY;
                $table = TBL_ANSWERS;
                break;
            default:
                echo $this->reportError(sprintf(\RightNow\Utils\Config::getMessage(PCT_S_ATTRIBUTE_IS_REQUIRED_MSG), 'table'));
                return false;
        }
        $visibility = ($visibility !== VIS_LIVE_CHAT && $this->widgetType === 'input') ? VIS_ENDUSER_EDIT_RW : $visibility;
        $fields = \RightNow\Utils\Framework::getCustomFieldList($table, $visibility);
        $this->data['fields'] = $this->data['ids'] =  $this->data['config'] = $this->data['js']['fieldMeta'] = array();
        foreach($fields as $field){
            $customFieldName = \RightNow\Utils\Text::getSubstringAfter($field['col_name'], 'c$');
			$this->data['fields'][] = $this->data['attrs']['table'] . ".CustomFields.c.$customFieldName";
			$this->data['js']['fieldMeta'][$field["cf_id"]] = $this->data['attrs']['table'] . ".CustomFields.c.$customFieldName";
        }

		 
		  $this->data['js']['fieldConfig'] =  $this->CI->model('custom/dynamicform_model')->returnDynamicConfig($this->data['attrs']['data_type'])->result;
	 
 
	 	$prodURL = Url::getParameter('p');
		$catURL = Url::getParameter('c');
		$this->data['js']['hierChain'] = '';
		if($this->data['attrs']['data_type'] == 'Product' && !empty($prodURL))
			$this->data['js']['hierChain'] = $prodURL;
		elseif(!empty($catURL))
			$this->data['js']['hierChain'] = $prodURL;

    }
}