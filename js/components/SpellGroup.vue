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

      <div class="mt-1 flex items-center gap-1">
        <button
          :title="$t('Move prepared spells to top')"
          @click="movePreparedSpellsToTop"
          class="button-icon"
          v-if="!readOnly"
        >
          <i class="fa-sharp fa-regular fa-arrow-up-from-line"></i>
        </button>

        <button-collapse
          :collapse-title="
            $t('Collapse all level {level} spells').replace('{level}', level)
          "
          :collapsed="!shouldCollapseAll"
          :expand-title="
            $t('Expand all level {level} spells').replace('{level}', level)
          "
          @click="updateSpellsCollapsed()"
          v-if="!readOnly"
        ></button-collapse>
      </div>
    </div>

    <spell-list :spell-group="spellGroup" :read-only="readOnly"></spell-list>
  </div>
</template>

<script>
import { state } from '../store';
import ButtonCollapse from './ButtonCollapse.vue';
import Field from './Field.vue';
import SpellList from './SpellList.vue';

/** @typedef {import('../store').SpellGroupKey} SpellGroupKey */

export default {
  name: 'SpellGroup',

  props: {
    spellGroup: /** @type {import('vue').PropType<SpellGroupKey>} */ (String),
  },

  computed: {
    readOnly() {
      return state.readOnly;
    },

    totalSlots() {
      if (!this.spellGroup) {
        return 0;
      }
      return state[this.spellGroup].slots;
    },

    expendedSlots() {
      if (!this.spellGroup) {
        return 0;
      }
      return state[this.spellGroup].expended;
    },

    shouldCollapseAll() {
      if (!this.spellGroup) {
        return false;
      }
      return state[this.spellGroup].spells.some((spell) => !spell.collapsed);
    },

    level() {
      if (!this.spellGroup) {
        return '';
      }
      return this.spellGroup.substring(3, 4);
    },
  },

  methods: {
    movePreparedSpellsToTop() {
      if (!this.spellGroup) {
        return;
      }

      const spells = state[this.spellGroup].spells;
      const preparedSpells = spells.filter((spell) => spell.prepared);
      const unpreparedSpells = spells.filter((spell) => !spell.prepared);

      if (preparedSpells.length === 0 || unpreparedSpells.length === 0) {
        return;
      }

      const newOrder = [...preparedSpells, ...unpreparedSpells];

      state[this.spellGroup].spells = newOrder;
    },

    /** @param {number} val */
    updateSlots(val) {
      if (!this.spellGroup) {
        return;
      }
      state[this.spellGroup].slots = val;
    },

    /** @param {number} val  */
    updateExpended(val) {
      if (!this.spellGroup) {
        return;
      }
      state[this.spellGroup].expended = val;
    },

    updateSpellsCollapsed() {
      if (!this.spellGroup) {
        return;
      }

      state[this.spellGroup].spells = state[this.spellGroup].spells.map(
        (spell) => ({
          ...spell,
          collapsed: this.shouldCollapseAll,
        }),
      );
    },
  },

  components: {
    'spell-list': SpellList,
    field: Field,
    'button-collapse': ButtonCollapse,
  },
};
</script>
