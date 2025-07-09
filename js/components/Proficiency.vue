<template>
  <section
    class="row row-spaced row-vert-centered"
    style="flex-wrap: wrap; margin-top: -0.5rem; padding-bottom: 0.5rem;"
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

    <div class="vert-center">
      <span class="meta small muted">Proficiency bonus</span>
      <span class="huge padded">{{ proficiencyBonus | signedNumString }}</span>
    </div>

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
  </section>
</template>

<script>
import { mapGetters, mapState } from "vuex";
import Field from "./Field";

export default {
  name: "Proficiency",

  computed: {
    ...mapGetters(["proficiencyBonus"]),
    ...mapState(["inspiration", "readOnly", "initiative", "shortRests"]),
  },

  methods: {
    updateInitiative(val) {
      val = val ? parseInt(val) : 0;
      this.$store.commit("updateInitiative", val);
    },

    updateInspiration(val) {
      this.$store.commit("updateInspiration", val.target.checked);
    },

    updateShortRests(val) {
      val = val ? parseInt(val) : 0;
      this.$store.commit("updateShortRests", val);
    },
  },

  components: {
    field: Field,
  },
};
</script>
