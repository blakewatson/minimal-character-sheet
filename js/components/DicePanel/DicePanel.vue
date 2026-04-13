<template>
  <div
    :class="{
      ['fixed right-0 bottom-0 max-h-3/4 w-full max-w-[100vw] min-[600px]:right-4 min-[600px]:max-w-97']:
        !maximized,
      ['dice-panel-maximized fixed right-0 bottom-0 max-h-3/4 w-full max-w-[100vw] min-[600px]:right-4 min-[600px]:max-w-97 min-[1000px]:sticky min-[1000px]:top-10 min-[1000px]:right-auto min-[1000px]:bottom-auto min-[1000px]:mt-10 min-[1000px]:max-h-none min-[1000px]:max-w-97']:
        maximized,
    }"
    class="@container flex flex-col overflow-hidden"
  >
    <div
      class="bg-light-background dark:bg-dark-background flex min-h-0 flex-1 flex-col rounded-xs border dark:shadow dark:shadow-black"
    >
      <button
        :aria-expanded="isOpen || maximized"
        :disabled="maximized"
        @click="toggle"
        aria-controls="dice-panel-content"
        class="text-reverse bg-reverse p-[2cqi] text-left leading-none disabled:cursor-default"
        type="button"
      >
        Dice
      </button>

      <div
        v-show="isOpen || maximized"
        id="dice-panel-content"
        class="dice-panel-content flex min-h-0 flex-1 flex-col"
      >
        <result-list
          :results="results"
          class="min-h-0 flex-1 overflow-y-auto"
        ></result-list>

        <div class="shrink-0 p-[1.5cqi]">
          <!-- Buttons for resetting and rolling the dice -->
          <div class="flex gap-[1.5cqi]">
            <button
              :disabled="!hasAnySelectedDice"
              @click="reset"
              class="button w-1/2"
            >
              Reset
            </button>

            <button
              :disabled="!hasAnySelectedDice"
              @click="roll"
              class="button-primary w-1/2"
            >
              Roll
            </button>
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
              class="col-span-2 row-span-2 aspect-auto! gap-4"
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
    </div>

    <button
      :title="maximized ? 'Minimize' : 'Maximize'"
      @click="maximized = !maximized"
      class="text-reverse absolute top-0 right-0 m-0 p-[2cqi] leading-none max-[600px]:hidden"
    >
      <template v-if="maximized">
        <i
          class="fa-sharp fa-regular fa-arrow-down-left-and-arrow-up-right-to-center"
        ></i>
        <span class="sr-only">Maximize</span>
      </template>
      <template v-else>
        <i
          class="fa-sharp fa-regular fa-arrow-up-right-and-arrow-down-left-from-center"
        ></i>
        <span class="sr-only">Minimize</span>
      </template>
    </button>
  </div>
</template>

<script>
import diceStore from '../../RandomStore.js';
import { setDiceMaximized, state } from '../../store';
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
      isOpen: false,
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

  computed: {
    hasAnySelectedDice() {
      return Object.values(this.selected).some((count) => count > 0);
    },

    maximized: {
      get() {
        return state.diceMaximized;
      },
      set(val) {
        setDiceMaximized(val);
      },
    },
  },

  created() {
    const savedOpen = localStorage.getItem('dicePanelOpen');
    if (savedOpen !== null) {
      this.isOpen = savedOpen === 'true';
    }
  },

  watch: {
    maximized(isMaximized) {
      if (isMaximized) {
        this.isOpen = true;
      }
    },

    isOpen(val) {
      localStorage.setItem('dicePanelOpen', val);
    },
  },

  methods: {
    addToSelection(sides) {
      this.selected[`d${sides}`]++;
    },

    toggle() {
      this.isOpen = !this.isOpen;
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

      if (!this.hasAnySelectedDice) {
        return;
      }

      for (const key in this.selected) {
        const sides = parseInt(key.slice(1));
        const count = this.selected[key];

        if (!count) {
          continue;
        }

        for (let i = 0; i < count; i++) {
          const num = diceStore.get(sides);
          result[key].push(num);
          result.total += num;
        }
      }

      this.results.push(result);

      this.reset();
    },
  },

  mounted() {
    window.addEventListener('resize', () => {
      if (window.innerWidth < 1000) {
        this.maximized = false;
      }
    });
  },

  components: {
    'die-button': DieButton,
    'result-list': ResultList,
  },
};
</script>

<style scoped>
@media (min-width: 1000px) {
  .dice-panel-maximized {
    height: calc(100vh - 40px);
  }

  @supports (height: 100dvh) {
    .dice-panel-maximized {
      height: calc(100dvh - 40px);
    }
  }
}
</style>
