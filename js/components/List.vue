<template>
    <div class="list-field">
        <ul>
            <li v-for="(item, i) in items" :key="item.id" :k="item.id" class="list-item deletable">
                <quill-editor :initial-contents="item.val" :read-only="readOnly" @quill-text-change="updateItem(i, $event)"></quill-editor>
                
                <div class="mt-sm" style="display: flex; justify-content: flex-end; gap: 0.25rem;">
                    <button v-if="!readOnly && i > 0" type="button" class="button button-sort" :disabled="readOnly" @click="sortItems(item.id, 'up')">
                        <span class="sr-only">Move up</span>
                        <span role="presentation">&uarr;</span>
                    </button>

                    <button v-if="!readOnly && i < items.length - 1" type="button" class="button button-sort" :disabled="readOnly" @click="sortItems(item.id, 'down')">
                        <span class="sr-only">Move down</span>
                        <span role="presentation">&darr;</span>
                    </button>

                    <button v-if="!readOnly" type="button" class="button button-delete" :disabled="readOnly" @click="deleteItem(i)">
                        <span class="sr-only">Delete</span>
                        <span role="presentation">×</span>
                    </button>
                </div>
            </li>
        </ul>
        <p class="text-center" v-if="!readOnly">
            <button type="button" class="button-add" :disabled="readOnly" @click="addToList">
                <span class="sr-only">Add list item</span>
                <span role="presentation">+</span>
            </button>
        </p>
    </div>
</template>

<script>
import QuillEditor from './QuillEditor';

export default {
    name: 'List',

    props: ['listField', 'readOnly'],

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
        },

        sortItems(id, direction) {
            this.$store.commit('sortListField', {
                field: this.listField,
                id,
                direction
            });
        }
    },

    components: {
        'quill-editor': QuillEditor
    }
}
</script>
