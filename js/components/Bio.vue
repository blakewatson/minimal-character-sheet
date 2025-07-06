<template>
  <section class="bio">
    <p class="meta mb-sm">
      <field
        align="left"
        class-names="mr-sm"
        :value="characterName"
        placeholder="Name"
        :read-only="readOnly"
        @update-value="updateBio('characterName', $event)"
      ></field>

      <label for="characterBackground" class="small muted"
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

      <label for="characterRace" class="small muted">
        {{ is_2024 ? "Species" : "Race" }}
      </label>
      <field
        :read-only="readOnly"
        :value="race"
        @update-value="updateBio('race', $event)"
        align="left"
        id="characterRace"
      ></field>
    </p>

    <p class="meta vert-after">
      <label for="characterClass" class="small muted sr-only"
        >Class / subclass</label
      >
      <field
        id="characterClass"
        class-names="mr-sm"
        align="left"
        :value="className"
        placeholder="Class / subclass"
        :read-only="readOnly"
        @update-value="updateBio('className', $event)"
      ></field>

      <label for="characterLevel" class="small muted">Level</label>
      <field
        id="characterLevel"
        class-names="mr-sm"
        :value="level"
        type="number"
        min="1"
        :read-only="readOnly"
        @update-value="updateLevel"
      ></field>

      <label for="characterXp" class="small muted">XP</label>
      <field
        id="characterXp"
        class-names="mr-sm"
        :value="xp"
        type="number"
        :read-only="readOnly"
        @update-value="updateBio('xp', $event)"
      ></field>

      <label for="characterAlignment" class="small muted"
        >Alignment</label
      >
      <field
        id="characterAlignment"
        align="left"
        :value="alignment"
        :read-only="readOnly"
        @update-value="updateBio('alignment', $event)"
      ></field>
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
