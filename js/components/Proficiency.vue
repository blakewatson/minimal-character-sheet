<template>
  <section
    class="row row-spaced row-vert-centered"
    style="
      flex-wrap: wrap;
      margin-top: -0.5rem;
      padding-bottom: 0.5rem;
      gap: 0.5rem;
    "
  >
    <div class="vert-center">
      <label class="meta small muted mr-xs" for="initiative">Initiative</label>
      <field
        :read-only="readOnly"
        :value="initiative"
        @update-value="updateInitiative($event)"
        classNames="huge block padded"
        id="initiative"
      ></field>
    </div>

    <button
      :disabled="readOnly"
      @click="openProficiencyDialog"
      title="Override proficiency bonus"
      class="button-discoverable vert-center"
    >
      <div class="meta small muted mr-sm">Proficiency bonus</div>
      <span
        class="huge"
        :class="{ 'skill-override': Boolean(proficiencyOverride) }"
        >{{ proficiencyBonus | signedNumString }}</span
      >
    </button>

    <div class="vert-center">
      <label class="vert-center meta small muted">
        Inspiration
        <input
          type="checkbox"
          :checked="inspiration"
          :disabled="readOnly"
          @input="updateInspiration"
        />
      </label>
    </div>

    <div class="vert-center">
      <label class="meta small muted mr-xs" for="shortRests">Short rests</label>
      <field
        :read-only="readOnly"
        :value="shortRests"
        @update-value="updateShortRests($event)"
        classNames="huge block padded"
        id="shortRests"
        type="number"
      ></field>
    </div>

    <dialog class="skill-override-dialog" ref="proficiencyDialog">
      <form @submit.prevent="saveProficiencyOverride">
        <p>
          <strong>Proficiency bonus override</strong>
        </p>
        <p>
          If you need to override the standard proficiency bonus calculation,
          you can enter it here. Click Remove override to revert back to the
          standard calculation.
        </p>

        <label for="proficiency-bonus">Proficiency bonus</label>
        <field
          :readOnly="readOnly"
          :value="proficiencyOverrideValue"
          @update-value="proficiencyOverrideValue = $event"
          id="proficiency-bonus"
          style="min-width: 50px"
          type="number"
        ></field>

        <div class="mt-md">
          <button type="submit" class="button-primary">Save</button>
          <button
            type="button"
            @click="removeProficiencyOverride"
            class="button-secondary"
          >
            Remove override
          </button>
          <button
            type="button"
            @click="closeProficiencyDialog"
            class="button-secondary"
          >
            Cancel
          </button>
        </div>
      </form>
    </dialog>
  </section>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import Field from './Field';

export default {
  name: 'Proficiency',

  data() {
    return {
      proficiencyOverrideValue: null,
    };
  },

  computed: {
    ...mapGetters(['proficiencyBonus']),
    ...mapState([
      'inspiration',
      'readOnly',
      'initiative',
      'shortRests',
      'proficiencyOverride',
    ]),
  },

  methods: {
    updateInitiative(val) {
      this.$store.commit('updateInitiative', val);
    },

    updateInspiration(val) {
      this.$store.commit('updateInspiration', val.target.checked);
    },

    updateShortRests(val) {
      val = val ? parseInt(val) : 0;
      this.$store.commit('updateShortRests', val);
    },

    openProficiencyDialog() {
      this.$refs.proficiencyDialog.showModal();
      this.proficiencyOverrideValue = this.proficiencyBonus.toString();
    },

    closeProficiencyDialog() {
      this.$refs.proficiencyDialog.close();
      this.proficiencyOverrideValue = null;
    },

    saveProficiencyOverride() {
      let override =
        this.proficiencyOverrideValue === ''
          ? null
          : (this.proficiencyOverrideValue ?? null);

      if (override !== null && override !== undefined) {
        const overrideStr = String(override);
        const validPattern = /^[+\-\d]\d*$/;

        if (validPattern.test(overrideStr)) {
          try {
            override = parseInt(overrideStr, 10);
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

      this.$store.commit('updateProficiencyOverride', override);
      this.$refs.proficiencyDialog.close();
      this.proficiencyOverrideValue = null;
    },

    removeProficiencyOverride() {
      this.$store.commit('updateProficiencyOverride', null);
      this.$refs.proficiencyDialog.close();
      this.proficiencyOverrideValue = null;
    },
  },

  components: {
    field: Field,
  },
};
</script>
