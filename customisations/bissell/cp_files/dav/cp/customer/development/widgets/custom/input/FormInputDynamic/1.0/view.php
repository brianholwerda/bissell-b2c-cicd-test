<? /* Overriding FormInput's view */ ?>
<rn:block id="top"/>
<? 
	if($this->fieldName == 'Incident.CustomFields.c.preferred_solution'): ?>
		<rn:widget path="input/SelectionInputDynamic" display_as_checkbox="true" />
<?	else :
	
	switch ($this->dataType):
    case 'Boolean':
    case 'Country':
    case 'NamedIDLabel':
    case 'NamedIDOptList':
    case 'AssignedSLAInstance':?>
        <rn:widget path="input/SelectionInputDynamic"/>
        <? break;
    case 'Date':
    case 'DateTime': 
    	if($this->data['inputName']=='Incident.CustomFields.c.purchase_date') {
			$dateLabel =  \RightNow\Utils\Config::getMessage(CUSTOM_MSG_PURCH_TRANS_DATE_LBL); //'Purchase Transaction Date (MM-DD-YYYY)';
		} else if ($this->data['inputName']=='Incident.CustomFields.c.store_visit_date') {
			$dateLabel = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_STORE_VISIT_DATE_LBL); //'Store Visit Date (MM-DD-YYYY)';
		} else if ($this->data['inputName']=='Incident.CustomFields.c.return_date') {
			$dateLabel = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_RETURN_TRANS_DATE_LBL); //'Return Transaction Date (MM-DD-YYYY)';
		} else {
			$dateLabel = \RightNow\Utils\Config::getMessage(CUSTOM_MSG_DATE_DEFAULT_LBL); //"Enter a Date (MM-DD-YYYY)";
		}   
    ?>
         <rn:widget path="custom/input/DateTimeUI" 
                widget_label_input="DateTimeUI Widget"
                display_date="true"
                date_name="#rn:php:$this->data['inputName']#"
                date_label_input=""
                date_label_required_input="Select a date"
                date_required="false"
                show_calendar="false"
                date_input_placeholder="#rn:php:$dateLabel#"
                time_input_placeholder="Select a Time"
                display_time="false"
                time_name=""
                time_label_input="Time Selection"
                time_label_required_input="Select a time"
                time_required="false"
            />  
       <? /*  <rn:widget path="input/DateInputDynamic"/> */ ?>
        <? break;
    default: ?>
        <? if ($this->fieldName === 'NewPassword'): ?>
        <rn:widget path="input/PasswordInput"/>
        <? else: ?>
        <rn:widget path="input/TextInputDynamic"/>
        <? endif; ?>
        <? break;
endswitch;
endif; ?>
<rn:block id="bottom"/>
