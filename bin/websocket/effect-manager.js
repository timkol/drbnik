const LEDS_IN_STRIP = 120;

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
        var categories = [100, 200, 500, 1000, 2000, 5000];
        var max = Math.max(Math.abs(gryffindor), Math.abs(hufflepuff), Math.abs(ravenclaw), Math.abs(slytherin));
        var edge = 0;
        var i = 0;
        do{
            edge = categories[i++];
        } while(edge < max);
        
        var gryffindor_led = Math.floor(gryffindor/edge*LEDS_IN_STRIP);
        var hufflepuff_led = Math.floor(hufflepuff/edge*LEDS_IN_STRIP);
        var ravenclaw_led = Math.floor(ravenclaw/edge*LEDS_IN_STRIP);
        var slytherin_led = Math.floor(slytherin/edge*LEDS_IN_STRIP);
        this.port.updatePoints(gryffindor_led, hufflepuff_led, ravenclaw_led, slytherin_led);
    }
    
    castSpell(spell) {
        switch(spell) {
            case "lumos":
                this.port.lumos("255", "255", "255");
                break;
            case "nox":
                this.port.nox();
                break;
            case "avada_kedavra":
                this.port.spellFlash("0", "255", "0");
                break;
            case "tarantallegra":
                this.port.tarantallegra();
                break;
            case "finite_tarantallegra":
                this.port.finiteTarantallegra();
                break;
            default:
                console.log("Error: Spell "+spell+" unrecognized.");
                return;
        }
        console.log("Spell "+spell+" cast.");
    }
}

/**
 * Module exports.
 */

module.exports = EffectManager;