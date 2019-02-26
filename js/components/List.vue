<template>
    <ul>
        <li v-for="(item, i) in items" class="spell-item">
            <field
                class="block size-full text-left"
                :class="{ 'field-focus': item === '' }"
                :value="item"
                @update-value="updateItem(i, $event)"></field>
        </li>
    </ul>
</template>

<script>
import Vue from 'vue';
import Field from './Field';

export default {
    name: 'List',

    data() {
        return {
            items: ['']
        };
    },

    methods: {
        updateItem(i, value) {
            console.log(i, value);
            Vue.set(this.items, i, value);

            var itemsLength = this.items.length;
            if(this.items[itemsLength - 1] !== '') {
                this.items.push('');
            }
        }
    },

    components: {
        'field': Field
    }
}
</script>
