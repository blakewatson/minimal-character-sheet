<template>
    <div class="row">
        <div class="box reverse">
            <span class="centered label">AC</span>
            <field classNames="huge block padded" :value="ac" @update-value="updateVitals('ac', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">HP</span>
            <field class="huge padded" :value="hp" @update-value="updateVitals('hp', $event)"></field>/<field class="normal strong" :value="maxHp" @update-value="updateVitals('maxHp', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Temp HP</span>
            <field class="centered huge block padded" :value="tempHp" @update-value="updateVitals('tempHp', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Hit die</span>
            <field class="huge padded" :value="hitDie" @update-value="updateVitals('hitDie', $event)"></field>/<field class="normal" :value="totalHitDie" @update-value="updateVitals('totalHitDie', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Speed</span>
            <field class="huge padded" :value="speed" @update-value="updateVitals('speed', $event)"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Death saves</span>
            <div class="row">
                <span class="mini-icon"><img src="/images/check.svg" alt="Success"></span>
                <input
                    type="checkbox"
                    v-for="(save, i) in deathSaves.successes"
                    :checked="save"
                    @input="updateDeathSaves('successes', i, !save)">
            </div>
            <div class="row">
                <span class="mini-icon"><img src="/images/death.svg" alt="Failure"></span>
                <input
                    type="checkbox"
                    v-for="(save, i) in deathSaves.failures"
                    :checked="save"
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
            'deathSaves'
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
