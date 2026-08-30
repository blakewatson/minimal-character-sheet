<template>
  <section
    class="border-light-foreground dark:border-dark-foreground flex flex-wrap items-center justify-around gap-2 border-y pt-2 pb-2"
  >
    <div class="flex items-center gap-1">
      <label class="small-label" for="initiative">{{ $t('Initiative') }}</label>
      <field
        :read-only="readOnly"
        :value="initiative"
        @update-value="updateInitiative($event)"
        class="text-center min-[500px]:text-xl sm:text-2xl!"
        id="initiative"
      ></field>
    </div>

    <button
      :disabled="readOnly"
      :title="$t('Override proficiency bonus')"
      @click="openProficiencyDialog"
      class="hover:border-light-foreground flex cursor-pointer items-center gap-2 rounded-xs border border-transparent px-1 dark:hover:border-neutral-400"
    >
      <div class="small-label">{{ $t('Proficiency bonus') }}</div>
      <span
        class="min-[500px]:text-xl sm:text-2xl"
        :class="{ underline: Boolean(proficiencyOverride) }"
        >{{ $signedNumString(proficiencyBonus) }}</span
      >
    </button>

    <div class="flex items-center gap-2">
      <label class="small-label" for="inspiration">{{
        $t('Inspiration')
      }}</label>
      <input
        :checked="inspiration"
        :disabled="readOnly"
        @input="updateInspiration"
        id="inspiration"
        type="checkbox"
      />
    </div>

    <div class="flex items-center gap-2">
      <label class="small-label" for="shortRests">{{
        $t('Short rests')
      }}</label>
      <field
        :read-only="readOnly"
        :value="shortRests"
        @update-value="updateShortRests($event)"
        class="text-center min-[500px]:text-xl sm:text-2xl!"
        id="shortRests"
        type="number"
      ></field>
    </div>

    <app-dialog
      :close-label="$t('Cancel')"
      :title="$t('Proficiency bonus override')"
      @close="showProficiencyDialog = false"
      @submit="saveProficiencyOverride"
      v-if="showProficiencyDialog"
    >
      <template #content>
        <p class="mb-2">
          {{ $t('Proficiency override description') }}
        </p>

        <label for="proficiency-bonus" class="small-label mr-4 text-base">{{
          $t('Proficiency bonus')
        }}</label>

        <field
          :readOnly="readOnly"
          :value="proficiencyOverrideValue || 0"
          @update-value="proficiencyOverrideValue = $event"
          id="proficiency-bonus"
          class="min-w-14 text-center text-lg!"
          type="number"
        ></field>
      </template>

      <template #actions>
        <button class="button-primary" type="submit">
          {{ $t('Save') }}
        </button>

        <button @click="removeProficiencyOverride" class="button" type="button">
          {{ $t('Remove override') }}
        </button>
      </template>
    </app-dialog>
  </section>
</template>

<script>
import { state, proficiencyBonus as storeProficiencyBonus } from '../store';
import AppDialog from './AppDialog.vue';
import Field from './Field.vue';

export default {
  name: 'Proficiency',

  data() {
    return {
      /** @type {number | null} */
      proficiencyOverrideValue: null,
      showProficiencyDialog: false,
    };
  },

  computed: {
    proficiencyBonus() {
      return storeProficiencyBonus.value;
    },
    inspiration() {
      return state.inspiration;
    },
    readOnly() {
      return state.readOnly;
    },
    initiative() {
      return state.initiative;
    },
    shortRests() {
      return state.shortRests;
    },
    proficiencyOverride() {
      return state.proficiencyOverride;
    },
  },

  methods: {
    /** @param {string} val */
    updateInitiative(val) {
      state.initiative = val;
    },

    /** @param {InputEvent} event */
    updateInspiration(event) {
      state.inspiration = /** @type {HTMLInputElement} */ (
        event.target
      ).checked;
    },

    /** @param {number | null} val */
    updateShortRests(val) {
      state.shortRests = val ?? 0;
    },

    openProficiencyDialog() {
      this.proficiencyOverrideValue = this.proficiencyBonus;
      this.showProficiencyDialog = true;
    },

    saveProficiencyOverride() {
      state.proficiencyOverride = this.proficiencyOverrideValue ?? null;
      this.showProficiencyDialog = false;
      this.proficiencyOverrideValue = null;
    },

    removeProficiencyOverride() {
      state.proficiencyOverride = null;
      this.showProficiencyDialog = false;
      this.proficiencyOverrideValue = null;
    },
  },

  components: {
    'app-dialog': AppDialog,
    field: Field,
  },
};
</script>
