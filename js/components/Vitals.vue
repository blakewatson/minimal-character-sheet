<template>
    <div>
        <div class="row vitals-row">
            <div class="box reverse">
                <label for="ac-field" class="block centered label">AC</label>
                <field id="ac-field" class="centered huge block padded" :value="ac" :read-only="readOnly" @update-value="updateVitals('ac', $event)"></field>
            </div>
            <div class="box box-lite">
                <label for="hp-field" class="block centered label">HP</label>
                <field id="hp-field" class="huge padded" :value="hp" :read-only="readOnly" @update-value="updateVitals('hp', $event)"></field>/<field class="normal strong" :value="maxHp" :read-only="readOnly" @update-value="updateVitals('maxHp', $event)"></field>
            </div>
            <div class="box box-lite">
                <label for="temp-hp-field" class="block centered label">Temp HP</label>
                <field id="temp-hp-field" class="centered huge block padded" :value="tempHp" :read-only="readOnly" @update-value="updateVitals('tempHp', $event)"></field>
            </div>
            <div class="box box-lite">
                <label for="hit-die-field" class="block centered label">Hit die</label>
                <field id="hit-die-field" class="huge padded" :value="hitDie" :read-only="readOnly" @update-value="updateVitals('hitDie', $event)"></field>/<field class="normal" :value="totalHitDie" :read-only="readOnly" @update-value="updateVitals('totalHitDie', $event)"></field>
            </div>
            <div class="box box-lite">
                <label for="speed-field" class="block centered label">Speed</label>
                <field id="speed-field" class="huge padded" :value="speed" :read-only="readOnly" @update-value="updateVitals('speed', $event)"></field>
            </div>
            <div class="box box-lite">
                <span class="centered label">Death saves</span>
                <div class="row">
                    <span class="mini-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M557 152.9L538.2 178.8L282.2 530.8L260.2 561.1C259.5 560.4 208 508.9 105.7 406.6L83 384L128.3 338.7C130.2 340.6 171.6 382 252.4 462.8L486.4 141.1L505.2 115.2L557 152.8z"/></svg>
                    </span>

                        <input
                        type="checkbox"
                        v-for="(save, i) in deathSaves.successes"
                        :checked="save"
                        :disabled="readOnly"
                        @input="updateDeathSaves('successes', i, !save)">
                </div>
                <div class="row">
                    <span class="mini-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M432 467.4L432 528L392 528L392 480L344 480L344 528L296 528L296 480L248 480L248 528L208 528L208 467.4L188.8 453C141.3 417.2 112 363.4 112 304C112 200.8 202.2 112 320 112C437.8 112 528 200.8 528 304C528 363.4 498.7 417.2 451.2 453L432 467.4zM480 491.4C538.5 447.4 576 379.8 576 304C576 171.5 461.4 64 320 64C178.6 64 64 171.5 64 304C64 379.8 101.5 447.4 160 491.4L160 576L480 576L480 491.4zM288 320C288 289.1 262.9 264 232 264C201.1 264 176 289.1 176 320C176 350.9 201.1 376 232 376C262.9 376 288 350.9 288 320zM408 376C438.9 376 464 350.9 464 320C464 289.1 438.9 264 408 264C377.1 264 352 289.1 352 320C352 350.9 377.1 376 408 376z"/></svg>
                    </span>
                    <input
                        type="checkbox"
                        v-for="(save, i) in deathSaves.failures"
                        :checked="save"
                        :disabled="readOnly"
                        @input="updateDeathSaves('failures', i, !save)">
                </div>
            </div>
        </div>

        <div class="row mt-sm" style="justify-content: center; flex-wrap: wrap;">
            <span class="no-break mb-xs mr-sm">
                <label for="characterConditions" class="meta small muted">Conditions</label>
                <field
                    id="characterConditions"
                    align="left"
                    :value="conditions"
                    :read-only="readOnly"
                    @update-value="updateVitals('conditions', $event)"
                    placeholder="None"
                ></field>
            </span>

            <span class="no-break mb-xs">
                <label for="characterConcentration" class="meta small muted">Concentration</label>
                <field
                    id="characterConcentration"
                    align="left"
                    :value="concentration"
                    :read-only="readOnly"
                    @update-value="updateVitals('concentration', $event)"
                    placeholder="None"
                ></field>
            </span>
        </div>
    </div>
</template>

<script>
import Field from './Field';
import { mapState } from 'vuex';

export default {
    name: 'Vitals',

    computed: {
        ...mapState([
            'hp',
            'maxHp',
            'tempHp',
            'hitDie',
            'totalHitDie',
            'ac',
            'speed',
            'deathSaves',
            'conditions',
            'concentration',
            'readOnly'
        ])
    },

    methods: {
        update(item, e) {
            var value = e.target.innerText;
            this[item] = value;
        },

        updateVitals(field, val) {
            this.$store.commit('updateVitals', { field, val });
        },

        updateDeathSaves(key, i, val) {
            this.$store.commit('updateDeathSaves', { key, i, val });
        }
    },

    components: {
        'field': Field
    }
}
</script>
