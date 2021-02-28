<template>
    <div class="spell-list">
        <ul>
            <li v-for="(item, i) in spellItems" :key="item.id" class="spell-item row deletable">
                <input type="checkbox" :checked="item.prepared" :disabled="readOnly" @change="updateSpellPrepared(i, $event)">
                <div class="size-full">
                    <label class="sr-only">Spell name and description</label>
                    <quill-editor :initial-contents="item.name" :read-only="readOnly" @quill-text-change="updateSpellName(i, $event)"></quill-editor>
                </div>
                <button v-if="!readOnly" type="button" class="button button-delete" :disabled="readOnly" @click="deleteSpell(i)">
                    <span class="sr-only">Delete</span>
                    <span role="presentation">×</span>
                </button>
            </li>
        </ul>
        <p class="text-center" v-if="!readOnly">
            <button type="button" class="button-add" :disabled="readOnly" @click="addSpell">
                <span class="sr-only">Add list item</span>
                <span role="presentation">+</span>
            </button>
        </p>
    </div>
</template>

<script>
import Vue from 'vue';
import QuillEditor from './QuillEditor';

export default {
    name: 'SpellList',

    props: ['listField', 'readOnly'],

    computed: {
        spellItems() {
            return this.$store.state[this.listField].spells.map(spell => {
                spell.id = Math.random().toString();
                return spell;
            });
        }
    },

    methods: {
        updateSpellName(i, name) {
            this.$store.commit('updateSpellName', {
                field: this.listField,
                i: i,
                name: name
            });
        },

        updateSpellPrepared(i, e) {
            this.$store.commit('updateSpellPrepared', {
                field: this.listField,
                i: i,
                prepared: e.target.checked
            });

            window.sheetEvent.$emit('autosave', 1);
        },

        addSpell() {
            this.$store.commit('addSpell', {
                field: this.listField,
                item: { prepared: false, name: '', url: '', id: Date.now() }
            });
        },

        deleteSpell(i) {
            this.$store.commit('deleteSpell', {
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
