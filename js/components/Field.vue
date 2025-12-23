<template>
  <input
    :class="this.computedClasses"
    :disabled="isReadOnly"
    :id="id"
    :placeholder="optionalValue(placeholder, '')"
    :style="autoSize ? { width: computedWidth } : {}"
    :type="optionalValue(type, 'text')"
    :value="value"
    @input="updateValue"
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
    id: String,
    placeholder: String,
    readOnly: Boolean,
    reverse: Boolean,
    type: String,
    value: [String, Number],
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
    updateValue(e) {
      this.$emit('update-value', e.target.value);
    },

    optionalValue(val, defaultVal) {
      return val ? val : defaultVal;
    },
  },
};
</script>
