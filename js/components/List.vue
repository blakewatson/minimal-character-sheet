<template>
    <div>
        <ul>
            <li v-for="(item, i) in items" class="spell-item row deletable">
                <field
                    class="size-full text-left"
                    :class="{ 'field-focus': item === '' }"
                    :value="item"
                    placeholder="…"
                    @update-value="updateItem(i, $event)"></field>
                <button type="button" class="button" @click="deleteItem(i)">-</button>
            </li>
        </ul>
        <button type="button" class="button" @click="addToList">+</button>
    </div>
</template>

<script>
import Vue from 'vue';
import { mapState } from 'vuex';
import Field from './Field';

export default {
    name: 'List',

    props: ['listField'],

    computed: {
        items() {
            return this.$store.state[this.listField];
        }
    },

    methods: {
        updateItem(i, val) {
            this.$store.commit('updateListField', {
                field: this.listField,
                i: i,
                val: val
            });
        },

        addToList() {
            this.$store.commit('addToListField', {
                field: this.listField,
                item: ''
            });
        },

        deleteItem(i) {
            this.$store.commit('deleteFromListField', {
                field: this.listField,
                item: i
            });
        }
    },

    components: {
        'field': Field
    }
}
</script>
