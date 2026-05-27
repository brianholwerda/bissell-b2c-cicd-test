<?php
namespace Custom\Widgets\reports;

class Multiline extends \RightNow\Widgets\Multiline {
    function __construct($attrs) {
        parent::__construct($attrs);
    }

    function getData() {        
        return parent::getData();
    }

    /**
     * Overridable methods from Multiline:
     */
    function showColumn($value, array $header){        
        if((!array_key_exists('visible', $header) || $header['visible'] === true)) {
            if($this->data['attrs']['hide_empty_columns'] && (is_null($value) || $value === '' || $value === false)) {
                return false;
            }
            if(is_array($this->data['js']['hide_columns']) && in_array($header['col_definition'], $this->data['js']['hide_columns'])) {
                return false;
            }
            return true;
        }
        return false;
    }
    // function getHeader(array $header)
}