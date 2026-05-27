RightNow.namespace('Custom.Widgets.input.FileAttachmentUploadWhite');
Custom.Widgets.input.FileAttachmentUploadWhite = RightNow.Widgets.FileAttachmentUpload.extend({ 
    /**
     * Place all properties that intend to
     * override those of the same name in
     * the parent inside `overrides`.
     */
    overrides: {
        /**
         * Overrides RightNow.Widgets.FileAttachmentUpload#constructor.
         */
        constructor: function() {
            // Call into parent's constructor
            this.parent();
        }

        /**
         * Overridable methods from FileAttachmentUpload:
         *
         * Call `this.parent()` inside of function bodies
         * (with expected parameters) to call the parent
         * method being overridden.
         */
        // getValue: function()
        // swapLabel: function(container, minAttachments, label, template)
        // updateMinAttachments: function(evt, constraint)
        // _onKeyPress: function(event)
        // _onFileAdded: function(e)
        // _validateFileExtension: function(fileName)
        // _displayStatus: function(message)
        // _sendUploadRequest: function()
        // _processServerError: function(response)
        // _processAttachmentThreshold: function(count)
        // _fileUploadReturn: function(response, originalEventObject)
        // _getFileFromInput: function()
        // _renderNewAttachmentItem: function(filename, count)
        // _normalizeFilename: function (filename, originalFileName)
        // _renameDuplicateFilename: function (filename)
        // _loadThumbnail: function(file, reader, callback)
        // _fileUploadFailure: function()
        // getAttachmentErrorInfo: function(attachmentInfo)
        // resetInput: function()
        // recreateInput: function()
        // removeClick: function(event, index)
        // _onValidateUpdate: function(type, args)
        // toggleErrorIndicator: function(showOrHide)
        // _setLoading: function(turnOn, statusMessage)
        // _displayError: function(errorMessage, errorLocation)
    },

    /**
     * Sample widget method.
     */
    methodName: function() {

    }
});