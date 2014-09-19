var base = base || {};
base.utils = base.utils || {};

base.utils.Request = function(options) {
    var self = this,
        response = null;

    this.method = options.method || 'GET';
    this.URI    = options.url;
    this.type   = options.type;
    this.async  = !(options.async === false);

    this.data   = null;

    if (options.data != undefined && typeof options.data === 'object') {
        this.data = options.data;
    }

    this.xhr = new XMLHttpRequest();

    if (this.type != undefined) {
        this.xhr.responseType = this.type;
    }

    this.callbacks = {};

    this.callbacks[404] = options.notFound || null;
    this.callbacks[200] = options.success || null;

    this.xhr.onreadystatechange = function(event) {
        if (this.readyState == 4) {
            response = this.response || null;
            self.callbacks[this.status] instanceof Function && self.callbacks[this.status].apply(self, [response]);
        }
    }

    this.xhr.onerror = function() {
        options.failure instanceof Function && options.failure.apply(this);
    }
};

base.utils.Request.prototype.send = function() {
    var query = [],
        url = this.URI;

    query = base.utils.Request._prepareQuery(this.data).join('&');
    if (this.method == 'GET') {
        url += !query ? '' : '?' + query;
        query = null;
    }

    this.xhr.open(this.method, url, this.async);
    this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    if (this.method != 'GET') {
        this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    this.xhr.send(query);
}

base.utils.Request._prepareQuery = function(data, raw) {
    var params = [];

    if (!data instanceof Object) return data;

    for (var name in data) {
        if (data[name] instanceof Object) {
            var keys = this._prepareQuery(data[name], true);

            for (var i = 0, l = keys.length; i < l; i++) {
                keys[i].unshift(encodeURIComponent(name));
                params.push(keys[i]);
            }

            continue;
        }

        params.push([encodeURIComponent(name), encodeURIComponent(data[name])]);
    }

    if (!raw) {
        for (var p = 0, l = params.length; p < l; p++) {
            var root  = params[p].shift(),
                value = params[p].pop(),
                keys  = params[p].length ? '[' + params[p].join('][') + ']' : '';

            params[p] = root + keys + '=' + value;
        }
    }

    return params;
}