/**
 * Created by stevenhawley on 7/11/2016.
 * Modified by kurtdusek 09/13/16 and BEYOND!!
 */
var CXRequest = function(baseURL)
{
    if(!window.external)
        throw new Error("Window External not supported on this browser");
    if(typeof window.external.GetCustomObject == 'undefined')
        throw new Error("CX Integration Methods not defined");
        
    var URL = "http://localhost:8080/addin";
            
	if(baseURL !== undefined)
	{
		var validateURL = new RegExp(/[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi);
		if (baseURL.test(value))
		{
	        URL = baseURL
		}
		else
		{
			throw new Error("Invalid request URL");			
		}
	}

    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            window.alert(xmlhttp.responseText);
        }
    };




    this.triggerNamedEvent = function(object)
    {
        try
        {
            xmlhttp.open("GET", URL + "?eventName=TRIGGERNAMEDEVENT&eventData=" + Base64.encode("{" + '"NamedEvent":"' + object.name + '","ID":"' + object.id + '","TypeName":"' + object.type + '"}') + '&Token":"' + Math.random(), true);
            xmlhttp.send();
        }
        catch(er)
        {
            window.alert(er.message);
        }

    };

    this.openIncident = function(object)
    {
	alert("openIncident");
        if(!window.external.Contact)
        {
            throw new Error("This function can only be called in the Contact workspace.");
        }
        try
        {
	    var test = URL + "?eventName=OPENINCIDENT&eventData=" + Base64.encode("{" + '"ContactID":"' + object.contactID + '","ReferenceNumber":"' + object.ReferenceNumber + '","Subject":"' + object.type + '"}')+ '&Token":"' + Math.random();

            xmlhttp.open("GET", URL + "?eventName=OPENINCIDENT&eventData=" + Base64.encode("{" + '"ContactID":"' + object.contactID + '","ReferenceNumber":"' + object.ReferenceNumber + '","Subject":"' + object.type + '"}')+ '&Token":"' + Math.random(), true);

            xmlhttp.send();

        }
        catch(er)
        {
            window.alert(test);
            window.alert(er.message);
        }

    };

    this.copyNote = function(object)
    {
        try
        {
            xmlhttp.open("GET", URL + "?eventName=COPYNOTE&eventData=" + Base64.encode("{" + '"ID":"' + object.id + '","Text":"' + object.content + '","TypeName":"' + object.type + '"}')+ '&Token":"' + Math.random(), true);
            xmlhttp.send();
        }
        catch(er)
        {
            window.alert(er.message);
        }

    };

    this.openContentPane = function(object)
    {
        try
        {

            xmlhttp.open("GET", URL + "?eventName=OPENCONTENTPANE&eventData=" + Base64.encode("{" + '"ID":"' + object.id + '","URL":"' +  object.Url + '"}')+ '&Token":"' + Math.random(), true);
            xmlhttp.send();
        }
        catch(er)
        {
            window.alert(er.message);
        }

   }
   
   this.openContact = function(object)
   {
	   try{
		   xmlhttp.open("GET", URL + "?eventName=OPENCONTACT&eventData=" + Base64.encode("{" + '"MemberID":"' + object.memberID+ '"}')+ '&Token":"' + Math.random(), true);
		   xmlhttp.send();
	   }
	   catch(er)
	   {
		   window.alert(er.message);
	   }
   }
   
   	this.openOrder = function(object)
	{
	   try{
		   xmlhttp.open("GET", URL + "?eventName=OPENORDER&eventData=" + Base64.encode("{" + '"OrderID":"' + object.orderID+ '",' + '"MemberID":"'+ object.memberID + '"}')+ '&Token":"' + Math.random(), true);
		   xmlhttp.send();
	   }
	   catch(er)
	   {
		   window.alert(er.message);
	   }
	}
    var Base64 = {

        // private property
        _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode : function(input) {
            var output = "";
            var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
            var i = 0;

            input = Base64._utf8_encode(input);

            while (i < input.length) {

                chr1 = input.charCodeAt(i++);
                chr2 = input.charCodeAt(i++);
                chr3 = input.charCodeAt(i++);

                enc1 = chr1 >> 2;
                enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
                enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
                enc4 = chr3 & 63;

                if (isNaN(chr2)) {
                    enc3 = enc4 = 64;
                } else if (isNaN(chr3)) {
                    enc4 = 64;
                }

                output = output + this._keyStr.charAt(enc1)
                    + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3)
                    + this._keyStr.charAt(enc4);

            }

            return output;
        },

        // public method for decoding
        decode : function(input) {
            var output = "";
            var chr1, chr2, chr3;
            var enc1, enc2, enc3, enc4;
            var i = 0;

            input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

            while (i < input.length) {

                enc1 = this._keyStr.indexOf(input.charAt(i++));
                enc2 = this._keyStr.indexOf(input.charAt(i++));
                enc3 = this._keyStr.indexOf(input.charAt(i++));
                enc4 = this._keyStr.indexOf(input.charAt(i++));

                chr1 = (enc1 << 2) | (enc2 >> 4);
                chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                chr3 = ((enc3 & 3) << 6) | enc4;

                output = output + String.fromCharCode(chr1);

                if (enc3 != 64) {
                    output = output + String.fromCharCode(chr2);
                }
                if (enc4 != 64) {
                    output = output + String.fromCharCode(chr3);
                }

            }

            output = Base64._utf8_decode(output);

            return output;

        },

        // private method for UTF-8 encoding
        _utf8_encode : function(string) {
            string = string.replace(/\r\n/g, "\n");
            var utftext = "";

            for ( var n = 0; n < string.length; n++) {

                var c = string.charCodeAt(n);

                if (c < 128) {
                    utftext += String.fromCharCode(c);
                } else if ((c > 127) && (c < 2048)) {
                    utftext += String.fromCharCode((c >> 6) | 192);
                    utftext += String.fromCharCode((c & 63) | 128);
                } else {
                    utftext += String.fromCharCode((c >> 12) | 224);
                    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                    utftext += String.fromCharCode((c & 63) | 128);
                }

            }

            return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode : function(utftext) {
            var string = "";
            var i = 0;
            var c = 0, c1 = 0, c2 = 0;

            while (i < utftext.length) {

                c = utftext.charCodeAt(i);

                if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
                } else if ((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i + 1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
                } else {
                    c2 = utftext.charCodeAt(i + 1);
                    c3 = utftext.charCodeAt(i + 2);
                    string += String.fromCharCode(((c & 15) << 12)
                        | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
                }

            }

            return string;
        }

    }
};
