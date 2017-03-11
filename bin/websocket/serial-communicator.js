const EventEmitter = require('events');
var SerialPort = require('serialport');

class SerialCommunicator extends EventEmitter {
    constructor(socket) {
        super();
        this.port = new SerialPort(socket, {
            parser: SerialPort.parsers.readline('\n')
        });
        
        this.port.on('open', function (){
            console.log("Serial connection opened");
            this.emit('open');
        });
        
        this.port.on('data', function(d) {
            var data = d.split(';');
            switch (data[0]) {
                case 'connect':
                    this.emit('connect', data[1]);
                    break;
                case 'disconnect':
                    this.emit('disconnect');
                    break;
                case 'log':
                    console.log("Serial log: "+data[1]);
                    break;
                default:
                    console.log("Unknown serial communication: "+d);
                    break;
            }
        });
    }
    
    connectionSucceeded() {
        this._send('connect', 'success');
    }
    
    pointsParseSucceeded() {
        this._send('points-parse', 'success');
    }
    
    pointsParseFailed() {
        this._send('points-parse', 'fail');
    }
    
    /**
     * Params are number of LEDs lighted
     * @param {int} gryffindor
     * @param {int} hufflepuff
     * @param {int} ravenclaw
     * @param {int} slytherin
     * @returns {undefined}
     */
    updatePoints(gryffindor, hufflepuff, ravenclaw, slytherin) {
        this._send('points', gryffindor + "|" + hufflepuff + "|" + ravenclaw + "|" + slytherin);
    }
    
    spellFlash(color) {
        this._send('flashLED', color);
    }
    
    _send(command, param) {
        data = command + ';' + param + "\n";
        this.port.write(data, function (err) {
            console.log("Serial error: "+err);
        });
        console.log("Arduino command: "+data);
    }
}

/**
 * Module exports.
 */

module.exports = SerialCommunicator;