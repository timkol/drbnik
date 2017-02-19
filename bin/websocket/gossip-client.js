var app = require('express')(); //TODO unnecessary
var http = require('http');
var server = http.Server(app);
var io = require('socket.io')(server);
var querystring = require('querystring');

var stdin = process.openStdin();

io.on('connection', function(socket){
    console.log('a user connected');
    var requestOptions = {
        host: '',
        path: '',
        method: 'POST',
        headers: {"Content-type": "application/x-www-form-urlencoded", "Accept": "application/json"}
    };
    socket.on('login-path', function(msg) {
        requestOptions['host'] = msg['host'];
        requestOptions['path'] = msg['path'];
    });

    stdin.addListener("data", function(d) {
    // note:  d is an object, and when converted to a string it will
    // end with a linefeed.  so we (rather crudely) account for that  
    // with toString() and then trim() 
        console.log("you entered: [" + 
            d.toString().trim() + "]");
        msg = d.toString().trim();
        if(msg === 'l') {
            var postData = querystring.stringify({token: "sem_prijde_token_z_NFC"});
            var req = http.request(requestOptions, function(response) {
                var str = '';
                response.on('data', function (chunk) {
                    str += chunk;
                });
                response.on('end', function () {
                    socket.emit('login', JSON.parse(str));
                });
            });
            req.write(postData);
            req.end();
        }
        else if(msg === 'k') {
            socket.emit('logout');
        }
    });
});

server.listen(3000, function(){
    console.log('listening on *:3000');
});