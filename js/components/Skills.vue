<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground border-t pb-4"
  >
    <summary class="section-label">{{ $t('Skills') }}</summary>

    <ul class="sm:columns-2">
      <li class="mb-2 flex items-center gap-2" v-for="(skill, i) in skills">
        <label :for="`double-prof-${i}`" class="sr-only">{{
          $t('Double proficiency')
        }}</label>

        <input
          :checked="skill.doubleProficient"
          :disabled="readOnly || !skill.proficient"
          :id="`double-prof-${i}`"
          :style="{
            opacity: skill.proficient ? 1 : 0,
            pointerEvents: skill.proficient ? 'auto' : 'none',
          }"
          :title="$t('Toggle double proficiency')"
          @change="setProficiency(i, 'doubleProficient')"
          class="mr-1"
          type="checkbox"
        />

        <input
          type="checkbox"
          :id="`skill-prof-${i}`"
          :checked="skill.proficient"
          :disabled="readOnly || (skill.proficient && skill.doubleProficient)"
          @change="setProficiency(i, 'proficient')"
        />

        <button
          :class="{
            underline: !isNullOrUndefined(skill.modifierOverride),
          }"
          :disabled="readOnly"
          :title="$t('Override modifier')"
          @click="openOverrideDialog(skill)"
          class="hover:border-light-foreground w-10 cursor-pointer rounded-xs border border-transparent px-1 text-right decoration-2 dark:hover:border-neutral-400"
        >
          {{ $signedNumString(getSkillModifier(skill)) }}
        </button>

        <label
          :for="`skill-prof-${i}`"
          :title="$t('Toggle proficiency')"
          class="skill-label"
        >
          {{ $t(skill.name) }}
          <span class="small-label not-italic">({{ $t(skill.ability) }})</span>
        </label>
      </li>
    </ul>

    <p class="py-3 text-center">
      <button
        :class="{ underline: passivePerceptionOverride !== null }"
        :disabled="readOnly"
        :title="$t('Override passive perception')"
        @click="openPassivePerceptionDialog"
        class="hover:border-light-foreground cursor-pointer rounded-xs border border-transparent px-1 decoration-2 dark:hover:border-neutral-400"
      >
        <strong class="">{{ getPassivePerception() }}</strong>
      </button>
      {{ $t('Passive Perception') }}
      <span class="small-label not-italic">({{ $t('WIS') }})</span>
    </p>

    <app-dialog
      :close-label="$t('Cancel')"
      :title="$t('Skill modifier override')"
      @close="showOverrideDialog = false"
      @submit="saveOverride"
      v-if="showOverrideDialog"
    >
      <template #content>
        <p class="mb-2 text-sm">
          {{ $t('Skill bonus override description') }}
        </p>

        <label class="small-label mr-4 text-base" for="skill-modifier">{{
          selectedSkill?.name ? $t(selectedSkill.name) : ''
        }}</label>
        <field
          :readOnly="readOnly"
          :value="modifierOverride"
          @update-value="modifierOverride = $event"
          class="min-w-14 text-center text-lg!"
          has-bg
          id="skill-modifier"
          style="min-width: 50px"
          type="number"
        ></field>

        <label class="mt-2 flex items-center gap-2">
          <input type="checkbox" v-model="modifierOverrideIsAdditive" />
          <span class="text-sm">Add to standard calculation</span>
        </label>
      </template>

      <template #actions>
        <button class="button-primary" type="submit">{{ $t('Save') }}</button>
        <button @click="removeOverride" class="button" type="button">
          {{ $t('Remove override') }}
        </button>
      </template>
    </app-dialog>
  </details>
</template>

<script>
import {
  state,
  modifiers as storeModifiers,
  proficiencyBonus as storeProficiencyBonus,
} from '../store';
import { isNullOrUndefined } from '../utils';
import AppDialog from './AppDialog.vue';
import Field from './Field.vue';

/** @typedef {import('../store').Skill} Skill */

