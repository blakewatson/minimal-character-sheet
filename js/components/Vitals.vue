<template>
  <div>
    <div class="row vitals-row">
      <div class="box reverse">
        <label for="ac-field" class="block centered label">AC</label>
        <field
          id="ac-field"
          class="centered huge block padded"
          :value="ac"
          :read-only="readOnly"
          @update-value="updateVitals('ac', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <label for="hp-field" class="block centered label">HP</label>
        <field
          id="hp-field"
          class="huge padded"
          :value="hp"
          :read-only="readOnly"
          @update-value="updateVitals('hp', $event)"
        ></field
        >/<field
          class="normal strong"
          :value="maxHp"
          :read-only="readOnly"
          @update-value="updateVitals('maxHp', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <label for="temp-hp-field" class="block centered label">Temp HP</label>
        <field
          id="temp-hp-field"
          class="centered huge block padded"
          :value="tempHp"
          :read-only="readOnly"
          @update-value="updateVitals('tempHp', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <label for="hit-die-field" class="block centered label">Hit die</label>
        <field
          id="hit-die-field"
          class="huge padded"
          :value="hitDie"
          :read-only="readOnly"
          @update-value="updateVitals('hitDie', $event)"
        ></field
        >/<field
          class="normal"
          :value="totalHitDie"
          :read-only="readOnly"
          @update-value="updateVitals('totalHitDie', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <label for="speed-field" class="block centered label">Speed</label>
        <field
          id="speed-field"
          class="huge padded"
          :value="speed"
          :read-only="readOnly"
          @update-value="updateVitals('speed', $event)"
        ></field>
      </div>

      <div class="box box-lite">
        <span class="centered label">Death saves</span>

        <div class="death-saves-row mb-xs">
          <i class="mini-icon fa-sharp fa-regular fa-check"></i>

          <input
            :checked="save"
            :disabled="readOnly"
            @input="updateDeathSaves('successes', i, !save)"
            class="m-none"
            type="checkbox"
            v-for="(save, i) in deathSaves.successes"
          />
        </div>

        <div class="death-saves-row">
          <i class="mini-icon fa-sharp fa-regular fa-skull"></i>
          <input
            :checked="save"
            :disabled="readOnly"
            @input="updateDeathSaves('failures', i, !save)"
            class="m-none"
            type="checkbox"
            v-for="(save, i) in deathSaves.failures"
          />
        </div>
      </div>
    </div>

    <div class="row mt-sm" style="justify-content: center; flex-wrap: wrap">
      <span class="no-break mb-xs mr-sm">
        <label for="characterConditions" class="meta small muted"
          >Conditions</label
        >
        <field
          id="characterConditions"
          align="left"
          :value="conditions"
          :read-only="readOnly"
          @update-value="updateVitals('conditions', $event)"
          placeholder="None"
        ></field>
      </span>

      <span class="no-break mb-xs">
        <label for="characterConcentration" class="meta small muted"
          >Concentration</label
        >
        <field
          id="characterConcentration"
          align="left"
          :value="concentration"
          :read-only="readOnly"
          @update-value="updateVitals('concentration', $event)"
          placeholder="None"
        ></field>
      </span>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';

export default {
  name: 'Vitals',

  computed: {
    ...mapState([
      'hp',
      'maxHp',
      'tempHp',
      'hitDie',
      'totalHitDie',
      'ac',
      'speed',
      'deathSaves',
      'conditions',
      'concentration',
      'readOnly',
    ]),
  },

  methods: {
    update(item, e) {
      var value = e.target.innerText;
      this[item] = value;
    },

    updateVitals(field, val) {
      this.$store.commit('updateVitals', { field, val });
    },

    updateDeathSaves(key, i, val) {
      this.$store.commit('updateDeathSaves', { key, i, val });
    },
  },

  components: {
    field: Field,
  },
};
</script>
