function ondataupdated(obj) {
    if(CXObject.instances[obj]) {
        for(var i = 0; i < CXObject.instances[obj].length; ++i) {
            CXObject.instances[obj][i].dataChanged();
        }
    }
}

var CXObject = function(objectName) {

    if(!window.external)
        throw new Error("Window External not supported on this browser");
    if(typeof window.external.GetCustomObject == 'undefined')
        throw new Error("CX Integration Methods not defined");

    var packageName, cxObject;
    var listeners = {}
    if(objectName.indexOf(".") >= 0) {
        packageName = objectName.split('.')[0];
        objectName = objectName.split('.')[1];
    }

    if(packageName) {
        cxObject = window.external.GetCustomObject(packageName, objectName);
        if(!cxObject)
            throw new Error("Not able to initialize CX Custom Object " + packageName + "." + objectName);
    } else {
        if(!window.external[objectName])
            throw new Error("No CX Object " + objectName + " available");
        cxObject = window.external[objectName]
    }

    this.fetchValue = function(fieldName) {
        if(fieldName.indexOf("$") >= 0 || packageName) {
            return cxObject.GetCustomFieldByName(fieldName);
        } else {
            return cxObject[fieldName];
        }
    }

    this.setValue = function(fieldName, value) {
        if(fieldName.indexOf("$") >= 0 || packageName) {
            cxObject.SetCustomFieldByName(fieldName, value);
        } else {
            cxObject[fieldName] = value;
        }
    }

    this.getObjectName = function() {
        if(packageName) {
            return packageName + "." + objectName;
        } else {
            return objectName;
        }
    }

    this.setListener = function(field, cb) {
        if(!listeners['fields'])
            listeners['fields'] = new Array();
        if(!listeners['fields'][field]) {
            var f = {
                prevValue : this.fetchValue(field),
                events : [cb]
            };
            listeners['fields'][field] = f;
        } else {
            listeners['fields'][field].events.push(cb);
        }
    }

    this.removeListener = function(field, cb) {
        if(listeners['fields'] && listeners['fields'][field] && listeners['fields'][field].events && listeners['fields'][field].events.length > 0) {
            for(var i = 0; i < listeners['fields'][field].events.length; ++i) {
                if(listeners['fields'][field].events[i] == cb) {
                    listeners['fields'][field].events.splice(i, 1);
                    break;
                }
            }
        }
    }

    this.dataChanged = function() {
        if(listeners['fields']) {
            for(var field in listeners['fields']) {
                var newValue = this.fetchValue(field);
                if(listeners['fields'][field].prevValue != newValue) {
                    for(var i = 0; i < listeners['fields'][field].events.length; ++i) {
                        listeners['fields'][field].events[i](listeners['fields'][field].prevValue, newValue, field, this);
                    }
                    listeners['fields'][field].prevValue = newValue;
                }
            }
        }
    }

    this.isCustomObject = function() {
        return (packageName != undefined);
    }

    if(!CXObject.instances[this.getObjectName()]) {
        CXObject.instances[this.getObjectName()] = new Array();
    }
    CXObject.instances[this.getObjectName()].push(this);
}

CXObject.instances = {};

CXObject.clearInstance = function(instance) {
    if(instance) {
        if(CXObject.instances[instance.getObjectName()]) {
            for(var i = 0; i < CXObject.instances[instance.getObjectName()].length; ++i) {
                if(CXObject.instances[instance.getObjectName()][i] == instance) {
                    CXObject.instances[instance.getObjectName()].splice(i, 1);
                    break;
                }
            }
        }
    } else {
        CXObject.instances = {};
    }
}
