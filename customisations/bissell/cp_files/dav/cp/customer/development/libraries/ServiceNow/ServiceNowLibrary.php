<?php

namespace Custom\Libraries\ServiceNow;

// TODO: Edit this use declaration as needed
require("custom/webservicehelper.php");
use RightNow\Connect\v1_3 as RNCPHP;

class CustomProcessClass {

    public static $runlocation; // 1 = tst1, 2 = tst2, 3 = prod
    public static $httpheader;
  
    public static function apply($runMode, $action, $incident, $cycle, $scriptloc, $save=false) {
		
		self::printcustom("Starting custom library...");
        self::$runlocation = $scriptloc;
		self::printcustom("Starting script with location " . self::$runlocation . " and incident " . $incident->ID);
		$time_start = microtime(true);
        
        
        if(self::$runlocation == 1) {
            $inc_report = 109405; // prod site

            // self::$httpheader = array(
                    // "Accept: application/json",
                    // "Content-Type: application/json",
                    // 'Authorization: Basic b3N2Y19jb25uZWN0b3I6VHJpYW5nbGUwNA=='
                    // // 'Cookie: JSESSIONID=1170EAD9F8F3EBD36546F477ECF38E9D; glide_user_route=glide.5996fd4a1ae3484e479cd544656bc16f; glide_session_store=20ACABD7DB64205029D6E439D39619D3; BIGipServerpool_bisselldev=512317194.39742.0000'
                // );
				
			self::$httpheader = array(
                    "Content-Type: application/json",
					'Authorization: Basic OTE5ODU3Nzc1Nzg1OmpnZDdmcHMwZDUvcHJvZA=='
                    // 'Cookie: JSESSIONID=1170EAD9F8F3EBD36546F477ECF38E9D; glide_user_route=glide.5996fd4a1ae3484e479cd544656bc16f; glide_session_store=20ACABD7DB64205029D6E439D39619D3; BIGipServerpool_bisselldev=512317194.39742.0000'
                );
				
				$noactionid = 95;
        } else {
             $inc_report = '';

            self::$servicenowauth = array();       
			$noactionid = 0;
        }    

        print("Starting Process.....");

        $rptdata = self::GetIncidentContactInfo($incident->ID, $inc_report);
		
		if(isset($rptdata['xxNotes'])) {
			
            $rptdata['xxNotes'] = strip_tags($rptdata['xxNotes']);

            if(empty($rptdata['xxNotes'])) {
                $rptdata['xxNotes'] = 'No note added';
            } else {
				// add the note to the thread and clear it out
				$incident->Threads = new RNCPHP\ThreadArray();
				$incident->Threads[0] = new RNCPHP\Thread();
				$incident->Threads[0]->EntryType = new RNCPHP\NamedIDOptList();
				$incident->Threads[0]->EntryType->ID = 1; // Private Note
				$incident->Threads[0]->Text = $rptdata['xxNotes'];
				
				$incident->CustomFields->ServiceNow->ServiceNow_notes = ' ';
			}
        }

		self::printcustom("Performing action " . $rptdata['xxSNOW Action']);
		
        if ($rptdata['xxSNOW Action'] == 'Create Ticket') {
            $returnedarray = self::CreateServiceNowIncident($rptdata); // note there is no error checking

			if(isset($returnedarray['response']['status_code'])){
				if($returnedarray['response']['status_code'] == "200"){
					//$incident->CustomFields->ServiceNow->snowincidentid = $returnedarray['sys_id'];
					$incident->CustomFields->ServiceNow->snowincidentid = $returnedarray['data']['workItemId'];
					//$incident->CustomFields->ServiceNow->snowincidentnumber = $returnedarray['number'];
					$incident->CustomFields->ServiceNow->snowincidentnumber = $returnedarray['data']['workItemId'];
					$incident->CustomFields->ServiceNow->snowintegrationstatus = "Success";
					self::printcustom("Setting ServiceNow action to ID $noactionid");
					$incident->CustomFields->c->servicenowaction->ID = $noactionid; // No Action - need to update
				}
				else{
					$incident->CustomFields->ServiceNow->snowincidentnumber = "Error";
					$incident->CustomFields->ServiceNow->snowintegrationstatus = "Error - Bad Status Code";
					$incident->CustomFields->c->servicenowaction->ID = $noactionid;
				}
			}
			else{
				$incident->CustomFields->ServiceNow->snowincidentnumber = "Error";
				$incident->CustomFields->ServiceNow->snowintegrationstatus = "Error - General Error";
				$incident->CustomFields->c->servicenowaction->ID = $noactionid;
			}

            if($save) {
                $incident->save();
            }
        } elseif ($rptdata['xxSNOW Action'] == 'Update Ticket') {
            $returnedarray = self::UpdateServiceNowIncident($rptdata); // note there is no error checking
			
			if(isset($returnedarray['response']['status_code'])){
				if($returnedarray['response']['status_code'] == "200"){
					$incident->CustomFields->ServiceNow->snowintegrationstatus = "Success";
					self::printcustom("Setting ServiceNow action to ID $noactionid");
					$incident->CustomFields->c->servicenowaction->ID = $noactionid; // No Action - need to update
				}
				else{
					$incident->CustomFields->ServiceNow->snowintegrationstatus = "Error - Bad Status Code";
					$incident->CustomFields->c->servicenowaction->ID = $noactionid;
				}
			}
			else{
				$incident->CustomFields->ServiceNow->snowintegrationstatus = "Error - General Error";
				$incident->CustomFields->c->servicenowaction->ID = $noactionid;
			}
			
            if($save) {
                $incident->save();
            }
        } elseif ($rptdata['xxSNOW Action'] == 'Close Ticket') {
            $returnedarray = self::CloseServiceNowIncident($rptdata); // note there is no error checking
			
			self::printcustom("Setting ServiceNow action to ID $noactionid");
        	$incident->CustomFields->c->servicenowaction->ID = $noactionid; // No Action - need to update
			
            if($save) {
                $incident->save();
            }
		}
    }

