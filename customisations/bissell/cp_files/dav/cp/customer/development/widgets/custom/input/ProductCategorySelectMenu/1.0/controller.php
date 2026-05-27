<?php
namespace Custom\Widgets\input;
use RightNow\Connect\v1_3 as RNCPHP;

class ProductCategorySelectMenu extends \RightNow\Libraries\Widget\Base { 
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {
        $this->data['attrs']['display_ids'] = array();
        $this->data['attrs']['filter_ids'] = false;
        $this->data['js']['name'] = $this->data['attrs']['table'].'.'.$this->data['attrs']['name'];

        // Explode an array from the comma separated string, and make sure it's not null
        (strlen($this->data['attrs']['show_ids']) > 0 ? ($this->data['attrs']['display_ids'] = explode(',', $this->data['attrs']['show_ids']) AND $this->data['attrs']['filter_ids'] = true) : $this->data['attrs']['show_ids'] = array('empty'));
        
        // Concatenate the id_prefix to uniquely identify this widget
        $this->data['attrs']['id_prefix'] = $this->data['attrs']['prefix'].'_'.$this->data['attrs']['name'].'Menu_';
        $this->CI->load->model($this->data['attrs']['ajax_model']);

        $topLevelIDs = $this->data['attrs']['top_level_ids'];
        $startingLevelID = $this->data['attrs']['starting_level_id'];

        if(strlen($this->data['attrs']['default_label']) > 0)
            $this->data['attrs']['final_selection_text'] = str_replace('<name>', $this->data['attrs']['default_label'], $this->data['attrs']['final_selection_label']);
        else
            $this->data['attrs']['final_selection_text'] = str_replace('<name>', $this->data['attrs']['name'], $this->data['attrs']['final_selection_label']);

        if($this->data['attrs']['required'])
            $this->data['attrs']['required'] = "required";
		
        // Check if an incident ID is provided, or if the incident_product_preset chain was defined and if so, get the specified product or category value
		$this->data['attrs']['incident_id'] = (int) \RightNow\Utils\Url::getParameter('i_id');
		if(($this->data['attrs']['incident_id'] > 0) || ($this->data['attrs']['incident_product_preset'] > 1)) {
			if($this->data['attrs']['incident_id'] > 0) {
			  	$incident = RNCPHP\Incident::fetch($this->data['attrs']['incident_id']);
			  	$incidentProduct = $incident->{$this->data['attrs']['name']}->ID;
			} else {
				$incidentProduct = $this->data['attrs']['incident_product_preset'];
			}
            // Get the heirarchy chain to the parent of the specified product or category
		  	$this->data['attrs']['incident_product_preset_array'] = $this->CI->model($this->data['attrs']['ajax_model'])->getFormattedChain($this->data['attrs']['name'], $incidentProduct, true)->result;
		}
		
        // If starting level ID was defined, get the direct descendants (since it's not a top level ID)
        if($startingLevelID > 0) {
            $this->data['products'] = $this->CI->model($this->data['attrs']['ajax_model'])->getDirectDescendants($this->data['attrs']['name'],$startingLevelID)->result;
        // Otherwise get the top level products or categories with the getHierarchy method
        } else {
        	$this->data['products'] = $this->CI->model($this->data['attrs']['ajax_model'])->getHierarchy(
                $this->data['attrs']['name'],
                1,
                $this->data['attrs']['maximum_top_levels'],
                $topLevelIDs ? explode(',', $topLevelIDs) : array(),
                30
            )->result;
        }
        
        if(!count($this->data['products'])) {
            return false;
        }

        if(strlen($this->data['attrs']['placeholder']) > 0)
            $this->data['attrs']['placeholderText'] = $this->data['attrs']['placeholder'];
        else
            $this->data['attrs']['placeholderText'] = 'Select a '.$this->data['attrs']['name'];

        // Calculate the width of each menu if they are being floated vertically
        $selectWidth = 100 / $this->data['attrs']['hierarchy_depth'] - 1;
        $selectWidth .= '%';

        // Build the first menu with the top level options
        $this->data['prodcat_menu1'] = '<select name="'.$this->data['attrs']['id_prefix'].'1" id="'.$this->data['attrs']['id_prefix'].'1" data-placeholder="'.$this->data['attrs']['placeholderText'].'..." class="chosen-product '.$this->data['attrs']['id_prefix'].'" style="width:'.$selectWidth.'" validation_label="'.$this->data['attrs']['validation_label'].' selection">';
        $this->data['prodcat_menu1'] .= '<option value=""></option>';

        $r = 0;
        $this->data['availTopLevelOptions'] = false;
        foreach ($this->data['products'] as $prodcats => $prodcat):
            if(!$this->data['attrs']['filter_ids'] || (!empty($this->data['attrs']['display_ids'] && in_array($prodcat['id'], $this->data['attrs']['display_ids'])))) {
                $this->data['prodcat_menu1'] .= '<option value="'.$prodcat['id'].'">'.$prodcat['label'].'</option>';
                $this->data['availTopLevelOptions'] = true;
            }
        $r++;
        endforeach;

        $this->data['prodcat_menu1'] .= '</select>';

        //Build subsequent menus - Level *
        foreach (range(2, $this->data['attrs']['hierarchy_depth']) as $childMenu):
            $this->data['prodcat_menus'][$childMenu] = '<select name="'.$this->data['attrs']['id_prefix'].$childMenu.'" id="'.$this->data['attrs']['id_prefix'].$childMenu.'" style="width:'.$selectWidth.'" data-placeholder="'.$this->data['attrs']['placeholderText'].'..." class="chosen-product '.$this->data['attrs']['id_prefix'].'" validation_label="'.$this->data['attrs']['validation_label'].' selection" disabled>';
            $this->data['prodcat_menus'][$childMenu] .= '<option value=""></option></select>';
        endforeach;
    }
}
