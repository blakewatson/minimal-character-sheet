<template>
  <div class="p-[1.5cqi] [&>*+*]:mt-4" ref="resultList">
    <p :key="result.id" class="my-0 leading-tight" v-for="result in results">
      <span
        :class="{
          'text-light-accent dark:text-dark-accent': isCriticalTotal(result),
        }"
        class="mr-4 block text-xl"
      >
        <span class="sr-only">Result total</span>
        {{ result.total }}
      </span>

      <template v-for="sides in [100, 20, 12, 10, 8, 6, 4]" :key="sides">
        <span class="mr-4" v-if="result[`d${sides}`].length > 0">
          <span class="mr-2">{{
            `d${sides}${hasMultipleDiceTypes(result) ? ':' : ''}`
          }}</span>

          <strong v-if="hasMultipleDiceTypes(result)">{{
            getTotal(result, sides)
          }}</strong>

          <span class="ml-2" v-if="result[`d${sides}`].length > 1"
            >(<template v-for="(die, i) in result[`d${sides}`]" :key="i"
              ><span
                :class="{
                  'text-light-accent dark:text-dark-accent': isMax(die, sides),
                }"
                >{{ die }}</span
              ><span v-if="i < result[`d${sides}`].length - 1"
                >,
              </span></template
            >)</span
          >
        </span>
      </template>
    </p>

    <p class="my-0 leading-tight" v-if="results.length === 0">
      Results will be displayed here.
    </p>
  </div>
</template>

<script setup>
import { nextTick, useTemplateRef, watch } from 'vue';

/** @typedef {import('./DicePanel.vue').Result} Result */

const props = defineProps({
  /** @type {import('vue').PropType<Result[]>} */
  results: {
    type: Array,
    required: true,
  },
});

const container = useTemplateRef('resultList');

// every time a result is added, scroll to the bottom
watch(
  () => props.results,
  () => {
    if (container.value) {
      nextTick(() => {
        container.value.scrollTop = container.value.scrollHeight;
      });
    }
  },
  { deep: true },
);

const getTotal = (result, sides) => {
  return result[`d${sides}`].reduce((sum, num) => sum + num, 0);
};

const isMax = (die, sides) => {
  return die === sides;
};

const isCriticalTotal = (result) => {
  const diceTypes = [100, 20, 12, 10, 8, 6, 4];
  const typesUsed = diceTypes.filter((sides) => result[`d${sides}`].length > 0);
  if (typesUsed.length !== 1) return false;
  const sides = typesUsed[0];
  return result[`d${sides}`].length === 1 && result[`d${sides}`][0] === sides;
};

const hasMultipleDiceTypes = (result) => {
  const diceTypes = [100, 20, 12, 10, 8, 6, 4];
  const typesUsed = diceTypes.filter((sides) => result[`d${sides}`].length > 0);
  return typesUsed.length > 1;
};
</script>
