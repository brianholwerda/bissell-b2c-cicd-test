<?php

namespace Custom\Controllers;

use RightNow\Utils\Framework,
    RightNow\Libraries\AbuseDetection,
    RightNow\Utils\Config,
    RightNow\Utils\Connect as ConnectUtil,
    RightNow\Utils\Connect,
    RightNow\Utils\Okcs,
    RightNow\Utils\Url,
    RightNow\Utils\Text,
    RightNow\Internal\Utils\Version,
    RightNow\Api,
    RightNow\Connect\v1_3 as RNCPHP;

class AjaxCustom extends \RightNow\Controllers\Base
{
    //This is the constructor for the custom controller. Do not modify anything within
    //this function.
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Sample function for ajaxCustom controller. This function can be called by sending
     * a request to /ci/ajaxCustom/ajaxFunctionHandler.
     */
    function ajaxFunctionHandler()
    {
        $postData = $this->input->post('post_data_name');
        //Perform logic on post data here
        echo $returnedInformation;
    }

    /**
     * Sample search function
     */
    function search () {
        $filters = json_decode($this->input->post('filters'), true);
        $filters['limit'] = array('value' => $this->input->request('limit'));
        $sourceID = $this->input->post('sourceID');

        $search = \RightNow\Libraries\Search::getInstance($sourceID);
        $search->addFilters($filters);

        echo json_encode($search->executeSearch());
    }
    
        function sendFormWDMR()
    {		
        AbuseDetection::check($this->input->post('f_tok'));
        $data = json_decode($this->input->post('form'));
        //die(print_r($data,true));
        foreach($data as $dataElement) {
			//Find the line item that matches the SKU passed in, set useThisLine to that line item
			if($dataElement->name == 'Contact.CustomFields.c.consumer_id') {
				$consumerID = $dataElement->value;
			}
			if($dataElement->name == 'Contact.Emails.PRIMARY.Address') {
				$theEmail = $dataElement->value;
			}
			
		}

	    $Contact = RNCPHP\Contact::first("Contact.CustomFields.c.consumer_id = " . $consumerID );
        $c_id = $Contact->ID;
        
        if(!isset($Contact->Emails[0]->Address))
		{
			//Check to see if the email provided already exists on a contact - error out
			$ExistingContact = RNCPHP\Contact::first("Emails.Address = '" . $theEmail . "'" );
			$c_id = $ExistingContact->ID;
			
			if(isset($c_id)) {
				//SET AN ERROR HERE AND DON'T ALLOW THE PAGE TO SUBMIT
				$errorObject = array (
					"suggestedErrorMessage" => "WRONG!",
					"error" => true,
					"errors" => array (
						"externalMessage" => Config::getMessage(CUSTOM_MSG_DUPLICATE_EMAIL_CONTACT)
					)
				);
				
				//LogMessage(print_r($errorObject, true));
				echo(json_encode($errorObject));
				return;
				
			} else {
				//UPDATE THE CONTACT WITHOUT AN EMAIL TO THE EMAIL THE USER ENTERED
				$Contact->Emails = new RNCPHP\EmailArray();
				$Contact->Emails[0] = new RNCPHP\Email();
				$Contact->Emails[0]->AddressType=new RNCPHP\NamedIDOptList();
				$Contact->Emails[0]->AddressType->LookupName = "Email - Primary";
				$Contact->Emails[0]->Address = $theEmail;
				$Contact->save(RNCPHP\RNObject::SuppressAll);
			}
		}
		
        if(!$data)
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            // Pad the error message with spaces so IE will actually display it instead of a misleading, but pretty, error message.
            Framework::writeContentWithLengthAndExit(json_encode(Config::getMessage(END_REQS_BODY_REQUESTS_FORMATTED_MSG)) . str_repeat("\n", 512), 'application/json');
        }
        if($listOfUpdateRecordIDs = json_decode($this->input->post('updateIDs'), true)){
            $listOfUpdateRecordIDs = array_filter($listOfUpdateRecordIDs);
        }
        $smartAssistant = $this->input->post('smrt_asst');
        if($flashMessage = $this->input->post('flash_message')){
            $this->session->setFlashData('info', $flashMessage);
        }
        $result = $this->model('Field')->sendForm($data, $listOfUpdateRecordIDs ?: array(), ($smartAssistant === 'true'))->toJson();
        //$this->_echoJSON($this->model('Field')->sendForm($data, $listOfUpdateRecordIDs ?: array(), ($smartAssistant === 'true'))->toJson());
        die(print_r($result,true));
    } 
	function searchRecall(){
		$isValidModel = false;
		$hasRecall = false;
		$recallID = 0;
		$modelNumber = isset($_REQUEST['modelNumber']) ? trim($_REQUEST['modelNumber']) : null;
		
		$query="select * from ServiceProduct where LookupName like '" . $modelNumber . " %'"; 
		$getProduct = RNCPHP\ROQL::query($query)->next()->next();
		
		if($getProduct != null){
			$isValidModel = true;
			$recallquery="select * from BI.ProductRecallConfig where IsActive = 1 and Country = 1 and Product = " . $getProduct['ID'];
			$getRecall = RNCPHP\ROQL::query($recallquery)->next()->next();
			
			if($getRecall != null){
				$hasRecall = true;
				$recallID = $getRecall['ProductRecall'];
			}
		}
		
		$returnObject = array (
			"IsValidModel" => $isValidModel,
			"ModelHasRecall" => $hasRecall,
			"RecallID" => $recallID
		);
		
		echo(json_encode($returnObject));
		return;
	}
}

