<template>
  <div
    class="border-light-foreground dark:border-dark-foreground flex w-16 flex-col items-center rounded-xs border"
  >
    <div
      class="text-reverse bg-reverse mb-1 self-stretch py-0.5 text-center text-sm"
    >
      {{ ability.name }}
    </div>
    <span class="block text-center text-xl">{{
      modifier | signedNumString
    }}</span>
    <field
      class="text-center font-bold"
      :value="ability.score"
      :read-only="readOnly"
      @update-value="updateScore($event)"
      type="number"
    ></field>

    <div
      class="border-light-foreground dark:border-dark-foreground mt-2 flex gap-2 border-t pt-2"
    >
      <label
        :for="inputId"
        class="small-label"
        style="display: flex; align-items: center; gap: 0.125em"
      >
        Save
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
    <div class="text-center">{{ saveBonus | signedNumString }}</div>
  </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import Field from './Field';

export default {
  name: 'Ability',

  props: ['ability', 'modifier'],

  computed: {
    ...mapState(['readOnly', 'savingThrows']),
    ...mapGetters(['proficiencyBonus']),

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
      this.$store.commit('updateAbilityScore', {
        name: this.ability.name,
        score: score,
      });
    },

    toggleProficiency() {
      if (!this.savingThrow) return;
      this.$store.commit('updateSavingThrow', {
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
