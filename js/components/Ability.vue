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
      @update-value="updateScore($event)"
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
    <div class="text-center">{{ $signedNumString(saveBonus) }}</div>
  </div>
</template>

<script>
import { state, proficiencyBonus as storeProficiencyBonus, updateAbilityScore, updateSavingThrow } from '../store';
import Field from './Field.vue';

export default {
  name: 'Ability',

  props: ['ability', 'modifier'],

  computed: {
    readOnly() { return state.readOnly; },
    savingThrows() { return state.savingThrows; },
    proficiencyBonus() { return storeProficiencyBonus.value; },

    savingThrow() {
      return this.savingThrows.find((st) => st.name === this.ability.name);
    },

    saveBonus() {
      if (!this.savingThrow) return 0;
      if (this.savingThrow.proficient)
        return this.modifier + this.proficiencyBonus;
      return this.modifier;
    },

    inputId() {
      return `${this.ability.name}-saving-throw`;
    },
  },

  methods: {
    updateScore(val) {
      var score = parseInt(val);
      updateAbilityScore({
        name: this.ability.name,
        score: score,
      });
    },

    toggleProficiency() {
      if (!this.savingThrow) return;
      updateSavingThrow({
        name: this.savingThrow.name,
        proficient: !this.savingThrow.proficient,
      });
    },
  },

  components: {
    field: Field,
  },
};
</script>
