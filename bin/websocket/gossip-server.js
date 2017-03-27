var app = require('express')();
var TokenAuthenticator = require('./token-authenticator');
var SerialCommunicator = require('./serial-communicator');
var EffectManager = require('./effect-manager');
var PointsManager = require('./points-manager');

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

var port = new SerialCommunicator('/dev/ttyACM0');
var authenticator = new TokenAuthenticator();
var points = new PointsManager();
var effects = new EffectManager(port);

const HOST = 'localhost';
const LOGIN_PATH = '/sign/intermediate';
const POINTS_PATH = '/team-points/local-add';
const REFRESH_PATH = '/team-points/refresh';

authenticator.setHost(HOST);
authenticator.setLoginPath(LOGIN_PATH);
points.setHost(HOST);
points.setPointsPath(POINTS_PATH);
points.setRefreshPath(REFRESH_PATH);

port.on('open', function () {
    points.refreshPoints(() => {
        console.log("refresh success");
    }, () => {
        console.log("refresh error");
    });
});

port.on('connect', function(token) {
    authenticator.login(token, false, function(jsonUser, cookie) {
        if(!TokenAuthenticator.isOrg(jsonUser)) {
            console.log("Unauthorized access, not org: "+token+" ("+jsonUser+")");
            return;
        }
        port.connectionSucceeded();
        points.startRecording(cookie, function(){
            port.pointsParseSucceeded();
        }, function(filename){
            port.pointsParseFailed();
            console.log("Failed parse: "+filename+" ("+jsonUser+")");
        }, function(){
            port.pointsParseFailed();
            console.log("Unauthorized access on points: "+token+" ("+jsonUser+")");
        });        
    }, function() {
        console.log("Unauthorized access: "+token);
    });
});
port.on('disconnect', function() {
    points.endRecording();
});

app.get('/points', function (req, res) {
    var gryffindor = Number(req.query.gryffindor);
    var hufflepuff = Number(req.query.hufflepuff);
    var ravenclaw = Number(req.query.ravenclaw);
    var slytherin = Number(req.query.slytherin);
    effects.updatePoints(gryffindor, hufflepuff, ravenclaw, slytherin);
    res.send("Points updated");
});

app.listen(3000, 'localhost', function(){
    console.log('listening on 127.0.0.1:3000');
});