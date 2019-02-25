<template>
    <input
        :class="classAttr"
        :type="optionalValue(type, 'text')"
        :value="value"
        :placeholder="optionalValue(placeholder, '')"
        @input="updateValue">
</template>

<script>
export default {
    name: 'Field',
    props: ['value', 'type', 'align', 'placeholder', 'classNames'],
    computed: {
        classAttr() {
            var align = this.align ? this.align : 'center';
            var value = this.value.toString();
            var placeholder = this.placeholder ? this.placeholder : '';
            var length = value.length > 0 ? value.length : placeholder.length;
            var classNames = this.classNames ? this.classNames : '';

            if(this.type === 'number') {
                classNames += ' field-number';
            }
            
            if(typeof value !== 'string') value = value.toString();
            return `field size-${length} text-${align} ${classNames}`;
        },

        typeValue() {
            if(!this.hasOwnProperty('type') || !this.type) return 'text';
            return this.type;
        }
    },
    methods: {
        updateValue(e) {
            this.$emit('update-value', e.target.value);
        },

        optionalValue(val, defaultVal) {
            return val ? val : defaultVal;
        }
    }
}
</script>
