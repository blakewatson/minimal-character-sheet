<template>
    <section>
        <h1 class="label centered">Spellcasting</h1>
        <div class="row vert-after">
            <div class="box box-lite">
                <span class="label centered">Class</span>
                <field class="centered block" :value="spClass" placeholder="Druid" :read-only="readOnly" @update-value="updateSpellInfo('spClass', $event)"></field>
            </div>
            <div class="box box-lite">
                <span class="label centered">Ability</span>
                <select v-if="!readOnly" @input="updateSpellInfo('spAbility', $event.target.value)">
                    <option v-for="a in abilities" :value="a.name" :selected="spAbility === a.name">{{ a.name }}</option>
                </select>
                <span v-else>{{ spAbility }}</span>
            </div>
            <div class="box">
                <span class="label centered reverse">Spell Save DC</span>
                <field class="centered block padded huge" :value="spSave" :read-only="readOnly" @update-value="updateSpellInfo('spSave', $event)"></field>
            </div>
            <div class="box">
                <span class="label centered reverse">Attack Bonus</span>
                <field class="centered block padded huge" :value="spAttack" :read-only="readOnly" @update-value="updateSpellInfo('spAttack', $event)"></field>
            </div>
        </div>

        <div>
            <div class="box box-lite">
                <span class="label float-left reverse">0</span>
                <span class="label centered">Cantrips</span>
            </div>
            <list list-field="cantripsList" :read-only="readOnly"></list>
        </div>

        <spell-group level="1"></spell-group>
        <spell-group level="2"></spell-group>
        <spell-group level="3"></spell-group>
        <spell-group level="4"></spell-group>
        <spell-group level="5"></spell-group>
        <spell-group level="6"></spell-group>
        <spell-group level="7"></spell-group>
        <spell-group level="8"></spell-group>
        <spell-group level="9"></spell-group>
    </section>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import List from './List';
import SpellGroup from './SpellGroup';

export default {
    name: 'Spells',

    computed: {
        ...mapState(['abilities', 'spClass', 'spAbility', 'spSave', 'spAttack', 'readOnly'])
    },

    methods: {
        updateSpellInfo(field, val) {
            this.$store.commit( 'updateSpellInfo', { field, val } );
        },
    },

    components: {
        'field': Field,
        'list': List,
        'spell-group': SpellGroup
    }
}
</script>
