<template>
  <section
    class="border-light-foreground dark:border-dark-foreground border-t pt-4"
  >
    <h1 class="section-label pt-0 font-bold">{{ $t('Spellcasting') }}</h1>

    <div
      class="grid grid-cols-2 items-start justify-between gap-2 sm:flex sm:flex-wrap"
    >
      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <span class="text-center text-sm">{{ $t('Class') }}</span>
        <field
          :read-only="readOnly"
          :value="spClass"
          @update-value="updateSpellInfo('spClass', $event)"
          class="text-center text-[13px]! min-[500px]:text-sm!"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <span class="text-center text-sm">{{ $t('Ability') }}</span>
        <select
          v-if="!readOnly"
          @input="updateSpellAbility"
          class="hover:text-light-accent focus:text-light-accent dark:hover:text-dark-accent dark:focus:text-dark-accent outline-light-accent dark:outline-dark-accent dark:focus:outline-dark-accent rounded-xs border border-transparent px-1 py-0.5 text-center text-sm hover:bg-neutral-100 focus:bg-neutral-100 dark:hover:border-neutral-700 dark:hover:bg-black dark:focus:bg-black dark:focus:outline-2"
        >
          <option
            v-for="(a, idx) in abilities"
            :selected="spAbility === a.name"
            :value="a.name"
          >
            {{ $t(a.name) }}: {{ $signedNumString(modifiers[idx].val) }}
          </option>
        </select>
        <div class="block" style="padding: 0.25em" v-else>
          {{ $t(spAbility) }}
        </div>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <label class="text-center text-sm" for="spell-save-dc">{{
          $t('Spell Save DC')
        }}</label>
        <field
          :read-only="readOnly"
          :value="spSave"
          @update-value="updateSpellInfo('spSave', $event)"
          class="text-center sm:text-2xl"
          id="spell-save-dc"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t"
      >
        <label class="text-center text-sm" for="spell-attack-bonus">{{
          $t('Attack Bonus')
        }}</label>
        <field
          :read-only="readOnly"
          :value="spAttack"
          @update-value="updateSpellInfo('spAttack', $event)"
          class="text-center sm:text-2xl"
          id="spell-attack-bonus"
        ></field>
      </div>
    </div>

    <div class="my-6">
      <div
        class="border-light-foreground dark:border-dark-foreground relative mb-3 flex items-center justify-center border-t"
      >
        <span
          class="text-reverse bg-reverse absolute top-0 left-0 rounded-b-xs px-2 text-xl"
        >
          <div class="sr-only">{{ $t('Level') }}</div>
          0
        </span>

        <span
          class="mx-auto mt-1 grow text-center text-sm tracking-wider uppercase"
          >{{ $t('Cantrips') }}</span
        >

        <button-collapse
          :collapse-title="$t('Collapse all cantrips')"
          :collapsed="!shouldCollapseAll"
          :expand-title="$t('Expand all cantrips')"
          @click="updateCantripsCollapsed()"
          class="absolute top-1 right-0"
          v-if="!readOnly"
        ></button-collapse>
      </div>
      <list list-field="cantripsList" :read-only="readOnly"></list>
    </div>

    <spell-group spell-group="lvl1Spells"></spell-group>
    <spell-group spell-group="lvl2Spells"></spell-group>
    <spell-group spell-group="lvl3Spells"></spell-group>
    <spell-group spell-group="lvl4Spells"></spell-group>
    <spell-group spell-group="lvl5Spells"></spell-group>
    <spell-group spell-group="lvl6Spells"></spell-group>
    <spell-group spell-group="lvl7Spells"></spell-group>
    <spell-group spell-group="lvl8Spells"></spell-group>
    <spell-group spell-group="lvl9Spells"></spell-group>
  </section>
</template>

<script>
import { state, modifiers as storeModifiers } from '../store';
import ButtonCollapse from './ButtonCollapse.vue';
import Field from './Field.vue';
import List from './List.vue';
import SpellGroup from './SpellGroup.vue';

/** @typedef {import('../store').AbilityName} AbilityName */

export default {
  name: 'Spells',

  computed: {
    abilities() {
      return state.abilities;
    },
    className() {
      return state.className;
    },
    spClass() {
      return state.spClass;
    },
    spAbility() {
      return state.spAbility;
    },
    spSave() {
      return state.spSave;
    },
    spAttack() {
      return state.spAttack;
    },
    cantripsList() {
      return state.cantripsList;
    },
    readOnly() {
      return state.readOnly;
    },
    modifiers() {
      return storeModifiers.value;
    },

    // button should collapse all cantrips if any are expanded
    shouldCollapseAll() {
      return this.cantripsList.some((cantrip) => !cantrip.collapsed);
    },
  },

  methods: {
    /** @param {InputEvent} event */
    updateSpellAbility(event) {
      const val = /** @type {HTMLSelectElement} */ (event.target).value;

      if (
        val === 'STR' ||
        val === 'DEX' ||
        val === 'CON' ||
        val === 'INT' ||
        val === 'WIS' ||
        val === 'CHA'
      ) {
        state.spAbility = val;
        return;
      }
    },

    /**
     * @param {'spAttack' | 'spClass' | 'spSave'} field
     * @param {string} val
     */
    updateSpellInfo(field, val) {
      state[field] = val;
    },

    updateCantripsCollapsed() {
      console.log('update collapsed');
      state.cantripsList = state.cantripsList.map((item) => ({
        ...item,
        collapsed: this.shouldCollapseAll,
      }));
    },
  },

  mounted() {
    if (this.readOnly) {
      return;
    }

    if (this.spClass === '') {
      this.updateSpellInfo('spClass', this.className);
    }
  },

  components: {
    field: Field,
    list: List,
    'spell-group': SpellGroup,
    'button-collapse': ButtonCollapse,
  },
};
</script>
