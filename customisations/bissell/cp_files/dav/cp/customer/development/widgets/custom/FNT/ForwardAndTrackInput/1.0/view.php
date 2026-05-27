<?php
	//define commonly used variables.
	$incident = $this->data['js']['data']['Incident']; // the Incident
	$threadIndex = $this->data['js']['data']['ThreadIndex']; // the Thread created for this FNT
	$filesChecked = $this->data['js']['data']['FilesChecked']; // array of files that were checked in the add in.
	$fileAttachments = $this->data['js']['data']['FileAttachments']; // boolean to determine that file attachments can be viewed.
	$startFNTText = $this->data['js']['data']['StartFNTText'];
	$categoryHeader = $this->data['js']['data']['CategoryHeader'];
	$categoryFields = $this->data['js']['data']['CategoryFields'];
	$queueHeader = $this->data['js']['data']['QueueHeader'];
	$queueFields = $this->data['js']['data']['QueueFields'];
?>
<div id="rn_<?=$this->instanceID;?>_ErrorLocation" class="rn_Hidden"></div>
<div class="rn_ForwardAndTrackInput">
	<div id="rn_<?=$this->instanceID;?>" class="rn_ForwardAndTrackInput2">
		<div class="rn_FNT2">
			
			<div class="rn_FNT2Heading">
				<p class="rn_FNT2Assist">#rn:msg:CUSTOM_MSG_FNT_ASSISTANCE_REQUESTED#</p>
				<div style="clear: both;"></div>
			</div>
			<div class="rn_FNT2HeaderFields">
				<b>INCIDENT DETAILS</b><br>
				<b>Reference #: </b><?=$incident->ReferenceNumber?><br>
				<b>Date Created: </b><?=gmdate("Y-m-d\TH:i:s\Z", $incident->CreatedTime)?><br>
				
				<br><b>CONTACT DETAILS</b><br>
				<b>Customer Name: </b><?=$incident->PrimaryContact->Name->First . " " . $incident->PrimaryContact->Name->Last?><br>
				<b>Customer Phone #: </b><?=$incident->PrimaryContact->Phones[0]->Number?><br>
				
				<?if(isset($categoryFields)):?>
					<br><b><?=$categoryHeader?></b><br>
					<?=$categoryFields?>
				<?endif;?>
				
				<?if(isset($queueFields)):?>
					<br><b><?=$queueHeader?></b><br>
					<?=$queueFields?>
				<?endif;?>
			</div>
			<br>
			<div class="rn_FNT2Module1">
				<?if (isset( $incident->Threads ) ):?>
					<?foreach ($incident->Threads as $t):?>
						<?if ( $t->DisplayOrder == $threadIndex  && strpos ($t->Text, $startFNTText) === false ):?>
				<div class="rn_FNT2From"><b>From:</b>
				&nbsp;<?=$t->Account->LookupName . " ". ($t->Account->Emails[0]->Address ? "(".$t->Account->Emails[0]->Address. ")" : '') . " at " . date('m-d-Y H:i:s', $t->CreatedTime)?>
				<?endif;?>
					<?endforeach;?>
					<?endif;?>
				</div>
				<div class="rn_FNT2Subject"><b>Subject:</b>&nbsp;<?=$incident->Subject?></div>
				<div class="rn_FNT2Message">
					<?if (isset( $incident->Threads )):?>
					<?foreach ($incident->Threads as $t):?>
						<?if ( $t->DisplayOrder == $threadIndex  && strpos ($t->Text, $startFNTText) === false ): ?>
							<?if ($t->ContentType->LookupName == "text/plain"):?>
								<?= str_replace( array("\n", "\t"), array("<br/>", "&nbsp;&nbsp;&nbsp;&nbsp;"), $t->Text)?>
							<?else:?>
								<?=$t->Text?>
							<?endif;?>
						<?endif;?>
					<?endforeach;?>
					<?endif;?>
				</div>
			</div>
		</div>
						<div id="rn_<?=$this->instanceID;?>_Loading" class="rn_FNT2Loading"><? /* =\RightNow\Utils\Config::getMessage(LOADING_LBL) */ ?>Loading...</div>

		<!-- results Area -->
		<div id="rn_<?=$this->instanceID;?>_Success" class="rn_Hidden rn_ForwardAndTrackThanks" >
			<?=\RightNow\Utils\Config::getMessage(CUSTOM_MSG_FNT_THANKS_TEXT)?>
		</div>
		<!-- The main form... -->
		<div id="rn_<?=$this->instanceID;?>_Container" class="rn_ForwardAndTrackInput rn_Hidden">
			<div class="rn_ForwardAndTrackForm">
			<form id="rn_<?=$this->instanceID?>_Form" name="rn_<?=$this->instanceID?>_Form" action="javascript:void(0);">
				<b><? /* =\RightNow\Utils\Config::getMessage(FROM_LBL) */ ?>Respond From
				<span class="rn_Required"> <?=\RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL);?> </span><span class="rn_ScreenReaderOnly"><?=\RightNow\Utils\Config::getMessage(REQUIRED_LBL)?></span>
				</b>
				<br>
				<input id="rn_<?=$this->instanceID;?>_From" style="width:99%;height:25px;font-size: 15px;" class="rn_ForwardAndTrackField" type="text" size="50" value="<?=$this->data['js']['data']['ResponseFrom']?>" />
				<br/><br>
				<b>Private <?=\RightNow\Utils\Config::getMessage(RESPONSE_LBL)?> 
				<span class="rn_Required"> <?=\RightNow\Utils\Config::getMessage(FIELD_REQUIRED_MARK_LBL);?> </span><span class="rn_ScreenReaderOnly"><?=\RightNow\Utils\Config::getMessage(REQUIRED_LBL)?></span>
				</b>
				<br/>
				<textarea style='width:99%;height:180px;font-size: 15px;' id="rn_<?=$this->instanceID;?>_Message"></textarea>
				<br/>
				<!--
				<div class="rn_Hidden">
				<b>Public <?=\RightNow\Utils\Config::getMessage(RESPONSE_LBL)?> 
				</b>
				<br/>
				<textarea style='width:450px;height:150px;' id="rn_<?=$this->instanceID;?>_MessagePublic"></textarea>					
				<br/>
				</div>
				-->
			</form>
			<br/>
			<form id="rn_<?=$this->instanceID?>_AttachForm" action="javascript:void(0);">
			<div class="rn_Label" style="width: 100%;"><b><?=\RightNow\Utils\Config::getMessage(ATTACHMENTS_LBL)?></b></div>
			<div>
				<!-- Viewable files -->
				<?if ($fileAttachments) :?>
					<div>
					<?if (isset( $incident->FileAttachments ) ):?>
					<?foreach($incident->FileAttachments as $fa):?>
						<?if (in_array ( $fa->FileName, $filesChecked )):?>
						<br/><a href="<?=$fa->getAdminURL ()?>" target="_blank"><?=$fa->FileName?></a><br/>
						<?endif;?>
					<?endforeach;?>
					<?endif;?>
					</div>
				<?endif;?>				
				<br/>
				<!-- Files added from the widget-->
				<div id="rn_<?=$this->instanceID;?>_Files"></div>
				<input name="file" style="width: 300px;border:none;" class="rn_ForwardAndTrackField" id="rn_<?=$this->instanceID;?>_FileInput" type="file" />
			</div>
			</form>
				<br/><br/>

			<form id="rn_<?=$this->instanceID?>_SaveForm" action="javascript:void(0);">
			<input id="rn_<?=$this->instanceID;?>_SaveLink" type="submit" value="<?=\RightNow\Utils\Config::getMessage(CUSTOM_MSG_FNT_RESPOND_BTN_LBL)?>" />
			</form>
			</div>
		</div>
	</div>
	
	<?if (isset( $incident->Threads )):?>
		<ul class="rn_FNTlegend">
			<li>Legend:</li>
			<li class="rn_FNTprivateNote">Private Note</li>
			<li class="rn_FNTcustomer">Customer</li>
			<li class="rn_FNTagentResponse">Agent</li>
		</ul>

		<div id="rn_<?=$this->instanceID?>_Threads" class="rn_ForwardAndTrackInput rn_ThreadHeader">
		<?foreach ($incident->Threads as $t):?>
		<?php
			//print $t->EntryType->LookupName;
			$c = "rn_ForwardAndTrackInputAgentResponse";
			if ( strpos ($t->Text, $startFNTText) !== false ||
				 strpos ($t->Text, "* This forward and track message is from") !== false || strpos ($t->EntryType->LookupName, "Note") !== false) 
			{
				$c = "rn_ForwardAndTrackInputPrivateNote";
			} else {
				if ( strpos( $t->EntryType->LookupName, "Customer") !== false )
				{
					$c = "rn_ForwardAndTrackInputCustomer";
				}
			}

		?>	
			
				<div class="rn_FNT2Modules">
					<div class="<?=$c?>"><b>From:</b><? /* =\RightNow\Utils\Config::getMessage(PRIMARY_CONTACT_LBL) */ ?>&nbsp;
						<?=$t->Account->LookupName . " ". ($t->Account->Emails[0]->Address ? "(".$t->Account->Emails[0]->Address. ")" : '') . " at " . date('m-d-Y H:i:s', $t->CreatedTime)?>
					<? /* <p class="rn_FNT2RefNum">
						<span><?=\RightNow\Utils\Config::getMessage(REFERENCE_NUM_LBL)?></span>&nbsp;<?=$incident->LookupName?>
					</p> */ ?>
					</div>
					<div class="rn_FNT2Subject"><b>Subject:</b><? /* =\RightNow\Utils\Config::getMessage(SUBJECT_LBL) */ ?>&nbsp;<?=$incident->Subject?></div>
					<div class="rn_FNT2Message">
						<?if ($t->ContentType->LookupName == "text/plain"):?>
							<?= str_replace( array("\n", "\t"), array("<br/>", "&nbsp;&nbsp;&nbsp;&nbsp;"), $t->Text)?>
						<?else:?>
							<?= $t->Text ?>
						<?endif;?>
					</div>
				</div>
		<?endforeach;?>
	</div>
	<?endif;?>
</div>