<template>
  <div>
    <div class="box box-lite row row-vert-centered mb-sm">
      <span class="slot-label reverse">{{ level }}</span>
      <div class="spell-slots">
        <span class="label label-inline">Slots:</span>
        <field
          :value="totalSlots"
          type="number"
          class="spell-slots-total"
          min="0"
          :read-only="readOnly"
          @update-value="updateSlots($event)"
        ></field>
        <span class="label label-inline">Expended:</span>
        <field
          :value="expendedSlots"
          type="number"
          class="spell-slots-expended"
          min="0"
          :max="totalSlots"
          :read-only="readOnly"
          @update-value="updateExpended($event)"
        ></field>
      </div>
      <button-collapse
        :collapsed="spellsCollapsed"
        @click="updateSpellsCollapsed()"
        :collapse-title="`Collapse all level ${level} spells`"
        :expand-title="`Expand all level ${level} spells`"
        v-if="!readOnly"
      ></button-collapse>
    </div>
    <spell-list :list-field="listField" :read-only="readOnly"></spell-list>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import SpellList from './SpellList';
import ButtonCollapse from './ButtonCollapse';

export default {
  name: 'SpellGroup',

  props: ['level'],

  data() {
    return {
      spellsCollapsed: false,
    };
  },

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
    },
  },

  methods: {
    updateSlots(val) {
      this.$store.commit('updateSpellSlots', {
        field: this.listField,
        val: parseInt(val),
      });
    },

    updateExpended(val) {
      this.$store.commit('updateExpendedSlots', {
        field: this.listField,
        val: parseInt(val),
      });
    },

    updateSpellsCollapsed() {
      this.spellsCollapsed = !this.spellsCollapsed;

      const spells = this.$store.state[this.listField].spells;
      spells.forEach((_, i) => {
        this.$store.commit('updateSpellCollapsed', {
          field: this.listField,
          i,
          collapsed: this.spellsCollapsed,
        });
      });
    },
  },

  components: {
    'spell-list': SpellList,
    field: Field,
    'button-collapse': ButtonCollapse,
  },
};
</script>
