<template>
    <div class="list-field">
        <ul>
            <li v-for="(item, i) in items" :key="item.id" :k="item.id" class="spell-item row deletable">
                <quill-editor :initial-contents="item.val" @quill-text-change="updateItem(i, $event)"></quill-editor>
                <button type="button" class="button button-delete" @click="deleteItem(i)">
                    <span class="sr-only">Delete</span>
                    <span role="presentation">×</span>
                </button>
            </li>
        </ul>
        <button type="button" class="button-add" @click="addToList">
            <span class="sr-only">Add list item</span>
            <span role="presentation">+</span>
        </button>
    </div>
</template>

<script>
import Vue from 'vue';
import { mapState } from 'vuex';
import QuillEditor from './QuillEditor';

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
                val: ''
            });
        },

        deleteItem(i) {
            this.$store.commit('deleteFromListField', {
                field: this.listField,
                i: i
            });

            window.sheetEvent.$emit('autosave', 1);
        }
    },

    components: {
        'quill-editor': QuillEditor
    }
}
</script>