    /*     * ***************************************************************************
     * Do not change below
     * *************************************************************************** */
	public static function printcustom($txt) {
		print"<pre>$txt</pre><br />";
	}
	
	public static function getdescription($data) {
		
		ksort($data); // alphabetic by keys
		
		$description = "";
        foreach ($data as $name => $value) {
			if(substr($name,0,2) == 'xx') {
				self::printcustom("Not adding $name to description");
			} else {
				self::printcustom("Adding $name to description");
				$description .= "$name: $value\r\n";
			}
        }
		
		if(!empty($data['xxNotes'])) {
			$description .= 'Last Note: ' . $data['xxNotes'];
		}
		
		return $description;
	}
	
// Gets incident, contact, and org info in one call
    public static function GetIncidentContactInfo($incident_id, $report_id) {
        $status_filter = new RNCPHP\AnalyticsReportSearchFilter;
        $status_filter->Name = 'i_id';
        $status_filter->Values = array($incident_id);
        $filters = new RNCPHP\AnalyticsReportSearchFilterArray;
        $filters[] = $status_filter;
        $ar = RNCPHP\AnalyticsReport::fetch($report_id);
        $arr = $ar->run(0, $filters);
        $row = $arr->next();
        self::printcustom("Incident Data:\n" . print_r($row, true));
        return($row);
    }

    //
    // set_up_curl
    //
    public static function CreateServiceNowIncident($data) {

        $description = self::getdescription($data);

        echo "<pre>Sending description $description</pre>";

        $ref_no = $data['Reference Number'];
        $date = $data['Date Created'];

        // Oracle Service Cloud Incident ID
        // put report contents into u_product
        $jsonarray = array(
            "active" => "true",
            "u_osvc_incident" => $data['Oracle Service Cloud Incident ID'],
            "u_product" => $data['Product'],
            "caller_id" => "user sys_id",
            "opened_by" => "user sys_id",
            "category" => "IoT",
            "subcategory" => "Escalated Consumer Call",
            "u_item" => "Mobile",
            "contact_type" => "self-service",
            "state" => "9",
            "impact" => "3",
            "urgency" => "3",
            "location" => "385c9a2edbbcec50ca3018df4b9619d3",
            "assignment_group" => "0a2b1b631b73af042c654261cd4bcb81",
            "u_cause" => "from OSvC",
            "short_description" => "Consumer Care Call Ticket: $ref_no Date: $date",
			"work_notes" => $data['xxNotes'],
            "description" => $description
        ); // Note priority should be set by impact and urgency automatically so not in this set

        $rawjson = json_encode($jsonarray, JSON_PRETTY_PRINT);
        print("<p>Starting to send incident to ServiceNow</p><pre>$rawjson</pre>");
        load_curl();
        $curl = curl_init();

        // curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://bisselldev.service-now.com/api/now/table/incident',
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => "",
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 0,
            // CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYHOST => false,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => "POST",
            // CURLOPT_POSTFIELDS => $rawjson,
            // CURLOPT_HTTPHEADER => self::$httpheader,
        // ));
		
		curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://jgd7fps0d5.execute-api.us-east-1.amazonaws.com/prod/osvc/incident',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $rawjson,
            CURLOPT_HTTPHEADER => self::$httpheader,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo '<pre>' . $response . '</pre>';
        $responseobject = json_decode($response, TRUE);
		
		$info = array(
			'ExtSystem'	=> 'ServiceNowIntegration',
			'RequestMsg'	=> json_encode($jsonarray),
			'ResponseMsg'	=> $response,
			'Description'	=> 'ServiceNow Incident Create',
			'IncidentID'	=> intval($data['Oracle Service Cloud Incident ID'])
		);
		logSysIntegration($info);
		
        //return($responseobject['result']);
		return($responseobject);
    }
    
