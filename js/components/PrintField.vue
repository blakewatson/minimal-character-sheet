<template>
  <div
    class="print-field"
    :class="{
      'print-field-box': box,
      'text-center': center,
      'print-field-big': big,
    }"
  >
    <slot>
      <p class="print-field-value" :class="{ 'font-bold': bold }">
        {{ signedValue }}
      </p>
    </slot>

    <slot name="label">
      <p class="print-field-label">{{ label }}</p>
    </slot>
  </div>
</template>

<script>
export default {
  name: 'PrintField',
  props: {
    label: String,
    value: [String, Number],
    signedNumber: Boolean,
    big: Boolean,
    bold: Boolean,
    box: Boolean,
    center: Boolean,
  },

  computed: {
    signedValue() {
      if (this.signedNumber && typeof this.value === 'number') {
        return this.value > 0
          ? `+${this.value}`
          : this.value < 0
            ? `-${this.value}`
            : this.value;
      }
      return this.value;
    },
  },
};
</script>
