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
            <span class="centered label">Death saves</span>
            <div class="row">
                <span>&#9745;</span>
                <input type="checkbox">
                <input type="checkbox">
                <input type="checkbox">
            </div>
            <div class="row">
                <span>&#9746;</span>
                <input type="checkbox">
                <input type="checkbox">
                <input type="checkbox">
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
        ...mapState(['hp', 'maxHp', 'tempHp', 'hitDie', 'totalHitDie', 'ac'])
    },

    methods: {
        update(item, e) {
            var value = e.target.innerText;
            this[item] = value;
        },

        updateVitals(field, val) {
            this.$store.commit('updateVitals', { field: val });
        }
    },

    components: {
        'field': Field
    }
}
</script>
