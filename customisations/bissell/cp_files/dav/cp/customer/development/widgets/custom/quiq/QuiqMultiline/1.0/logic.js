RightNow.namespace('Custom.Widgets.quiq.QuiqMultiline');
Custom.Widgets.quiq.QuiqMultiline = RightNow.Widgets.Multiline.extend({
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.Multiline#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
            this._processContent();

            if(this.data.attrs.links_regex_to_send){
              this.data.attrs.links_regex_to_send = new RegExp(this.data.attrs.links_regex_to_send);
            }
            else {
              this.data.attrs.links_regex_to_send = /.+/;
            }
        }

        /**
         * Overridable methods from Multiline:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // _setFilter: function()
        // _searchInProgress: function(evt, args)
        // _setLoading: function(loading)
        ,
        _onReportChanged: function(type, args){
          var newdata = args[0].data,
              ariaLabel, firstLink,
              newContent = "";

          newdata.attrs = this.data.attrs;

          this._displayDialogIfError(newdata.error);

          if (!this._contentDiv) return;

          if(newdata.total_num > 0) {
              ariaLabel = this.data.attrs.label_screen_reader_search_success_alert;
              newdata.hide_empty_columns = this.data.attrs.hide_empty_columns;
              newdata.hide_columns = this.data.js.hide_columns;
              newContent = new EJS({text: this.getStatic().templates.view}).render(newdata);
          }
          else {
              ariaLabel = this.data.attrs.label_screen_reader_search_no_results_alert;
          }

          this._updateAriaAlert(ariaLabel);
          this._contentDiv.set("innerHTML", newContent);

          if (this.data.attrs.hide_when_no_results) {
              this.Y.one(this.baseSelector)[((newContent) ? 'removeClass' : 'addClass')]('rn_Hidden');
          }

          this._setLoading(false);
          RightNow.Url.transformLinks(this._contentDiv);

          this._processContent();
        }
        // _displayDialogIfError: function(error)
        // _updateAriaAlert: function(text)
    },

    _processContent: function(){
      if(this.data.attrs.append_links_to_message){
        this._addQuiqListenersToLinks();
      }
      else {
        this._disableLinks();
      }
      this._addToggleHiddenContentListeners();
      this._addQuiqListeners();
    },

    _disableLinks: function() {
      this._contentDiv.all('a').each(function(node){
        node.on('click', function(e){
          e.preventDefault();
        })
      })
    },

    _addToggleHiddenContentListeners: function(){
      this._contentDiv.all('[data-toggler]').each(function(node){
        node.on('click', function(e){
          console.log(node.getData('toggler'));
          this._contentDiv.all('[data-togglee="'+node.getData('toggler')+'"]').toggleClass('rn_Hidden');
        }, this)
      }, this)
    },

    _addQuiqListenersToLinks: function() {
      this._contentDiv.all('a').each(function(node){
        node.on('click', function(e){
          e.preventDefault();
          e.stopImmediatePropagation();
          var link = node.get('href');

          var link_matches = link.match(this.data.attrs.links_regex_to_send);

          link = link_matches[0];

          var parent = e.currentTarget.ancestor('[quiq-item-container]');
          var message_elem = parent.one('[quiq-message-content]');
          var message = this.getPlainText(message_elem._node);

          message = message.substring(0, this.data.attrs.message_content_length) + this.data.attrs.message_link_separator + link;

          this._sendQuiqMessage(message);
        }, this)
      }, this)
    },

    _addQuiqListeners: function() {
      this._contentDiv.all('[quiq-message-sender]').each(function(node){
        node.on('click', this._handleQuiqMessageSenderClick, this)
      }, this)
    },

    _handleQuiqMessageSenderClick: function(e){
	  //This change sends the user the link instead of the text
		
      // var parent = e.currentTarget.ancestor('[quiq-item-container]');
      // var message_elem = parent.one('[quiq-message-content]');
      // var message = this.getPlainText(message_elem._node);
      // this._sendQuiqMessage(message);
	  
	  var parent = e.currentTarget.ancestor('[quiq-item-container]');
      var message_elem = parent.one('.rn_Element1');
      var message = message_elem._node.children[0].children[0].getAttribute("href")
	  //console.log("https://bissell-ext.custhelp.com" + message_elem._node.children[0].children[0].getAttribute("href"));
		if (!message.includes('bissell-ext')) { 
		  message = "https://bissell-ext.custhelp.com" + message_elem._node.children[0].children[0].getAttribute("href");
		}
		
		if (message.includes('session')) { 
		  message = message.replace(/[s][e][s][s][i][o][n].*/gm,'');
		}
		console.log(message);
		
      this._sendQuiqMessage(message);
    },

    _sendQuiqMessage: function(message){
      RightNow.Event.fire('quiq_SendMessage', message);
    },

    getPlainText: function(node){
    	// used for testing:
    	//return node.innerText || node.textContent;


    	var normalize = function(a){
    		// clean up double line breaks and spaces
    		if(!a) return "";
    		return a.replace(/ +/g, " ")
    				.replace(/[\t]+/gm, "")
    				.replace(/[ ]+$/gm, "")
    				.replace(/^[ ]+/gm, "")
    				.replace(/\n+/g, "\n")
    				.replace(/\n+$/, "")
    				.replace(/^\n+/, "")
    				.replace(/\nNEWLINE\n/g, "\n\n")
    				.replace(/NEWLINE\n/g, "\n\n"); // IE
    	}
    	var removeWhiteSpace = function(node){
    		// getting rid of empty text nodes
    		var isWhite = function(node) {
    			return !(/[^\t\n\r ]/.test(node.nodeValue));
    		}
        // added for quiq
    		var quiqIgnore = function(node) {
          if(typeof node.hasAttribute !== 'function'){
            return false;
          }
          if(node.hasAttribute('quiq-ignore-text')){
            return true;
          }
    			return false;
    		}
    		var ws = [];
    		var findWhite = function(node){
    			for(var i=0; i<node.childNodes.length;i++){
    				var n = node.childNodes[i];
    				if (n.nodeType==3 && isWhite(n) || quiqIgnore(n)){
    					ws.push(n)
    				}else if(n.hasChildNodes()){
    					findWhite(n);
    				}
    			}
    		}
    		findWhite(node);
    		for(var i=0;i<ws.length;i++){
    			ws[i].parentNode.removeChild(ws[i])
    		}

    	}
    	var sty = function(n, prop){
    		// Get the style of the node.
    		// Assumptions are made here based on tagName.
    		if(n.style[prop]) return n.style[prop];
    		var s = n.currentStyle || n.ownerDocument.defaultView.getComputedStyle(n, null);
    		if(n.tagName == "SCRIPT") return "none";
    		if(!s[prop]) return "LI,P,TR".indexOf(n.tagName) > -1 ? "block" : n.style[prop];
    		if(s[prop] =="block" && n.tagName=="TD") return "feaux-inline";
    		return s[prop];
    	}

    	var blockTypeNodes = "table-row,block,list-item";
    	var isBlock = function(n){
    		// diaply:block or something else
    		var s = sty(n, "display") || "feaux-inline";
    		if(blockTypeNodes.indexOf(s) > -1) return true;
    		return false;
    	}
    	var recurse = function(n){
    		// Loop through all the child nodes
    		// and collect the text, noting whether
    		// spaces or line breaks are needed.
    		if(/pre/.test(sty(n, "whiteSpace"))) {
    			t += n.innerHTML
    				.replace(/\t/g, " ")
    				.replace(/\n/g, " "); // to match IE
    			return "";
    		}
    		var s = sty(n, "display");
    		if(s == "none") return "";
    		var gap = isBlock(n) ? "\n" : ""; // changed so " " doesn't break words
    		t += gap;
    		for(var i=0; i<n.childNodes.length;i++){
    			var c = n.childNodes[i];
    			if(c.nodeType == 3) t += c.nodeValue;
    			if(c.childNodes.length) recurse(c);
    		}
    		t += gap;
    		return t;
    	}
    	// Use a copy because stuff gets changed
    	node = node.cloneNode(true);
    	// Line breaks aren't picked up by textContent
    	node.innerHTML = node.innerHTML.replace(/<br>/g, "\n");

    	// Double line breaks after P tags are desired, but would get
    	// stripped by the final RegExp. Using placeholder text.
    	var paras = node.getElementsByTagName("p");
    	for(var i=0; i<paras.length;i++){
    		paras[i].innerHTML += "NEWLINE";
    	}

    	var t = "";
    	removeWhiteSpace(node);
    	// Make the call!
    	return normalize(recurse(node));
    }
});