    public static function UpdateServiceNowIncident($data) {

        $description = self::getdescription($data);

        echo "<pre>Sending description $description</pre>";

        $ref_no = $data['Reference Number'];
        $date = $data['Date Created'];

        // Oracle Service Cloud Incident ID
        // put report contents into u_product
        $jsonarray = array(
            "u_osvc_incident" => $data['Oracle Service Cloud Incident ID'],
			"u_cause" => "from OSvC",
			"work_notes" => $data['xxNotes'],
			"description" => $description
        ); // Note priority should be set by impact and urgency automatically so not in this set

        $rawjson = json_encode($jsonarray, JSON_PRETTY_PRINT);
        print("<p>Starting to send incident to ServiceNow</p><pre>$rawjson</pre>");
        load_curl();
        $curl = curl_init();
        
        echo "<p>Sending to " . 'https://bisselldev.service-now.com/api/now/table/incident/' . $data['xxServiceNowIncidentID'] . "</p>";

        // curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://bisselldev.service-now.com/api/now/table/incident/' . $data['xxServiceNowIncidentID'],
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => "",
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 0,
            // CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYHOST => false,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => "PATCH",
            // CURLOPT_POSTFIELDS => $rawjson,
            // CURLOPT_HTTPHEADER => self::$httpheader,
        // ));
		
		curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://jgd7fps0d5.execute-api.us-east-1.amazonaws.com/prod/osvc/incident',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => $rawjson,
            CURLOPT_HTTPHEADER => self::$httpheader,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo '<pre>' . $response . '</pre>';
        $responseobject = json_decode($response, TRUE);
		
		$info = array(
			'ExtSystem'	=> 'ServiceNowIntegration',
			'RequestMsg'	=> json_encode($jsonarray),
			'ResponseMsg'	=> $response,
			'Description'	=> 'ServiceNow Incident Update',
			'IncidentID'	=> intval($data['Oracle Service Cloud Incident ID'])
		);
		logSysIntegration($info);
		
        //return($responseobject['result']);
		return($responseobject);
    }
    
    public static function CloseServiceNowIncident($data) {


        // Oracle Service Cloud Incident ID
        // put report contents into u_product
        $jsonarray = array(
            "close_code"=>"Solved (Permanently)",
            "close_notes"=>"Closed in OSvC",
            "resolved by"=>"646b27fbdb58e890ca3018df4b96191b",
            "state" => "2"
        ); 

        $rawjson = json_encode($jsonarray, JSON_PRETTY_PRINT);
        print("<p>Starting to send incident to ServiceNow</p><pre>$rawjson</pre>");
        load_curl();
        $curl = curl_init();
        
        echo "<p>Sending to " . 'https://bisselldev.service-now.com/api/now/table/incident/' . $data['xxServiceNowIncidentID'] . "</p>";

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bisselldev.service-now.com/api/now/table/incident/' . $data['xxServiceNowIncidentID'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => $rawjson,
            CURLOPT_HTTPHEADER => self::$httpheader,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo '<pre>' . $response . '</pre>';
        $responseobject = json_decode($response, TRUE);
		
		$info = array(
			'ExtSystem'	=> 'ServiceNowIntegration',
			'RequestMsg'	=> json_encode($jsonarray),
			'ResponseMsg'	=> $response,
			'Description'	=> 'ServiceNow Incident Close',
			'IncidentID'	=> intval($data['Oracle Service Cloud Incident ID'])
		);
		logSysIntegration($info);
		
        return($responseobject['result']);
    }

}
