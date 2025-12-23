<template>
  <div>
    <div
      class="mb-4 grid grid-cols-3 justify-center gap-3 sm:flex sm:items-start sm:justify-between"
    >
      <div
        class="text-reverse bg-reverse border-light-foreground dark:border-dark-foreground rounded-xs border-t text-center"
      >
        <label for="ac-field" class="block text-sm">AC</label>
        <field
          :read-only="readOnly"
          :value="ac"
          @update-value="updateVitals('ac', $event)"
          class="text-reverse! hover:bg-light-foreground! hover:text-dark-accent! focus:text-dark-accent! focus:bg-light-foreground! dark:hover:text-dark-accent! dark:hover:bg-light-foreground! dark:focus:text-dark-accent! text-center text-lg! sm:text-2xl!"
          id="ac-field"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground border-t text-center"
      >
        <label for="hp-field" class="block text-sm">HP</label>
        <field
          :read-only="readOnly"
          :value="hp"
          @update-value="updateVitals('hp', $event)"
          class="text-center text-lg! sm:text-2xl!"
          id="hp-field"
        ></field
        >/<field
          :read-only="readOnly"
          :value="maxHp"
          @update-value="updateVitals('maxHp', $event)"
          class="text-center text-lg! font-bold"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground border-t text-center"
      >
        <label for="temp-hp-field" class="block text-sm">Temp HP</label>
        <field
          id="temp-hp-field"
          class="text-center text-lg! sm:text-2xl!"
          :value="tempHp"
          :read-only="readOnly"
          @update-value="updateVitals('tempHp', $event)"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground border-t text-center"
      >
        <label for="hit-die-field" class="block text-sm">Hit die</label>
        <field
          :read-only="readOnly"
          :value="hitDie"
          @update-value="updateVitals('hitDie', $event)"
          class="text-center text-lg! sm:text-2xl!"
          id="hit-die-field"
        ></field
        >/<field
          :read-only="readOnly"
          :value="totalHitDie"
          @update-value="updateVitals('totalHitDie', $event)"
          class="text-center text-lg!"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground border-t text-center"
      >
        <label for="speed-field" class="block text-sm">Speed</label>
        <field
          :read-only="readOnly"
          :value="speed"
          @update-value="updateVitals('speed', $event)"
          class="text-center text-lg! sm:text-2xl!"
          id="speed-field"
        ></field>
      </div>

      <div
        class="border-light-foreground dark:border-dark-foreground flex flex-col items-center border-t text-center"
      >
        <span class="mb-1 block text-sm">Death saves</span>

        <div class="mb-1 flex gap-1.5">
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

        <div class="flex gap-1.5">
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

    <div class="flex flex-wrap justify-center gap-x-4">
      <span class="mb-1 inline-flex items-baseline gap-1">
        <label for="characterConditions" class="small-label">Conditions</label>
        <field
          :read-only="readOnly"
          :value="conditions"
          @update-value="updateVitals('conditions', $event)"
          id="characterConditions"
          placeholder="None"
        ></field>
      </span>

      <span class="mb-1 inline-flex items-baseline gap-1">
        <label for="characterConcentration" class="small-label">
          Concentration
        </label>
        <field
          :read-only="readOnly"
          :value="concentration"
          @update-value="updateVitals('concentration', $event)"
          id="characterConcentration"
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
