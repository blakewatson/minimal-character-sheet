<template>
    <div class="row">
        <div class="box reverse">
            <span class="centered label">AC</span>
            <field classNames="huge block padded" :value="ac" @update-value="ac = $event"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">HP</span>
            <field class="huge padded" :value="hp" @update-value="hp = $event"></field>/<field class="normal strong" :value="maxHp" @update-value="maxHp = $event"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Temp HP</span>
            <field class="centered huge block padded" :value="tempHp" @update-value="tempHp = $event"></field>
        </div>
        <div class="box box-lite">
            <span class="centered label">Hit die</span>
            <field class="huge padded" :value="hitDie" @update-value="hitDie = $event"></field>/<field class="normal" :value="totalHitDie" @update-value="totalHitDie = $event"></field>
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

export default {
    name: 'Vitals',

    data() {
        return {
            hp: 38,
            maxHp: 38,
            tempHp: 0,
            hitDie: '5d8',
            totalHitDie: 5,
            ac: 12,
        };
    },

    methods: {
        update(item, e) {
            var value = e.target.innerText;
            this[item] = value;
        }
    },

    components: {
        'field': Field
    }
}
</script>
