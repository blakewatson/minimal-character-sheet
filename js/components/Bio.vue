<template>
  <section class="pb-2">
    <div class="mb-4">
      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterName" class="small-label">{{ $t('Name') }}</label>
        <field
          :placeholder="$t('Name')"
          :read-only="readOnly"
          :value="characterName"
          @update-value="updateBio('characterName', $event)"
          align="left"
          class-names="mr-sm"
          id="characterName"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterBackground" class="small-label">{{
          $t('Background')
        }}</label>
        <field
          :placeholder="$t('Background')"
          :read-only="readOnly"
          :value="background"
          @update-value="updateBio('background', $event)"
          align="left"
          class-names="mr-sm"
          id="characterBackground"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterRace" class="small-label">
          {{ $t(is_2024 ? 'Species' : 'Race') }}
        </label>
        <field
          :placeholder="$t(is_2024 ? 'Species' : 'Race')"
          :read-only="readOnly"
          :value="race"
          @update-value="updateBio('race', $event)"
          align="left"
          id="characterRace"
        ></field>
      </span>

      <br class="hidden sm:block" />

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterClass" class="small-label">{{ $t('Class') }}</label>
        <field
          :placeholder="$t('Class')"
          :read-only="readOnly"
          :value="className"
          @update-value="updateBio('className', $event)"
          align="left"
          class-names="mr-sm"
          id="characterClass"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterLevel" class="small-label">{{ $t('Level') }}</label>
        <field
          :placeholder="$t('Level')"
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
        <label for="characterXp" class="small-label">{{ $t('XP') }}</label>
        <field
          :placeholder="$t('XP')"
          :read-only="readOnly"
          :value="xp"
          @update-value="updateBio('xp', $event)"
          class-names="mr-sm"
          id="characterXp"
          type="number"
        ></field>
      </span>

      <span class="mb-1 inline-flex max-w-full items-baseline gap-1">
        <label for="characterAlignment" class="small-label">{{
          $t('Alignment')
        }}</label>
        <field
          :placeholder="$t('Alignment')"
          :read-only="readOnly"
          :value="alignment"
          @update-value="updateBio('alignment', $event)"
          align="left"
          id="characterAlignment"
        ></field>
      </span>
    </div>

    <vitals></vitals>
  </section>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field.vue';
import Vitals from './Vitals.vue';

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
