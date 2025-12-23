<template>
  <div>
    <div
      class="border-light-foreground dark:border-dark-foreground mb-2 flex items-center justify-between border-t"
    >
      <span class="text-reverse bg-reverse px-2 text-xl">{{ level }}</span>

      <div class="flex items-baseline gap-1">
        <span class="small-label min-[500px]:text-sm">Slots:</span>
        <field
          :read-only="readOnly"
          :value="totalSlots"
          @update-value="updateSlots($event)"
          class="mr-1.5 text-sm! font-bold sm:mr-4"
          min="0"
          type="number"
        ></field>

        <span class="small-label min-[500px]:text-sm">Expended:</span>
        <field
          :max="totalSlots"
          :read-only="readOnly"
          :value="expendedSlots"
          @update-value="updateExpended($event)"
          class="text-sm!"
          min="0"
          type="number"
        ></field>
      </div>

      <button-collapse
        :collapse-title="`Collapse all level ${level} spells`"
        :collapsed="!shouldCollapseAll"
        :expand-title="`Expand all level ${level} spells`"
        @click="updateSpellsCollapsed()"
        class="mt-1"
        v-if="!readOnly"
      ></button-collapse>
    </div>

    <spell-list :list-field="listField" :read-only="readOnly"></spell-list>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import ButtonCollapse from './ButtonCollapse';
import Field from './Field';
import SpellList from './SpellList';

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
    },

    shouldCollapseAll() {
      return this.$store.state[this.listField].spells.some(
        (spell) => !spell.collapsed,
      );
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
      const newState = this.shouldCollapseAll;

      const spells = this.$store.state[this.listField].spells;
      spells.forEach((_, i) => {
        this.$store.commit('updateSpellCollapsed', {
          field: this.listField,
          i,
          collapsed: newState,
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
