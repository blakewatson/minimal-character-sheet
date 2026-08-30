<template>
  <section class="pb-2">
    <div class="mb-4">
      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterName" class="small-label">{{ $t('Name') }}</label>
        <field
          :placeholder="$t('Name')"
          :read-only="readOnly"
          :value="characterName"
          @update-value="updateBio('characterName', $event)"
          align="left"
          class-names="mr-sm"
          id="characterName"
        ></field>
      </span>

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterBackground" class="small-label">{{
          $t('Background')
        }}</label>
        <field
          :placeholder="$t('Background')"
          :read-only="readOnly"
          :value="background"
          @update-value="updateBio('background', $event)"
          align="left"
          class-names="mr-sm"
          id="characterBackground"
        ></field>
      </span>

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterRace" class="small-label">
          {{ $t(is_2024 ? 'Species' : 'Race') }}
        </label>
        <field
          :placeholder="$t(is_2024 ? 'Species' : 'Race')"
          :read-only="readOnly"
          :value="race"
          @update-value="updateBio('race', $event)"
          align="left"
          id="characterRace"
        ></field>
      </span>

      <br class="hidden sm:block" />

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterClass" class="small-label">{{
          $t('Class')
        }}</label>
        <field
          :placeholder="$t('Class')"
          :read-only="readOnly"
          :value="className"
          @update-value="updateBio('className', $event)"
          align="left"
          class-names="mr-sm"
          id="characterClass"
        ></field>
      </span>

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterLevel" class="small-label">{{
          $t('Level')
        }}</label>
        <field
          :placeholder="$t('Level')"
          :read-only="readOnly"
          :value="level"
          @update-value="updateLevel"
          class-names="mr-sm"
          id="characterLevel"
          min="1"
          type="number"
        ></field>
      </span>

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterXp" class="small-label">{{ $t('XP') }}</label>
        <field
          :placeholder="$t('XP')"
          :read-only="readOnly"
          :value="xp"
          @update-value="updateBio('xp', $event)"
          class-names="mr-sm"
          id="characterXp"
          type="number"
        ></field>
      </span>

      <span class="mr-2 mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterAlignment" class="small-label">{{
          $t('Alignment')
        }}</label>
        <field
          :placeholder="$t('Alignment')"
          :read-only="readOnly"
          :value="alignment"
          @update-value="updateBio('alignment', $event)"
          align="left"
          id="characterAlignment"
        ></field>
      </span>
    </div>

    <vitals></vitals>
  </section>
</template>

<script lang="js">
// @ts-check

import { state } from '../store';
import Field from './Field.vue';
import Vitals from './Vitals.vue';

/** @typedef {import('../store').AppState} AppState */

/**
 * @typedef {(
 *   'characterName' |
 *   'race' |
 *   'background' |
 *   'className' |
 *   'xp' |
 *   'alignment'
 * )} BioField
 */

export default {
  name: 'Bio',

  computed: {
    is_2024() {
      return state.is_2024;
    },
    level() {
      return state.level;
    },
    characterName() {
      return state.characterName;
    },
    className() {
      return state.className;
    },
    race() {
      return state.race;
    },
    background() {
      return state.background;
    },
    alignment() {
      return state.alignment;
    },
    xp() {
      return state.xp;
    },
    readOnly() {
      return state.readOnly;
    },
  },

  methods: {
    /** @param {string | number} level */
    updateLevel(level) {
      state.level = typeof level === 'number' ? level : parseInt(level);
    },

    /**
     * @param {BioField} field
     * @param {string | number} val
     */
    updateBio(field, val) {
      if (field === 'xp') {
        if (typeof val !== 'number') {
          return;
        }

        state.xp = val;
        return;
      }

      if (typeof val !== 'string') {
        return;
      }

      state[field] = val;
    },
  },

  components: {
    field: Field,
    vitals: Vitals,
  },
};
</script>
