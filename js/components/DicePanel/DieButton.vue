<template>
  <button
    class="dark:hover:text-dark-accent hover:text-light-accent relative flex aspect-square appearance-none flex-col items-center justify-center gap-2 rounded-xs border p-2 hover:bg-neutral-100 max-[350px]:p-1 dark:hover:bg-black"
  >
    <slot name="icon">
      <i
        :class="`fa-light fa-sharp fa-dice-d${sides} text-3xl max-[390px]:text-xl`"
      ></i>
    </slot>

    <slot name="label">
      <span class="text-sm leading-none max-[400px]:text-xs">d{{ sides }}</span>
    </slot>

    <span
      v-if="count > 0"
      class="bg-light-accent absolute top-0 right-0 z-10 translate-x-1/4 -translate-y-1/4 rounded-full px-1.5 py-1 text-xs leading-none text-white"
    >
      {{ count }} <span class="sr-only">selected</span>
    </span>
  </button>
</template>

<script>
export default {
  name: 'DieButton',

  props: {
    count: {
      type: Number,
      default: 1,
    },
    sides: {
      type: Number,
      required: true,
    },
  },
};
</script>
