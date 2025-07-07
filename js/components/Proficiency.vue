<template>
  <section class="row row-spaced row-vert-centered">
    <div class="vert-center">
      <label class="meta small muted" for="initiative">Initiative</label>
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
      <label class="meta small muted">
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
      <span class="meta small muted mr-sm">Short rests</span>
      <div class="checkbox-group">
        <label class="meta small muted">
          <input
            type="checkbox"
            :checked="shortRest1"
            :disabled="readOnly"
            @input="updateShortRest1"
          />
          <span class="sr-only">1</span>
        </label>
        <label class="meta small muted">
          <input
            type="checkbox"
            :checked="shortRest2"
            :disabled="readOnly"
            @input="updateShortRest2"
          />
          <span class="sr-only">2</span>
        </label>
      </div>
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
    ...mapState(["inspiration", "readOnly", "initiative", "shortRest1", "shortRest2"]),
  },

  methods: {
    updateInitiative(val) {
      val = val ? parseInt(val) : 0;
      this.$store.commit("updateInitiative", val);
    },

    updateInspiration(val) {
      this.$store.commit("updateInspiration", val.target.checked);
    },

    updateShortRest1(val) {
      this.$store.commit("updateShortRest1", val.target.checked);
    },

    updateShortRest2(val) {
      this.$store.commit("updateShortRest2", val.target.checked);
    },
  },

  components: {
    field: Field,
  },
};
</script>
