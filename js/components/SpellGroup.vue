<template>
    <div>
        <div class="box box-lite clearfix">
            <span class="label float-left reverse">{{ level }}</span>
            <div class="spell-slots">
                <span class="label label-inline">Slots:</span>
                <field :value="totalSlots" type="number" class="spell-slots-total" min="0" :read-only="readOnly" @update-value="updateSlots($event)"></field>
                <span class="label label-inline">Expended:</span>
                <field :value="expendedSlots" type="number" class="spell-slots-expended" min="0" :max="totalSlots" :read-only="readOnly" @update-value="updateExpended($event)"></field>
            </div>
        </div>
        <spell-list :list-field="listField" :read-only="readOnly"></spell-list>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import SpellList from './SpellList';
import Field from './Field';

export default {
    name: 'SpellGroup',

    props: ['level'],

    computed: {
        ...mapState(['readOnly']),
        
        totalSlots() {
            return this.$store.state[this.listField].slots;
        },

        expendedSlots() {
            return this.$store.state[this.listField].expended;
        },

        listField() {
            return `lvl${this.level}Spells`;
        }
    },

    methods: {
        updateSlots(val) {
            this.$store.commit('updateSpellSlots', {
                field: this.listField,
                val: parseInt(val)
            });
        },

        updateExpended(val) {
            this.$store.commit('updateExpendedSlots', {
                field: this.listField,
                val: parseInt(val)
            });
        }
    },

    components: {
        'spell-list': SpellList,
        'field': Field
    }
}
</script>