RightNow.namespace('Custom.Widgets.FNT.ForwardAndTrackInput');
Custom.Widgets.FNT.ForwardAndTrackInput = RightNow.Widgets.extend({ 
		/**
		 * Widget constructor.
		 */
	constructor: function() {
		this._requestInProgress = false;
		this._formErrorLocation = document.getElementById("rn_" + this.instanceID + '_ErrorLocation');
		this._threads = document.getElementById("rn_" + this.instanceID + '_Threads'); // list of incident threads at bottom of view
		this._challengeDivID = this.data.attrs.challenge_location;
		this._parentForm = "rn_" + this.instanceID + "_Form"; // main input form
		this._container = document.getElementById("rn_" + this.instanceID + '_Container');
		this._fnttok = this.data.js["FNT.Config.ResponseFrom"];
		this._k = this.data.js["k"];
		this._i = this.data.js["i"];
		this._message = document.getElementById("rn_" + this.instanceID + '_Message');
		this._messagePublic = document.getElementById("rn_" + this.instanceID + '_MessagePublic');
		this._from = document.getElementById("rn_" + this.instanceID + '_From');
			
		this._successMessage = document.getElementById("rn_" + this.instanceID + '_Success');
		this._loading = document.getElementById("rn_" + this.instanceID + '_Loading');
		this.Y.DOM.addClass(this._loading, "rn_Hidden"); // getData is done...
		this._saveLink = document.getElementById("rn_" + this.instanceID + '_SaveLink');
		this._attachments = [];
		// file upload stuff
		this._max_attachments = this.data.attrs.max_attachments || 3;
		this._max_attachments_msg = this.data.attrs.label_max_attachment_limit || "Attachments limited to " + this._max_attachments;
		this._files = document.getElementById("rn_" + this.instanceID + "_Files"); // element to render files attached from this form.
		this._inputField = document.getElementById("rn_" + this.instanceID + "_FileInput"); // file attachment field.
		this.Y.one(this._inputField).on("change", this._onFileAdded, this);
		this.Y.one(this._inputField).on( "keypress", this._onKeyPress, this);
		//pasting === bad
		this.Y.one(this._inputField).on("paste", function () {
			return false;
		});
		this.Y.one(this._saveLink).on("click", this._onButtonClick, this);

		// check to see if any errors occurred in the controller
		if (this.data.js.error) {
			// hide the main display
			this.Y.DOM.addClass(this._container, "rn_Hidden");
			// show the error
			this._formErrorLocation.innerHTML = this.data.js.error;
			this.Y.DOM.removeClass(this._formErrorLocation, "rn_Hidden");
			this.Y.DOM.addClass(this._formErrorLocation, "rn_ErrorMessage");
		} else {
			this.Y.DOM.removeClass("#" + this.instanceID, "rn_Hidden");
			this.Y.DOM.removeClass(this._threads, "rn_Hidden");
			if(this.data.attrs.read_only == false)
			{
				this.Y.DOM.removeClass(this._container, "rn_Hidden");
				this._enableClickListener();
			}
			else
				this.Y.DOM.addClass(this._container, "rn_Hidden");
		}
    },
	
	_onButtonClick : function (type, args) {

		if (!this._message.value && (this._messagePublic && !this._messagePublic.value)) {
			var m = RightNow.Interface.getMessage("RESPONSE_LBL") + " " + RightNow.Interface.getMessage("REQUIRED_LBL");
			this._displayError(m);
			this._message.focus();
			return;
		}
		
		if (!this._from.value) {
			var m = RightNow.Interface.getMessage("FROM_LBL") + " " + RightNow.Interface.getMessage("REQUIRED_LBL");
			this._displayError(m);
			this._from.focus();
			return;
		}
		
		this._saveLink.value = 'Please wait...';
		if (this._requestInProgress)
			return false;

		this._disableClickListener();

		//Reset form errors & status message
		this._successMessage.innerHTML = "";
		this.Y.DOM.addClass(this._formErrorLocation, "rn_Hidden");
		this._formErrorLocation.innerHTML = "";

		// Make AJAX request:
		var eventObj = new RightNow.Event.EventObject(this, { dats: [] });
		// YAHOO.util.Connect.setForm(this._parentForm);
		eventObj.w_id = this.data.info.w_id;
		eventObj.data['w_id'] = this.data.info.w_id;
		eventObj.data['FNT_Config_ResponseFrom'] = this._fnttok;
		eventObj.data['ResponseFrom_Message'] = this._message.value;
		eventObj.data['From'] = this._from.value;
		// get the attachments
		eventObj.data['AttachmentsFolder'] = this._attachmentsFolder || "";
		eventObj.data['Files'] = "";
		eventObj.data['FilesChecked'] = "";
		//eventObj.data['Disposition'] = this.Y.one("#" + this._disposition).get('value');
		eventObj.data['PublicMessage'] = "";//this._messagePublic.value;
		// Build the FilesChecked string to post
		var f = document.getElementById("rn_" + this.instanceID + '_AttachForm');
		var els = f.elements, len = els.length;
		for ( var i = 0; i < len; i++ ) {
			if ( els[ i ].type === 'checkbox' ) {
				if ( els[ i ].checked ) {
					if (eventObj.data['FilesChecked'] && eventObj.data['FilesChecked'].length > 0) eventObj.data['FilesChecked'] += ",";
					eventObj.data['FilesChecked'] += els[ i ].value;
				}
			}
		}
		// Build the Files string to post
		for (var i = 0; i < this._attachments.length; i++ ) {
			if (eventObj.data['Files'] && eventObj.data['Files'].length > 0) eventObj.data['Files'] += ",";
			eventObj.data['Files'] += this._attachments[i].FileInfo.name;
			eventObj.data['Files'] += "|";
			eventObj.data['Files'] += this._attachments[i].FileInfo.tmp_name;
			eventObj.data['Files'] += "|";
			eventObj.data['Files'] += this._attachments[i].FileInfo.type;
		}
		if (this._challengeDivID) {
			eo.challengeHandler = RightNow.Event.createDelegate(this, this._challengeHandler);
		}
		//since the form is submitted by script, deliberately tell IE to do auto completion of the form data
		if (this.Y.UA.ie !== 0 && window.external && "AutoCompleteSaveForm" in window.external)
			window.external.AutoCompleteSaveForm(document.getElementById(this._parentForm));

		// RightNow.Event.fire("evt_submitFormRequest", eo);
		RightNow.Ajax.makeRequest(this.data.attrs.fnt_validate_token, eventObj.data, {
			successHandler : this._formSubmitResponse,
			failureHandler : this._onFormValidationFail,
			challengeHandler : this._challengeHandler,
			scope : this,
			type : 'POST',
			data : eventObj.data,
			json : true
		});
	},

	/**
	 * Report an incorrect or absent abuse challenge response.
	 * @param errorMessage string Error message to display.
	 */
	_reportChallengeError : function (errorMessage) {
		if (!errorMessage) {
			errorMessage = RightNow.Interface.getMessage("PLS_VERIFY_REQ_ENTERING_TEXT_IMG_MSG");
		}
		var errorLinkAnchorID = "rn_ChallengeErrorLink",
		errorLink = "<div><b><a id ='" + errorLinkAnchorID + "' href='javascript:void(0);'>" + errorMessage + "</a></b></div>";

		RightNow.UI.Form.errorCount++;
		if (RightNow.UI.Form.chatSubmit && RightNow.UI.Form.errorCount === 1)
			this._formErrorLocation.innerHTML = "";

		this._formErrorLocation.innerHTML += errorLink;
		document.getElementById(errorLinkAnchorID).onclick = RightNow.Event.createDelegate(this, function () {
				this._challengeProvider.focus();
				return false;
			});
	},

	/**
	 * Ensure that a div exists to display the abuse challenge in.
	 */
	_createChallengeDiv : function () {
		var challengeDiv = document.getElementById(this._challengeDivID);
		if (!challengeDiv) {
			challengeDiv = document.createElement("div");
			challengeDiv.id = this._challengeDivID;
			this.Y.DOM.insertBefore(challengeDiv, this._saveLink);
		}
	},

	/**
	 * Called back by the RightNow.Ajax layer when it determines that the server responded that a challenge is required.
	 * @param abuseResponse An object returned by the server containing the challenge provider script.
	 * @param requestObject The original request object
	 * @param isRetry A boolean indicating if the the server said that the request contained an incorrect challenge response.
	 */
	_challengeHandler : function (abuseResponse, requestObject, isRetry) {
		this._createChallengeDiv();

		if (!this._challengeProvider) {
			this._challengeProvider = RightNow.UI.AbuseDetection.getChallengeProvider(abuseResponse);
			RightNow.Event.subscribe("evt_formFieldValidateRequest", this._onValidateChallengeResponse, this);
		}
		this._challengeProvider.create(this._challengeDivID, RightNow.UI.AbuseDetection.options);
		this._reportChallengeError(RightNow.UI.AbuseDetection.getDialogCaption(abuseResponse));
		this._onFormValidationFail();
	},

	/**
	 * Event handler for form validation.
	 */
	_onValidateChallengeResponse : function () {
		var eo = new RightNow.Event.EventObject();
		//Challenges have no data to be passed with the form
		eo.data = {
			form : false
		};
		if (RightNow.UI.Form.form === this._parentForm) {
			var inputs = this._challengeProvider.getInputs(this._challengeDivID);
			if (inputs.abuse_challenge_response) {
				for (var key in inputs) {
					if (inputs.hasOwnProperty(key)) {
						RightNow.Ajax.addRequestData(key, inputs[key]);
					}
				}
				RightNow.Event.fire("evt_formFieldValidateResponse", eo);
			} else {
				this._reportChallengeError();
				RightNow.UI.Form.formError = true;
			}
		} else {
			RightNow.Event.fire("evt_formFieldValidateResponse", eo);
		}
		RightNow.Event.fire("evt_formFieldCountRequest");
	},

	/**
	 * Event handler for when form has been validated
	 */
	_onFormValidated : function () {
		if (RightNow.UI.Form.form === this._parentForm && RightNow.UI.Form.formFields.length > 0) {
			//Show the loading icon and status message
			//this.Y.DOM.removeClass("rn_" + this.instanceID + "_LoadingIcon", "rn_Hidden");
			if (this._successMessage)
				this._successMessage.innerHTML = RightNow.Interface.getMessage('SUBMITTING_ELLIPSIS_MSG');
		}
	},

	/**
	 * Event handler for when form fails validation check
	 */
	_onFormValidationFail : function () {
		this._saveLink.value = 'Continue...';
		if (RightNow.UI.Form.form === this._parentForm) {
			//give error div a common error message CSS class
			this.Y.DOM.addClass(this._formErrorLocation, "rn_MessageBox");
			this.Y.DOM.addClass(this._formErrorLocation, "rn_ErrorMessage");
			this.Y.DOM.removeClass(this._formErrorLocation, "rn_Hidden");
			if (this._formErrorLocation.tabIndex === 0) {
				window.scrollTo(0, this._formErrorLocation.offsetTop - 20); //20px buffer above error div
				//focusing half a second later helps screen readers anounce the message correctly
				this.Y.Lang.later(500, this, function () {
					this._formErrorLocation.tabIndex = 0;
					this._formErrorLocation.focus();
					this._formErrorLocation.tabIndex = 1;
				});
			} else {
				//focus first link in the error box and scroll to it
				var firstField = this.Y.DOM.getElementBy(function (e) {
						return true;
					}, "A", this._formErrorLocation);
				if (firstField && firstField.focus)
					firstField.focus();
				window.scrollTo(0, this._formErrorLocation.offsetTop - 20); //20px buffer above error div
			}
			this._enableClickListener();
		}
	},

	/**
	 * Event handler for when form submission returns from the server
	 * @param type string Event name
	 * @param args object Event arguments
	 */
	_formSubmitResponse : function (response, originalEventObj) {
		var result;
		if (response.result) {
			if (this._successMessage) {
				this.Y.DOM.removeClass(this._successMessage, "rn_Hidden");
				this._successMessage.innerHTML = RightNow.Interface.getMessage("CUSTOM_MSG_FNT_THANKS_TEXT");
				this.Y.DOM.removeClass(this._container, "rn_ForwardAndTrackInput");
				this.Y.DOM.addClass(this._container, "rn_Hidden");
			}
		} else {
			this.Y.DOM.addClass(this._container, "rn_Hidden");
			// show the error
			var m = response.errorMessage ? response.errorMessage : 'undefined';
			if (!m) {
				if (response.data) {
					var obj = response.data;
					var str = '';
					for (var p in obj) {
						if (obj.hasOwnProperty(p)) {
							str += p + '::' + obj[p] + '\n';
						}
					}
					m = str;
				} else {
					// TODO: display message for unspecified error and allow the user to check the data with a page reload?
					location.reload();
				}
			}
			this._formErrorLocation.innerHTML = m;
			this.Y.DOM.removeClass(this._formErrorLocation, "rn_Hidden");
			this.Y.DOM.addClass(this._formErrorLocation, "rn_ErrorMessage");
		}
	},

	/**
	 * Enable the form submit control by enabling button and adding an onClick listener.
	 */
	_enableClickListener : function () {
		this._saveLink.disabled = this._requestInProgress = false;
		this.Y.one(this._saveLink).on("click", this._onButtonClick, this);
	},

	/**
	 * Disable the form submit control by disabling button and removing the onClick listener.
	 */
	_disableClickListener : function () {
		this._saveLink.disabled = this._requestInProgress = true;
		this.Y.one(this._saveLink).purge();
	},

	/**
	 * Event handler for when value changes in file attachment input
	 */
	_onFileAdded : function () {
		if (this._inputField.value === "" || this._uploading)
			return;

		//Check file extension if they've specified an accepted list
		var Dom = this.Y.DOM;
		this._uploading = true;
		this._saveLink.value = RightNow.Interface.getMessage("UPLOADING_ELLIPSIS_MSG");
		var fid = "rn_" + this.instanceID + "_AttachForm";
		var f = document.getElementById(fid);
		this.originalEnctype = f.enctype;
		f.enctype = f.encoding = "multipart/form-data";
		// Make AJAX request:
		var eventObj = new RightNow.Event.EventObject(this, { data: { "w_id": this.data.info.w_id } });
		eventObj.data['FNT_Config_ResponseFrom'] = this._fnttok;
		eventObj.data['AttachmentFolder'] = this._attachmentsFolder || "";
		//since the form is submitted by script, deliberately tell IE to do auto completion of the form data
		if (this.Y.UA.ie !== 0 && window.external && "AutoCompleteSaveForm" in window.external)
			window.external.AutoCompleteSaveForm(document.getElementById(this._parentForm));
		
		RightNow.Ajax.makeRequest("/cc/fntUpload/upload", eventObj.data, {
			timeout : (RightNow.Interface.getConfig("CP_FILE_UPLOAD_MAX_TIME") || 300) * 1000,
			successHandler : this._fileUploadedHandler,
			upload : fid,
			scope : this,
			type : 'POST',
			data : eventObj,
			json : true
		});
		return true;
	},

	_fileUploadedHandler : function (responseObject) {
		if (responseObject.error) {
			this._displayError(responseObject.errorMessage);
			return;
		}
		this._uploading = false;
		this._saveLink.value = RightNow.Interface.getMessage("CUSTOM_MSG_FNT_RESPOND_BTN_LBL");
		var f = document.getElementById("rn_" + this.instanceID + '_AttachForm');
		f.enctype = this.originalEnctype;
		if (!responseObject.data) {
			this._displayError("Something went wrong.");
			return;
		}
		this._attachments.push(responseObject.data);
		if (this._attachmentsFolder && this._attachmentsFolder != responseObject.data.AttachmentsFolder) {
			this._displayError("Attachments Folder problem.");
			return;
		}
		this._attachmentsFolder = responseObject.data.AttachmentsFolder;
		var id = "rn_" + this.instanceID + "_FileChecked";
		this._files.innerHTML += "<div><input id='" + id + "' type='checkbox' checked='true' value='" + responseObject.data.FileInfo.name + "' />&nbsp;&nbsp;" + responseObject.data.FileInfo.name + "</div><br/>";
		if(this._attachments.length === this._max_attachments) {
			this._inputField.disabled = true;
			this._files.innerHTML += "<div class='rn_ErrorMessage'>" + this._max_attachments_msg + "</div>";
        }
	},

	/**
	 * Event handler for when user performs a keypress in the file input field.
	 * Overrides older browser behavior that allows users to type input, causing upload errors.
	 * Allows tabbing and enter keypress to continue through.
	 * @param event Event keypress event
	 */
	_onKeyPress : function (event) {
		var keyPressed = event.keyCode;
		//allow tabbing and enter keypress through
		if (keyPressed && keyPressed !== 13 && keyPressed !== 9) {
			this.Y.Event.stopEvent(event);
		} else if (this.Y.UA.ie && keyPressed === 13) {
			//IE submits the form when the user hits enter while focused on the input
			//field/button. Manually invoking a click will eventually invoke a security
			//exception in IE for some reason. Therefore, just supress the key and do nothing
			this.Y.Event.stopEvent(event);
		}
	},

	_displayError : function (errorMessage) {
		var commonErrorDiv = this._formErrorLocation;
		if (commonErrorDiv) {
			var errorLink = "<div><b><a href='javascript:void(0);' onclick='return false;'>" + errorMessage + " </a></b></div>";
			commonErrorDiv.innerHTML = errorLink;

			this.Y.DOM.addClass(this._formErrorLocation, "rn_MessageBox");
			this.Y.DOM.addClass(this._formErrorLocation, "rn_ErrorMessage");
			this.Y.DOM.removeClass(this._formErrorLocation, "rn_Hidden");
			scrollTo(0, 0);
		}
	}
});