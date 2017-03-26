var app = require('express')(); //TODO unnecessary
var http = require('http');
var server = http.Server(app);
var io = require('socket.io')(server);
var TokenAuthenticator = require('./token-authenticator');
var SerialCommunicator = require('./serial-communicator');

process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

var port = new SerialCommunicator('/dev/ttyACM0');

io.on('connection', function(socket){
    console.log('a user connected');
    
    var authenticator = new TokenAuthenticator();
    socket.on('login-path', function(msg) {
        authenticator.setHost(msg['host']);
        authenticator.setLoginPath(msg['path']);
    });
    
    port.on('connect', function(token) {
        authenticator.login(token, true, function(jsonUser, cookie) {
            socket.emit('login', jsonUser);
        }, function() {
            console.log("Unauthorized access: "+token);
        });
    });
    port.on('disconnect', function() {
        socket.emit('logout');
    });
});

server.listen(3000, 'localhost', function(){
    console.log('listening on 127.0.0.1:3000');
});