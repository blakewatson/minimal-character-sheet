<template>
    <section>
        <h1 class="label centered">Spellcasting</h1>
        <div class="row vert-after">
            <div class="box box-lite">
                <span class="label centered">Class</span>
                <field class="centered block" :value="spClass" placeholder="Druid" @update-value="spClass = $event"></field>
            </div>
            <div class="box box-lite">
                <span class="label centered">Ability</span>
                <select v-model="spAbility">
                    <option v-for="a in abilities" :value="a.name">{{ a.name }}</option>
                </select>
            </div>
            <div class="box">
                <span class="label centered reverse">Spell Save DC</span>
                <field class="centered block padded huge" :value="spSave" @update-value="spSave = $event"></field>
            </div>
            <div class="box">
                <span class="label centered reverse">Attack Bonus</span>
                <field class="centered block padded huge" :value="spAttack" @update-value="spAttack = $event"></field>
            </div>
        </div>

        <div>
            <div class="box box-lite">
                <span class="label float-left reverse">0</span>
                <span class="label centered">Cantrips</span>
            </div>
            <list></list>
        </div>
    </section>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import List from './List';

export default {
    name: 'Spells',

    data() {
        return {
            spClass: '',
            spAbility: 'WIS',
            spSave: '',
            spAttack: ''
        };
    },

    computed: {
        ...mapState(['abilities'])
    },

    components: {
        'field': Field,
        'list': List
    }
}
</script>
