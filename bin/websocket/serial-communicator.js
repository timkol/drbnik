const EventEmitter = require('events');
var SerialPort = require('serialport');

class SerialCommunicator extends EventEmitter {
    constructor(socket) {
        super();
        this.port = new SerialPort(socket, {
            parser: SerialPort.parsers.readline('\n')
        });
        
        this.port.on('open', () => {
            console.log("Serial connection opened");
            this.emit('open');
        });
        
        this.port.on('data', (d) => {
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
    
/**
* 1 -> points (int,int,int)
* 2 -> randomize
* 3 -> derandomize
* 4 -> demo
* 5 -> blink (int,int,int)
* 6 -> lumos (int,int,int)
* 7 -> nox
*/
    
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
        this._send('1', gryffindor + "|" + hufflepuff + "|" + ravenclaw + "|" + slytherin);
    }
    
    spellFlash(R, G, B) {
        this._send('5', R + "|" + G + "|" + B);
    }
    
    lumos(R,G,B) {
        this._send('6', R + "|" + G + "|" + B);
    }
    
    nox() {
        this._send('7', "");
    }
    
    tarantallegra() {
        this._send('2', "");
    }
    
    finiteTarantallegra() {
        this._send('3', "");
    }
    
    demo() {
        this._send('4', "");
    }
    
    _send(command, param) {
        var data = command + ';' + param + "\n";
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