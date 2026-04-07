<template>
  <details
    class="bg-light-background dark:bg-dark-background @container fixed right-0 bottom-0 max-h-3/4 w-full max-w-[100vw] rounded-xs border min-[600px]:right-4 min-[600px]:max-w-97 dark:shadow dark:shadow-black"
  >
    <summary
      class="text-reverse bg-reverse p-[2cqi] leading-none marker:content-none"
    >
      Dice
    </summary>

    <div class="flex max-h-[calc(75vh-2lh)] flex-col">
      <result-list
        :results="results"
        class="min-h-0 flex-1 overflow-y-auto"
      ></result-list>

      <div class="shrink-0 p-[1.5cqi]">
        <!-- Buttons for resetting and rolling the dice -->
        <div class="flex gap-[1.5cqi]">
          <button @click="reset" class="button w-1/2">Reset</button>
          <button @click="roll" class="button-primary w-1/2">Roll</button>
        </div>

        <!-- Dice tray -->
        <div class="mt-[1.5cqi] grid grid-cols-5 gap-[1.5cqi]">
          <die-button
            :count="selected['d4']"
            :sides="4"
            @click="addToSelection(4)"
          ></die-button>

          <die-button
            :count="selected['d6']"
            :sides="6"
            @click="addToSelection(6)"
          ></die-button>

          <die-button
            :count="selected['d8']"
            :sides="8"
            @click="addToSelection(8)"
          ></die-button>

          <die-button
            :count="selected['d20']"
            :sides="20"
            @click="addToSelection(20)"
            class="col-span-2 row-span-2 gap-4"
          >
            <template #icon>
              <i
                class="fa-thin fa-sharp fa-dice-d20 text-7xl max-[390px]:text-5xl"
              ></i>
            </template>
          </die-button>

          <die-button
            :count="selected['d10']"
            :sides="10"
            @click="addToSelection(10)"
          ></die-button>

          <die-button
            :count="selected['d12']"
            :sides="12"
            @click="addToSelection(12)"
          ></die-button>

          <die-button
            :count="selected['d100']"
            :sides="100"
            @click="addToSelection(100)"
            class="relative"
          >
            <template #icon>
              <i
                class="fa-light fa-sharp fa-dice-d10 text-3xl opacity-0 max-[390px]:text-xl"
              ></i>
              <i
                class="fa-light fa-sharp fa-dice-d10 absolute top-1/2 left-1/2 -translate-x-[calc(50%+10px)] -translate-y-[calc(50%+10px-4px)] text-2xl max-[390px]:text-lg"
              ></i>
              <i
                class="fa-light fa-sharp fa-dice-d10 absolute top-1/2 left-1/2 -translate-x-[calc(50%-10px)] -translate-y-[calc(50%+10px+4px)] text-2xl max-[390px]:text-lg"
              ></i>
            </template>
          </die-button>
        </div>
      </div>
    </div>
  </details>
</template>

<script>
import diceStore from '../../RandomStore.js';
import DieButton from './DieButton.vue';
import ResultList from './ResultList.vue';

/** @typedef {Object} Hand
 * @property {number} d4
 * @property {number} d6
 * @property {number} d8
 * @property {number} d10
 * @property {number} d12
 * @property {number} d20
 * @property {number} d100
 */

/** @typedef {Object} Result
 * @property {number[]} d4
 * @property {number[]} d6
 * @property {number[]} d8
 * @property {number[]} d10
 * @property {number[]} d12
 * @property {number[]} d20
 * @property {number[]} d100
 * @property {string} id
 * @property {number} total
 */

export default {
  name: 'DicePanel',

  data() {
    return {
      /** @type {Result[]} */
      results: [],
      /** @type {Hand} */
      selected: {
        d4: 0,
        d6: 0,
        d8: 0,
        d10: 0,
        d12: 0,
        d20: 0,
        d100: 0,
      },
    };
  },

  methods: {
    addToSelection(sides) {
      this.selected[`d${sides}`]++;
    },

    reset() {
      for (const key in this.selected) {
        this.selected[key] = 0;
      }
    },

    roll() {
      /** @type {Result} */
      const result = {
        id: crypto.randomUUID(),
        total: 0,
        d4: [],
        d6: [],
        d8: [],
        d10: [],
        d12: [],
        d20: [],
        d100: [],
      };

      let hasAnyDice = false;

      for (const key in this.selected) {
        const sides = parseInt(key.slice(1));
        const count = this.selected[key];

        if (!count) {
          continue;
        }

        hasAnyDice = true;

        for (let i = 0; i < count; i++) {
          const num = diceStore.get(sides);
          result[key].push(num);
          result.total += num;
        }
      }

      if (!hasAnyDice) {
        return;
      }

      this.results.push(result);

      this.reset();
    },
  },

  components: {
    'die-button': DieButton,
    'result-list': ResultList,
  },
};
</script>
