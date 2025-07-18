<template>
  <details open class="section">
    <summary class="label centered">Skills</summary>

    <ul class="skills-list">
      <li class="skill-item" v-for="(skill, i) in skills">
        <label class="sr-only" :for="`double-prof-${i}`"
          >Double proficiency</label
        >

        <input
          :checked="skill.doubleProficient"
          :disabled="readOnly"
          :id="`double-prof-${i}`"
          :style="{
            opacity: skill.proficient ? 1 : 0,
            pointerEvents: skill.proficient ? 'auto' : 'none',
          }"
          @change="setProficiency(i, 'doubleProficient')"
          class="mr-sm"
          title="Toggle double proficiency"
          type="checkbox"
        />

        <label title="Toggle proficiency">
          <input
            type="checkbox"
            :checked="skill.proficient"
            :disabled="readOnly || skill.doubleProficient"
            @change="setProficiency(i, 'proficient')"
          />

          <span class="sr-only">Toggle proficiency</span>
        </label>

        <button
          :disabled="readOnly"
          @click="openOverrideDialog(skill)"
          title="Override modifier"
          class="skill-button"
        >
          <strong class="skill-modifier" :class="{ 'skill-override': Boolean(skill.modifierOverride) }">{{
            getSkillModifier(skill) | signedNumString
          }}</strong>
          {{ skill.name }}
          <span class="small muted">({{ skill.ability }})</span>
        </button>
      </li>
    </ul>

    <p class="centered">
      <br />
      <strong class="skill-modifier">{{ getPassivePerception() }}</strong>
      Passive Perception <span class="small muted">(WIS)</span>
    </p>

    <dialog class="skill-override-dialog" ref="overrideDialog">
      <form @submit.prevent="saveOverride">
        <p>
          <strong>{{ selectedSkill?.name }} modifier</strong>
        </p>
        <p>
          If you need to override the standard modifier calculation for
          <strong>{{ selectedSkill?.name }}</strong
          >, you can enter it here. Click Remove override to revert back to the
          standard calculation.
        </p>

        <label for="skill-modifier">{{ selectedSkill?.name }}</label>
        <field
          :readOnly="readOnly"
          :value="modifierOverride"
          @update-value="modifierOverride = $event"
          id="skill-modifier"
          style="min-width: 50px;"
          type="number"
        ></field>

        <div class="mt-md">
          <button type="submit" class="button-primary">Save</button>
          <button type="button" @click="removeOverride" class="button-secondary">Remove override</button>
          <button type="button" @click="closeOverrideDialog" class="button-secondary">Cancel</button>
        </div>
      </form>
    </dialog>
  </details>
</template>

<script>
import { mapState, mapGetters } from "vuex";
import Field from "./Field.vue";

export default {
  name: "Skills",

  data() {
    return {
      modifierOverride: null,
      selectedSkill: null,
    };
  },

  computed: {
    ...mapState(["skills", "readOnly"]),
    ...mapGetters(["modifiers", "proficiencyBonus"]),
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

      if (prop === "proficient") {
        proficient = !proficient;
      } else if (prop === "doubleProficient") {
        doubleProficient = !doubleProficient;
      }

      this.$store.commit("updateSkillProficiency", {
        i,
        proficient,
        doubleProficient,
      });
    },

    getPassivePerception() {
      // Find the Perception skill
      const perceptionSkill = this.skills.find(
        (skill) => skill.name === "Perception"
      );
      if (perceptionSkill) {
        return 10 + this.getSkillModifier(perceptionSkill);
      }
      // Fallback to just Wisdom modifier if Perception skill not found
      return 10 + this.getSkillModifier({ ability: "WIS" });
    },

    openOverrideDialog(skill) {
      this.selectedSkill = skill;
      this.$refs.overrideDialog.showModal();
      this.modifierOverride = this.getSkillModifier(skill).toString();
      console.log(this.modifierOverride);
    },

    closeOverrideDialog() {
      this.$refs.overrideDialog.close();
      this.selectedSkill = null;
      this.modifierOverride = null;
    },

    saveOverride() {
      let override = this.modifierOverride === "" ? null : this.modifierOverride ?? null;

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

      this.$store.commit("updateSkillModifierOverride", {
        skillName: this.selectedSkill.name,
        modifierOverride: override,
      });

      this.$refs.overrideDialog.close();
      this.selectedSkill = null;
      this.modifierOverride = null;
    },

    removeOverride() {
      this.$store.commit("updateSkillModifierOverride", {
        skillName: this.selectedSkill.name,
        modifierOverride: null,
      });
      this.$refs.overrideDialog.close();
      this.selectedSkill = null;
      this.modifierOverride = null;
    },
  },

  components: {
    field: Field,
  },
};
</script>
