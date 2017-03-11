var http = require('http');
var querystring = require('querystring');

class TokenAuthenticator {
    constructor() {
        this.requestOptions = {
            host: '',
            path: '',
            method: 'POST',
            headers: {"Content-type": "application/x-www-form-urlencoded", "Accept": "application/json"}
        };
    }
    
    setHost(host) {
        this.requestOptions['host'] = host;
    }
    
    setLoginPath(path) {
        this.loginPath = path;
    }
    
    _hashToken(token) {
        // "security" by obscurity
        return ("FfN_njIrdTOHzCexqnEzFH0kJYLt9vqVpe9FaMPqFMYoeXfDectp3ETx5ScN6F4jhX00GCtXT0YMoG6b1-wSf5faymdh0tlAQbMJ"+token).slice(-100);
    }
    
    login(token, intermediate, successCallback, unauthorizedCallback) {
        var postData = querystring.stringify({token: this._hashToken(token), returnToken: intermediate});
        var reqOptions = this.requestOptions.slice(); //hard copy
        reqOptions['path'] = this.loginPath;
        var req = http.request(reqOptions, function(response) {
            if(response.statusCode !== 200) {
                unauthorizedCallback();
                return;
            }
            var str = '';
            response.on('data', function (chunk) {
                str += chunk;
            });
            response.on('end', function () {
                successCallback(JSON.parse(str), response.headers['Set-Cookie']);
            });
        });
        req.write(postData);
        req.end();
    }
    
    static isOrg(person) {
        return person.roles.includes('org');
    }
}

/**
 * Module exports.
 */

module.exports = TokenAuthenticator;