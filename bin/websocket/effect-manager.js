const LEDS_IN_STRIP = 100;

class EffectManager {
    
    /**
     * 
     * @param {SerialCommunicator} port
     * @returns {nm$_effect-manager.EffectManager}
     */
    constructor(port) {
        this.port = port;
    }
    
    updatePoints(gryffindor, hufflepuff, ravenclaw, slytherin) {
        var gryffindor_led = Math.min(gryffindor, LEDS_IN_STRIP);
        var hufflepuff_led = Math.min(hufflepuff, LEDS_IN_STRIP);
        var ravenclaw_led = Math.min(ravenclaw, LEDS_IN_STRIP);
        var slytherin_led = Math.min(slytherin, LEDS_IN_STRIP);
        this.port.updatePoints(gryffindor_led, hufflepuff_led, ravenclaw_led, slytherin_led);
    }
    

}

/**
 * Module exports.
 */

module.exports = EffectManager;