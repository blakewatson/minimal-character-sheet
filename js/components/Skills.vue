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
          :disabled="readOnly || skill.doubleProficient"
          @change="setProficiency(i, 'proficient')"
        />

        <button
          :class="{ underline: Boolean(skill.modifierOverride) }"
          :disabled="readOnly"
          :title="$t('Override modifier')"
          @click="openOverrideDialog(skill)"
          class="hover:border-light-foreground w-10 cursor-pointer rounded-xs border border-transparent px-1 text-right dark:hover:border-neutral-400"
        >
          {{ getSkillModifier(skill) | signedNumString }}
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
        class="hover:border-light-foreground cursor-pointer rounded-xs border border-transparent px-1 dark:hover:border-neutral-400"
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
        <p class="mb-2">
          {{ $t('Proficiency override description') }}
        </p>

        <label class="small-label text-base" for="skill-modifier">{{
          selectedSkill?.name ? $t(selectedSkill.name) : ''
        }}</label>
        <field
          :readOnly="readOnly"
          :value="modifierOverride"
          @update-value="modifierOverride = $event"
          class="min-w-14 bg-neutral-100 text-center text-lg!"
          id="skill-modifier"
          style="min-width: 50px"
          type="number"
        ></field>
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
import { mapGetters, mapState } from 'vuex';
import AppDialog from './AppDialog.vue';
import Field from './Field.vue';

export default {
  name: 'Skills',

  data() {
    return {
      modifierOverride: null,
      selectedSkill: null,
      showOverrideDialog: false,
    };
  },

  computed: {
    ...mapState(['skills', 'readOnly', 'passivePerceptionOverride']),
    ...mapGetters(['modifiers', 'proficiencyBonus']),
  },

  methods: {
    getSkillModifier(skill) {
      var mod = this.modifiers.reduce((acc, m) => {
        if (m.ability === skill.ability) return acc + m.val;
        return acc;
      }, 0);

      if (
        skill.modifierOverride !== null &&
        skill.modifierOverride !== undefined
      ) {
        return skill.modifierOverride;
      }

      if (skill.doubleProficient) {
        return mod + this.proficiencyBonus * 2;
      }

      if (skill.proficient) {
        return mod + this.proficiencyBonus;
      }

      return mod;
    },

    setProficiency(i, prop) {
      var proficient = this.skills[i].proficient;
      var doubleProficient = this.skills[i].doubleProficient;

      if (prop === 'proficient') {
        proficient = !proficient;
      } else if (prop === 'doubleProficient') {
        doubleProficient = !doubleProficient;
      }

      this.$store.commit('updateSkillProficiency', {
        i,
        proficient,
        doubleProficient,
      });
    },

    getPassivePerception() {
      // Check for override first
      if (
        this.passivePerceptionOverride !== null &&
        this.passivePerceptionOverride !== undefined
      ) {
        return this.passivePerceptionOverride;
      }

      // Find the Perception skill
      const perceptionSkill = this.skills.find(
        (skill) => skill.name === 'Perception',
      );
      if (perceptionSkill) {
        return 10 + this.getSkillModifier(perceptionSkill);
      }
      // Fallback to just Wisdom modifier if Perception skill not found
      return 10 + this.getSkillModifier({ ability: 'WIS' });
    },

    openOverrideDialog(skill) {
      this.selectedSkill = skill;
      this.modifierOverride = this.getSkillModifier(skill).toString();
      this.showOverrideDialog = true;
    },

    openPassivePerceptionDialog() {
      // Create a pseudo-skill object for passive perception
      this.selectedSkill = {
        name: 'Passive Perception',
        isPassivePerception: true,
      };
      this.modifierOverride = this.getPassivePerception().toString();
      this.showOverrideDialog = true;
    },

    closeOverrideDialog() {
      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
    },

    saveOverride() {
      let override =
        this.modifierOverride === '' ? null : (this.modifierOverride ?? null);

      // Validate and parse the override value
      if (override !== null && override !== undefined) {
        const overrideStr = String(override);

        // Check if string starts with +, -, or digit and rest are digits
        const validPattern = /^[+\-\d]\d*$/;

        if (validPattern.test(overrideStr)) {
          try {
            override = parseInt(overrideStr, 10);
            // Check if parsing resulted in a valid number
            if (isNaN(override)) {
              override = null;
            }
          } catch (error) {
            override = null;
          }
        } else {
          override = null;
        }
      }

      // Check if this is passive perception or a regular skill
      if (this.selectedSkill.isPassivePerception) {
        this.$store.commit('updatePassivePerceptionOverride', override);
      } else {
        this.$store.commit('updateSkillModifierOverride', {
          skillName: this.selectedSkill.name,
          modifierOverride: override,
        });
      }

      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
    },

    removeOverride() {
      // Check if this is passive perception or a regular skill
      if (this.selectedSkill.isPassivePerception) {
        this.$store.commit('updatePassivePerceptionOverride', null);
      } else {
        this.$store.commit('updateSkillModifierOverride', {
          skillName: this.selectedSkill.name,
          modifierOverride: null,
        });
      }
      this.showOverrideDialog = false;
      this.selectedSkill = null;
      this.modifierOverride = null;
    },
  },

  components: {
    'app-dialog': AppDialog,
    field: Field,
  },
};
</script>