export default {
  name: 'Skills',

  data() {
    return {
      /** @type {number | null} */
      modifierOverride: null,
      modifierOverrideIsAdditive: false,
      /** @type {Skill | null} */
      selectedSkill: null,
      showOverrideDialog: false,
    };
  },

  computed: {
    skills() {
      return state.skills;
    },
    readOnly() {
      return state.readOnly;
    },
    passivePerceptionOverride() {
      return state.passivePerceptionOverride;
    },
    passivePerceptionOverrideIsAdditive() {
      return state.passivePerceptionOverrideIsAdditive;
    },
    modifiers() {
      return storeModifiers.value;
    },
    proficiencyBonus() {
      return storeProficiencyBonus.value;
    },
  },

  methods: {
    /** @param {Partial<Skill>} skill */
    getSkillModifier(skill) {
      var mod = this.modifiers.reduce((acc, m) => {
        if (m.ability === skill.ability) return acc + m.val;
        return acc;
      }, 0);

      let bonus = mod;

      if (skill.doubleProficient) {
        bonus = mod + this.proficiencyBonus * 2;
      } else if (skill.proficient) {
        bonus = mod + this.proficiencyBonus;
      }

      if (
        skill.modifierOverride !== null &&
        skill.modifierOverride !== undefined
      ) {
        return skill.isAdditive
          ? skill.modifierOverride + bonus
          : skill.modifierOverride;
      }

      return bonus;
    },

    /**
     * @param {number} i
     * @param {'proficient' | 'doubleProficient'} prop
     */
    setProficiency(i, prop) {
      var proficient = this.skills[i].proficient;
      var doubleProficient = this.skills[i].doubleProficient;

      if (prop === 'proficient') {
        proficient = !proficient;
        state.skills[i].proficient = proficient;
        return;
      }

      doubleProficient = !doubleProficient;
      state.skills[i].doubleProficient = doubleProficient;
    },

    getPassivePerception() {
      // Default to just Wisdom modifier
      let passivePerception = 10 + this.getSkillModifier({ ability: 'WIS' });

      // Find the Perception skill
      const perceptionSkill = this.skills.find(
        (skill) => skill.name === 'Perception',
      );

      if (perceptionSkill) {
        // This will account for proficiency bonuses.
        passivePerception = 10 + this.getSkillModifier(perceptionSkill);
      }

      // Check for override
      if (
        this.passivePerceptionOverride !== null &&
        this.passivePerceptionOverride !== undefined
      ) {
        // If the override is found, it either replaces or adds to the existing bonus.
        passivePerception = this.passivePerceptionOverrideIsAdditive
          ? passivePerception + this.passivePerceptionOverride
          : this.passivePerceptionOverride;
      }

      return passivePerception;
    },

    isNullOrUndefined,

    /** @param {Skill} skill */
    openOverrideDialog(skill) {
      this.selectedSkill = skill;
      this.modifierOverride = skill.isAdditive
        ? skill.modifierOverride
        : this.getSkillModifier(skill);
      this.modifierOverrideIsAdditive = skill.isAdditive;
      this.showOverrideDialog = true;
    },

    openPassivePerceptionDialog() {
      // Create a pseudo-skill object for passive perception
      // @ts-ignore
      this.selectedSkill = {
        name: 'Passive Perception',
        isPassivePerception: true,
      };
      this.modifierOverride = this.passivePerceptionOverrideIsAdditive
        ? this.passivePerceptionOverride
        : this.getPassivePerception();
      this.modifierOverrideIsAdditive =
        this.passivePerceptionOverrideIsAdditive;
      this.showOverrideDialog = true;
    },

    closeOverrideDialog() {
      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
    },

    saveOverride() {
      let override = this.modifierOverride ?? null;

      // Check if this is passive perception or a regular skill
      if (this.selectedSkill?.isPassivePerception) {
        state.passivePerceptionOverride = override;
        state.passivePerceptionOverrideIsAdditive =
          this.modifierOverrideIsAdditive;
      } else {
        state.skills = state.skills.map((skill) => {
          if (skill.name === this.selectedSkill?.name) {
            return {
              ...skill,
              modifierOverride: override,
              isAdditive: this.modifierOverrideIsAdditive,
            };
          }
          return skill;
        });
      }

      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
      this.modifierOverrideIsAdditive = false;
    },

    removeOverride() {
      // Check if this is passive perception or a regular skill
      if (this.selectedSkill?.isPassivePerception) {
        state.passivePerceptionOverride = null;
        state.passivePerceptionOverrideIsAdditive = false;
      } else {
        state.skills = state.skills.map((skill) => {
          if (skill.name === this.selectedSkill?.name) {
            return {
              ...skill,
              modifierOverride: null,
              isAdditive: false,
            };
          }
          return skill;
        });
      }
      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
      this.modifierOverrideIsAdditive = false;
    },
  },

  components: {
    'app-dialog': AppDialog,
    field: Field,
  },
};
</script>
