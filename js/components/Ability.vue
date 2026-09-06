<template>
  <div
    class="border-light-foreground dark:border-dark-foreground flex min-w-16 flex-col items-center rounded-xs border"
  >
    <div
      class="text-reverse bg-reverse mb-1 self-stretch py-0.5 text-center text-sm"
    >
      {{ $t(ability.name) }}
    </div>

    <span class="block text-center text-xl">{{
      $signedNumString(modifier)
    }}</span>

    <field
      class="text-center font-bold"
      :value="ability.score"
      :read-only="readOnly"
      @update-value="updateScore"
      type="number"
    ></field>

    <div
      class="border-light-foreground dark:border-dark-foreground mt-2 flex gap-2 border-t px-1 pt-2"
    >
      <label
        :for="inputId"
        class="small-label"
        style="display: flex; align-items: center; gap: 0.125em"
      >
        {{ $t('Save (throw)') }}
      </label>
      <input
        v-if="savingThrow"
        type="checkbox"
        :id="inputId"
        :checked="savingThrow.proficient"
        :disabled="readOnly"
        @change="toggleProficiency"
      />
    </div>
    <button
      :class="{
        underline:
          savingThrow?.modifierOverride !== null &&
          savingThrow?.modifierOverride !== undefined,
      }"
      :disabled="readOnly"
      @click="openSavingThrowOverrideDialog"
      class="hover:border-light-foreground my-1 rounded-xs border border-transparent px-2 text-center decoration-2 dark:hover:border-neutral-400"
      type="button"
    >
      {{ $signedNumString(saveBonus) }}
    </button>
  </div>

  <app-dialog
    :close-label="$t('Cancel')"
    :title="$t('Saving throw bonus override')"
    @close="showSavingThrowOverrideDialog = false"
    @submit="updateSavingThrowOverride"
    v-if="showSavingThrowOverrideDialog"
  >
    <template #content>
      <p class="mb-2 text-sm">
        {{ $t('Saving throw bonus override description') }}
      </p>

      <label class="small-label mr-4 text-base" for="skill-modifier">{{
        ability.name ? $t(ability.name) : ''
      }}</label>

      <field
        :readOnly="readOnly"
        :value="savingThrowModifierOverride"
        @update-value="savingThrowModifierOverride = $event"
        class="min-w-14 text-center text-lg!"
        has-bg
        id="skill-modifier"
        style="min-width: 50px"
        type="number"
      ></field>

      <label class="mt-2 flex items-center gap-2">
        <input type="checkbox" v-model="savingThrowOverrideIsAdditive" />
        <span class="text-sm">Add to standard calculation</span>
      </label>
    </template>

    <template #actions>
      <button
        :disabled="shouldDisableSave"
        class="button-primary"
        type="submit"
      >
        {{ $t('Save') }}
      </button>
      <button
        :disabled="readOnly"
        @click="removeSavingThrowOverride"
        class="button"
        type="button"
      >
        {{ $t('Remove override') }}
      </button>
    </template>
  </app-dialog>
</template>

<script>
import { state, proficiencyBonus as storeProficiencyBonus } from '../store';
import { isNullOrUndefined } from '../utils';
import AppDialog from './AppDialog.vue';
import Field from './Field.vue';

/** @typedef {import('../store').SavingThrow} SavingThrow */
/** @typedef {import('../store').Ability} Ability */
/** @typedef {import('../store').Modifier} Modifier */

export default {
  name: 'Ability',

  props: {
    ability: {
      type: /** @type {import('vue').PropType<Ability>} */ (Object),
      required: true,
    },

    modifier: {
      type: /** @type {import('vue').PropType<number>} */ (Number),
      required: true,
    },
  },

  data() {
    return {
      savingThrowOverrideIsAdditive: false,
      /** @type {number | null} */
      savingThrowModifierOverride: null,
      showSavingThrowOverrideDialog: false,
    };
  },

  computed: {
    inputId() {
      return `${this.ability.name}-saving-throw`;
    },

    proficiencyBonus() {
      return storeProficiencyBonus.value;
    },

    readOnly() {
      return state.readOnly;
    },

    savingThrows() {
      return state.savingThrows;
    },

    savingThrow() {
      return this.savingThrows.find((st) => st.name === this.ability.name);
    },

    saveBonus() {
      if (!this.savingThrow) {
        return 0;
      }

      const bonus = this.savingThrow.proficient
        ? this.modifier + this.proficiencyBonus
        : this.modifier;

      if (
        this.savingThrow.modifierOverride !== null &&
        this.savingThrow.modifierOverride !== undefined
      ) {
        return this.savingThrow.isAdditive
          ? this.savingThrow.modifierOverride + bonus
          : this.savingThrow.modifierOverride;
      }

      return bonus;
    },

    shouldDisableSave() {
      return (
        this.readOnly || isNullOrUndefined(this.savingThrowModifierOverride)
      );
    },
  },

  methods: {
    openSavingThrowOverrideDialog() {
      if (!this.savingThrow) {
        return;
      }

      this.savingThrowModifierOverride = this.savingThrow.isAdditive
        ? this.savingThrow.modifierOverride || 0
        : this.saveBonus;

      this.savingThrowOverrideIsAdditive = this.savingThrow.isAdditive;
      this.showSavingThrowOverrideDialog = true;
    },

    removeSavingThrowOverride() {
      this.savingThrowModifierOverride = null;
      this.savingThrowOverrideIsAdditive = false;
      this.updateSavingThrowOverride();
    },

    updateSavingThrowOverride() {
      state.savingThrows = state.savingThrows.map((savingThrow) => {
        if (savingThrow.name === this.savingThrow?.name) {
          return {
            ...savingThrow,
            modifierOverride: this.savingThrowModifierOverride,
            isAdditive: this.savingThrowOverrideIsAdditive,
          };
        }

        return savingThrow;
      });

      this.showSavingThrowOverrideDialog = false;
    },

    toggleProficiency() {
      if (!this.savingThrow) {
        return;
      }

      state.savingThrows = state.savingThrows.map((savingThrow) => {
        if (savingThrow.name === this.savingThrow?.name) {
          return {
            ...savingThrow,
            proficient: !this.savingThrow.proficient,
          };
        }
        return savingThrow;
      });
    },

    /** @param {number} score */
    updateScore(score) {
      state.abilities = state.abilities.map((ability) => {
        if (ability.name === this.ability.name) {
          return {
            ...ability,
            score,
          };
        }
        return ability;
      });
    },
  },

  mounted() {
    if (this.savingThrow) {
      this.savingThrowOverrideIsAdditive = this.savingThrow.isAdditive;
    }
  },

  components: {
    'app-dialog': AppDialog,
    field: Field,
  },
};
</script>
