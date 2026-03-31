<template>
  <div>
    <div
      class="border-light-foreground dark:border-dark-foreground mb-2 flex items-center justify-between border-t"
    >
      <span class="text-reverse bg-reverse px-2 text-xl">{{ level }}</span>

      <div class="flex items-baseline gap-1">
        <span class="small-label min-[500px]:text-sm">{{ $t('Slots') }}:</span>
        <field
          :read-only="readOnly"
          :value="totalSlots"
          @update-value="updateSlots($event)"
          class="mr-1.5 text-sm! font-bold sm:mr-4"
          min="0"
          type="number"
        ></field>

        <span class="small-label min-[500px]:text-sm"
          >{{ $t('Expended') }}:</span
        >
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
        :collapse-title="
          $t('Collapse all level {level} spells').replace('{level}', level)
        "
        :collapsed="!shouldCollapseAll"
        :expand-title="
          $t('Expand all level {level} spells').replace('{level}', level)
        "
        @click="updateSpellsCollapsed()"
        class="mt-1"
        v-if="!readOnly"
      ></button-collapse>
    </div>

    <spell-list :list-field="listField" :read-only="readOnly"></spell-list>
  </div>
</template>

<script>
import {
  state,
  updateSpellSlots,
  updateExpendedSlots,
  updateSpellCollapsed,
} from '../store';
import ButtonCollapse from './ButtonCollapse.vue';
import Field from './Field.vue';
import SpellList from './SpellList.vue';

export default {
  name: 'SpellGroup',

  props: ['level'],

  computed: {
    readOnly() {
      return state.readOnly;
    },

    totalSlots() {
      return state[this.listField].slots;
    },

    expendedSlots() {
      return state[this.listField].expended;
    },

    listField() {
      return `lvl${this.level}Spells`;
    },

    shouldCollapseAll() {
      return state[this.listField].spells.some((spell) => !spell.collapsed);
    },
  },

  methods: {
    updateSlots(val) {
      updateSpellSlots({
        field: this.listField,
        val: parseInt(val),
      });
    },

    updateExpended(val) {
      updateExpendedSlots({
        field: this.listField,
        val: parseInt(val),
      });
    },

    updateSpellsCollapsed() {
      const newState = this.shouldCollapseAll;

      const spells = state[this.listField].spells;
      spells.forEach((_, i) => {
        updateSpellCollapsed({
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
