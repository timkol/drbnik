var http = require('http');
var querystring = require('querystring');
var spawn = require('child_process').spawn;

const SCRIPTS_PATH = '../speech/';
const AUDIO_PATH = '../../audio/speech/';

class PointsManager {
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
    
    setPointsPath(path) {
        this.pointsPath = path;
    }
    
    setRefreshPath(path) {
        this.refreshPath = path;
    }
    
    startRecording(cookie, successCallback, parseFailCallback, unauthorizedCallback) {
        var that = this;
        var filename = AUDIO_PATH + this._randomName(15);
        this.recorder = spawn(SCRIPTS_PATH + 'record.sh', [filename]);

        this.recorder.stderr.on('data', (data) => {
            console.log(`stderr: ${data}`);
        });

        this.recorder.on('close', (code) => {
            console.log(`record exited with code ${code}`);
            var stt = spawn(SCRIPTS_PATH + 'stt_points.sh', [filename]);
            var string = '';
            stt.stdout.on('data', (data) => {
                string += data;
            });
            stt.on('close', (code) => {
                console.log(`stt exited with code ${code}`);
                var parsed = that._parseString(string);
                if(parsed['status'] !== true) {
                    parseFailCallback(filename);
                    return;
                }
                that._addPoints(parsed['team'], parsed['pointsChange'], filename, cookie, successCallback, unauthorizedCallback);
            });
        });
    }
    
    endRecording() {
        this.recorder.kill();
    }
    
    refreshPoints(successCallback, errorCallback) {
        var reqOptions = JSON.parse(JSON.stringify(this.requestOptions)); //hard copy
        reqOptions['path'] = this.refreshPath;
        reqOptions['method'] = 'GET';
        //reqOptions['headers']['Cookie'] = cookie;
        var req = http.request(reqOptions, function(response) {
            if(response.statusCode !== 200) {
                errorCallback();
                return;
            }
            response.on('end', function () {
                successCallback();
            });
        });
        req.end();
    }
    
    _randomName(length)
    {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for( var i=0; i < length; i++ ) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }
        text += '.wav';
        return text;
    }
    
    _parseInt(str) {
        switch (str) {
            case 'pet':
                return 5;
                break;
            case 'deset':
                return 10;
                break;
            case 'dvacet':
                return 20;
                break;
            default:
                return null;
                break;
        }
    }
    
    _parseString(string) {
        var add = /^(pet|deset|dvacet) bodu pro (nebelvir|mrzimor|havraspar|zmijozel)$/;
        var sub = /^odebiram (pet|deset|dvacet) bodu (nebelviru|mrzimoru|havrasparu|zmijozelu)$/;
        var matches = string.match(add);
        if(matches !== null) {
            return {team: matches[2], pointsChange: this._parseInt(matches[1]), status: true};
        }
        var matches = string.match(sub);
        if(matches !== null) {
            return {team: matches[2].slice(0, -1), pointsChange: -this._parseInt(matches[1]), status: true};
        }
        return {status: false};
    }
    
    _addPoints(team, pointsChange, note, cookie, successCallback, unauthorizedCallback) {
        var postData = querystring.stringify({team: team, pointsChange: pointsChange, note: note});
        var reqOptions = JSON.parse(JSON.stringify(this.requestOptions)); //hard copy
        reqOptions['path'] = this.pointsPath;
        reqOptions['headers']['Cookie'] = cookie;
        var req = http.request(reqOptions, function(response) {
            if(response.statusCode !== 200) {
                unauthorizedCallback();
                return;
            }
            response.on('end', function () {
                successCallback();
            });
        });
        req.write(postData);
        req.end();
    }
}

/**
 * Module exports.
 */

module.exports = PointsManager;