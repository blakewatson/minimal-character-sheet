<template>
  <dialog
    class="bg-light-background dark:bg-dark-background max-w-90% fixed top-1/2 left-1/2 w-160 -translate-1/2 rounded border border-neutral-300 p-4 shadow-lg dark:border-neutral-600 dark:backdrop:bg-[rgba(0,0,0,0.5)]"
    v-bind="{ ...$attrs }"
  >
    <slot>
      <form @submit.prevent="submitForm">
        <h2 class="mb-2 text-lg font-bold">{{ title }}</h2>

        <slot name="content"></slot>

        <div class="mt-4">
          <slot name="actions"></slot>

          <button type="button" @click="closeDialog" class="button">
            {{ closeLabel }}
          </button>
        </div>
      </form>
    </slot>
  </dialog>
</template>

<script>
export default {
  name: 'AppDialog',

  props: {
    closeLabel: {
      type: String,
      default: 'Close',
    },
    open: {
      type: Boolean,
      default: false,
    },
    title: String,
  },

  methods: {
    closeDialog() {
      this.$el.close();
      this.$emit('close');
    },

    submitForm() {
      this.$emit('submit');
    },
  },

  mounted() {
    this.$el.showModal();
  },
};
</script>
