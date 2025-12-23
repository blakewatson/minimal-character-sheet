<template>
  <section class="pb-2">
    <div class="mb-4">
      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterName" class="small-label">Name</label>
        <field
          :read-only="readOnly"
          :value="characterName"
          @update-value="updateBio('characterName', $event)"
          align="left"
          class-names="mr-sm"
          id="characterName"
          placeholder="Name"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterBackground" class="small-label">Background</label>
        <field
          :read-only="readOnly"
          :value="background"
          @update-value="updateBio('background', $event)"
          align="left"
          class-names="mr-sm"
          id="characterBackground"
          placeholder=""
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterRace" class="small-label">
          {{ is_2024 ? 'Species' : 'Race' }}
        </label>
        <field
          :read-only="readOnly"
          :value="race"
          @update-value="updateBio('race', $event)"
          align="left"
          id="characterRace"
        ></field>
      </span>

      <br class="hidden sm:block" />

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterClass" class="small-label">Class</label>
        <field
          id="characterClass"
          class-names="mr-sm"
          align="left"
          :value="className"
          :read-only="readOnly"
          @update-value="updateBio('className', $event)"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterLevel" class="small-label">Level</label>
        <field
          :read-only="readOnly"
          :value="level"
          @update-value="updateLevel"
          class-names="mr-sm"
          id="characterLevel"
          min="1"
          type="number"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterXp" class="small-label">XP</label>
        <field
          id="characterXp"
          class-names="mr-sm"
          :value="xp"
          type="number"
          :read-only="readOnly"
          @update-value="updateBio('xp', $event)"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterAlignment" class="small-label">Alignment</label>
        <field
          id="characterAlignment"
          align="left"
          :value="alignment"
          :read-only="readOnly"
          @update-value="updateBio('alignment', $event)"
        ></field>
      </span>
    </div>

    <vitals></vitals>
  </section>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import Vitals from './Vitals';

export default {
  name: 'Bio',

  computed: {
    ...mapState([
      'is_2024',
      'level',
      'characterName',
      'className',
      'race',
      'background',
      'alignment',
      'xp',
      'readOnly',
    ]),
  },

  methods: {
    updateLevel(level) {
      this.$store.commit('updateLevel', { level: parseInt(level) });
    },

    updateBio(field, val) {
      this.$store.commit('updateBio', { field, val });
    },
  },

  components: {
    field: Field,
    vitals: Vitals,
  },
};
</script>
