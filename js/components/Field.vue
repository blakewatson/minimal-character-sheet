<template>
  <input
    :class="{
      'box-content': autoSize,
      'box-border': !autoSize,
      'bg-neutral-100 dark:bg-black': hasBg,
    }"
    :disabled="isReadOnly"
    :id="id"
    :placeholder="placeholder ?? ''"
    :style="autoSize ? { width: computedWidth } : {}"
    :type="type ?? 'text'"
    :value="value"
    @blur="restoreInvalidNumber"
    @input="updateValue"
    ref="input"
    v-bind="{ ...$attrs }"
    class="text-light-foreground hover:text-light-accent focus:text-light-accent dark:text-dark-foreground dark:hover:text-dark-accent dark:focus:text-dark-accent outline-light-accent dark:outline-dark-accent max-w-full rounded-xs border border-transparent px-1 py-0.5 text-[16px] hover:bg-neutral-100 focus:bg-neutral-100 focus:outline-2 sm:text-[15px] dark:hover:border-neutral-700 dark:hover:bg-black dark:focus:bg-black"
  />
</template>

<script>
export default {
  name: 'Field',

  props: {
    autoSize: {
      type: Boolean,
      default: true,
    },
    classNames: String,
    hasBg: Boolean,
    id: String,
    placeholder: String,
    readOnly: Boolean,
    reverse: Boolean,
    // Don't use the number type but enforce numbers.
    type: String,
    value: /** @type {import('vue').PropType<string | number | null>} */ ([
      String,
      Number,
    ]),
  },

  computed: {
    computedClasses() {
      let classes = this.autoSize ? ' box-content' : ' box-border';

      return classes;
    },

    computedWidth() {
      var value = this.value?.toString() || '';
      var placeholder = this.placeholder ? this.placeholder : '';
      var length = value.length > 0 ? value.length : placeholder.length;

      // Minimum width of 1 character
      if (length < 1) length = 1;

      return this.typeValue === 'number'
        ? `calc(${length}ch + 1.5em)`
        : `calc(${length}ch + 0.5em)`;
    },

    isReadOnly() {
      return Boolean(this.readOnly);
    },

    typeValue() {
      if (!this.type) return 'text';
      return this.type;
    },
  },

  methods: {
    /** @param {InputEvent} event */
    updateValue(event) {
      const input = /** @type {HTMLInputElement} */ (event.currentTarget);

      if (this.type !== 'number') {
        this.$emit('update-value', input.value);
        return;
      }

      const value = input.valueAsNumber;

      // Allows temporary editing states such as "-" or an empty field.
      if (!Number.isFinite(value)) {
        return;
      }

      this.$emit('update-value', value);
    },

    /** @param {FocusEvent} event */
    restoreInvalidNumber(event) {
      if (this.type !== 'number') {
        return;
      }

      const input = /** @type {HTMLInputElement} */ (event.currentTarget);

      if (!Number.isFinite(input.valueAsNumber)) {
        input.value = this.value == null ? '' : String(this.value);
      }
    },
  },
};
</script>
