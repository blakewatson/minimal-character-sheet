<template>
  <input
    :disabled="isReadOnly"
    :id="id"
    :placeholder="optionalValue(placeholder, '')"
    :style="autoSize ? { width: computedWidth } : {}"
    :type="optionalValue(type, 'text')"
    :value="value"
    @input="updateValue"
    v-bind="{ ...$attrs }"
    :class="this.computedClasses"
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
    type: String,
    value: [String, Number],
  },
  computed: {
    computedClasses() {
      let classes =
        'text-base px-1 py-0.5 text-neutral-800 hover:bg-neutral-100 hover:text-blue-700 focus:bg-neutral-100 focus:text-blue-700';

      classes += this.autoSize ? ' box-content' : ' box-border';

      return classes;
    },

    computedWidth() {
      var value = this.value?.toString() || '';
      var placeholder = this.placeholder ? this.placeholder : '';
      var length = value.length > 0 ? value.length : placeholder.length;

      // Minimum width of 1 character
      if (length < 1) length = 1;

      return this.typeValue === 'number'
        ? `calc(${length}ch + 1.75em)`
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
