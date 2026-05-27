RightNow.namespace('Custom.Widgets.output.Notification');
Custom.Widgets.output.Notification = RightNow.Widgets.extend({ 
    /**
     * Widget constructor.
     */
    constructor: function() {
        //this.announcementDecision();
    },

    /* setAnnCookie: function() {
        var expiration = this.setExpTime();
        console.log(expiration);
        $.cookie('rn_Ann', true, { expires: expiration, path: '/' });
    }, */

    setExpTime: function() {
        if(!isNaN(this.data.attrs.cookie_expiration)) {
            return parseInt(this.data.attrs.cookie_expiration);
        //} else if(moment(this.data.attrs.cookie_expiration, 'MM-DD-YYYY',true).isValid()) {
            //var nowDate = Date();
            //var newDate = moment(this.data.attrs.cookie_expiration, 'MM-DD-YYYY').format('ddd, MMM Do YYYY, h:mm:ss a');
            //return nowDate.diff(newDate, 'days') // 1
            //return moment(this.data.attrs.cookie_expiration, 'MM-DD-YYYY').format('ddd, MMM Do YYYY, h:mm:ss a GMT-0600 (MDT)');
        } else {
            return; // Not a valid date or number of day defaults to session cookie
        }
    },

    /* announcementDecision: function() {
        if(!$.cookie('rn_Ann')) {
            if((this.data.attrs.announcement_text == "No notifications at this time") || (!this.data.attrs.announcement_text)) {
                if(this.data.attrs.show_empty_announcement) {
                    this.showAnnouncement();
                }
            } else {
                this.showAnnouncement();
            }
        }
    }, */

    /**
     * 
     */
    showAnnouncement: function() {
        if(this.data.attrs.modal) {
            $('.rn_AnnouncementBody').dialog({
                modal: true,
                title: this.data.attrs.announcement_title,
                width: this.data.attrs.modal_width
            });
        } else {
            $('#rn_AnnouncementContainer').show();
        }
        this.setAnnCookie();
    }
});