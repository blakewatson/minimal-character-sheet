<template>
  <section
    class="border-light-foreground dark:border-dark-foreground flex flex-wrap items-center justify-around gap-2 border-t pt-2 pb-2"
  >
    <div class="flex items-center gap-1">
      <label class="small-label" for="initiative">Initiative</label>
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
      @click="openProficiencyDialog"
      title="Override proficiency bonus"
      class="hover:border-light-foreground flex cursor-pointer items-center gap-2 rounded-xs border border-transparent px-1 dark:hover:border-neutral-400"
    >
      <div class="small-label">Proficiency bonus</div>
      <span
        class="min-[500px]:text-xl sm:text-2xl"
        :class="{ underline: Boolean(proficiencyOverride) }"
        >{{ proficiencyBonus | signedNumString }}</span
      >
    </button>

    <div class="flex items-center gap-2">
      <label class="small-label" for="inspiration"> Inspiration </label>
      <input
        :checked="inspiration"
        :disabled="readOnly"
        @input="updateInspiration"
        id="inspiration"
        type="checkbox"
      />
    </div>

    <div class="flex items-center gap-2">
      <label class="small-label" for="shortRests">Short rests</label>
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
      @close="showProficiencyDialog = false"
      @submit="saveProficiencyOverride"
      title="Proficiency bonus override"
      close-label="Cancel"
      v-if="showProficiencyDialog"
    >
      <template #content>
        <p class="mb-2">
          If you need to override the standard proficiency bonus calculation,
          you can enter it here. Click Remove override to revert back to the
          standard calculation.
        </p>

        <label for="proficiency-bonus" class="small-label text-base"
          >Proficiency bonus</label
        >

        <field
          :readOnly="readOnly"
          :value="proficiencyOverrideValue"
          @update-value="proficiencyOverrideValue = $event"
          id="proficiency-bonus"
          class="min-w-14 text-center text-lg!"
          type="number"
        ></field>
      </template>

      <template #actions>
        <button type="submit" class="button-primary mb-2">Save</button>

        <button
          type="button"
          @click="removeProficiencyOverride"
          class="button mb-2"
        >
          Remove override
        </button>
      </template>
    </app-dialog>
  </section>
</template>

<script>
import { mapGetters, mapState } from 'vuex';
import AppDialog from './AppDialog.vue';
import Field from './Field';

export default {
  name: 'Proficiency',

  data() {
    return {
      proficiencyOverrideValue: null,
      showProficiencyDialog: false,
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
      this.proficiencyOverrideValue = this.proficiencyBonus.toString();
      this.showProficiencyDialog = true;
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
      this.showProficiencyDialog = false;
      this.proficiencyOverrideValue = null;
    },

    removeProficiencyOverride() {
      this.$store.commit('updateProficiencyOverride', null);
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
