<template>
  <section class="bio pb-sm">
    <p class="meta vert-after">
      <span class="flex-baseline mb-xs">
        <label for="characterName" class="small muted inline-block">Name</label>
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

      <span class="flex-baseline mb-xs">
        <label for="characterBackground" class="small muted inline-block"
          >Background</label
        >
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

      <span class="flex-baseline mb-xs">
        <label for="characterRace" class="small muted inline-block">
          {{ is_2024 ? "Species" : "Race" }}
        </label>
        <field
          :read-only="readOnly"
          :value="race"
          @update-value="updateBio('race', $event)"
          align="left"
          id="characterRace"
        ></field>
      </span>

      <br class="bio-break">

      <span class="flex-baseline mb-xs">
        <label for="characterClass" class="small muted inline-block"
          >Class</label
        >
        <field
          id="characterClass"
          class-names="mr-sm"
          align="left"
          :value="className"
          :read-only="readOnly"
          @update-value="updateBio('className', $event)"
        ></field>
      </span>

      <span class="flex-baseline mb-xs">
        <label for="characterLevel" class="small muted inline-block">Level</label>
        <field
          id="characterLevel"
          class-names="mr-sm"
          :value="level"
          type="number"
          min="1"
          :read-only="readOnly"
          @update-value="updateLevel"
        ></field>
      </span>

      <span class="flex-baseline mb-xs">
        <label for="characterXp" class="small muted inline-block">XP</label>
        <field
          id="characterXp"
          class-names="mr-sm"
          :value="xp"
          type="number"
          :read-only="readOnly"
          @update-value="updateBio('xp', $event)"
        ></field>
      </span>

      <span class="flex-baseline mb-xs">
        <label for="characterAlignment" class="small muted inline-block"
          >Alignment</label
        >
        <field
          id="characterAlignment"
          align="left"
          :value="alignment"
          :read-only="readOnly"
          @update-value="updateBio('alignment', $event)"
        ></field>
      </span>
    </p>

    <vitals></vitals>
  </section>
</template>

<script>
import { mapState } from "vuex";
import Field from "./Field";
import Vitals from "./Vitals";

export default {
  name: "Bio",

  computed: {
    ...mapState([
      "is_2024",
      "level",
      "characterName",
      "className",
      "race",
      "background",
      "alignment",
      "xp",
      "readOnly",
    ]),
  },

  methods: {
    updateLevel(level) {
      this.$store.commit("updateLevel", { level: parseInt(level) });
    },

    updateBio(field, val) {
      this.$store.commit("updateBio", { field, val });
    },
  },

  components: {
    field: Field,
    vitals: Vitals,
  },
};
</script>
