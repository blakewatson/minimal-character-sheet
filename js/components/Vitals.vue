<template>
    <div class="row">
        <div class="box reverse">
            <span class="centered label">AC</span>
            <field classNames="huge block padded" :value="ac" :read-only="readOnly" @update-value="updateVitals('ac', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">HP</span>
            <field class="huge padded" :value="hp" :read-only="readOnly" @update-value="updateVitals('hp', $event)"></field>/<field class="normal strong" :value="maxHp" :read-only="readOnly" @update-value="updateVitals('maxHp', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Temp HP</span>
            <field class="centered huge block padded" :value="tempHp" :read-only="readOnly" @update-value="updateVitals('tempHp', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Hit die</span>
            <field class="huge padded" :value="hitDie" :read-only="readOnly" @update-value="updateVitals('hitDie', $event)"></field>/<field class="normal" :value="totalHitDie" :read-only="readOnly" @update-value="updateVitals('totalHitDie', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Speed</span>
            <field class="huge padded" :value="speed" :read-only="readOnly" @update-value="updateVitals('speed', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Death saves</span>
            <div class="row">
                <span class="mini-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M173.898 439.404l-166.4-166.4c-9.997-9.997-9.997-26.206 0-36.204l36.203-36.204c9.997-9.998 26.207-9.998 36.204 0L192 312.69 432.095 72.596c9.997-9.997 26.207-9.997 36.204 0l36.203 36.204c9.997 9.997 9.997 26.206 0 36.204l-294.4 294.401c-9.998 9.997-26.207 9.997-36.204-.001z"/></svg>
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
                    <svg version="1.1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M344,200c-30.9,0 -56,25.1 -56,56c0,30.9 25.1,56 56,56c30.9,0 56,-25.1 56,-56c0,-30.9 -25.1,-56 -56,-56Zm-176,0c-30.9,0 -56,25.1 -56,56c0,30.9 25.1,56 56,56c30.9,0 56,-25.1 56,-56c0,-30.9 -25.1,-56 -56,-56Zm88,-200c-141.4,0 -256,100.3 -256,224c0,70.1 36.9,132.6 94.5,173.7c9.7,6.9 15.2,18.1 13.5,29.9l-6.8,47.9c-2.7,19.3 12.2,36.5 31.7,36.5h246.3c19.5,0 34.4,-17.2 31.7,-36.5l-6.8,-47.9c-1.7,-11.7 3.8,-23 13.5,-29.9c57.5,-41.1 94.4,-103.6 94.4,-173.7c0,-123.7 -114.6,-224 -256,-224Zm133.7,358.6c-24.6,17.5 -37.3,46.5 -33.2,75.7l4.2,29.7h-40.7v-40c0,-4.4 -3.6,-8 -8,-8h-16c-4.4,0 -8,3.6 -8,8v40h-64v-40c0,-4.4 -3.6,-8 -8,-8h-16c-4.4,0 -8,3.6 -8,8v40h-40.7l4.2,-29.7c4.1,-29.2 -8.6,-58.2 -33.2,-75.7c-47.2,-33.7 -74.3,-82.7 -74.3,-134.6c0,-97 93.3,-176 208,-176c114.7,0 208,79 208,176c0,51.9 -27.1,100.9 -74.3,134.6Z"></path></svg>
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
            console.log(key, i, val);
            this.$store.commit('updateDeathSaves', { key, i, val });
        }
    },

    components: {
        'field': Field
    }
}
</script>
