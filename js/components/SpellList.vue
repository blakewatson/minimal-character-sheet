<template>
    <div>
        <ul>
            <li v-for="(item, i) in spellItems" :key="item.i" class="spell-item row deletable">
                <input type="checkbox" :value="item.prepared" @change="updateSpellPrepared(i, $event)">
                <div class="size-full">
                    <label>Spell name</label>
                    <field
                        class="size-full text-left"
                        :class="{ 'field-focus': item.name === '' }"
                        :value="item.name"
                        placeholder="…"
                        @update-value="updateSpellName(i, $event)"></field>
                </div>
                <div class="size-full">
                    <label>URL</label>
                    <field
                        class="size-full text-left"
                        :class="{ 'field-focus': item.name === '' }"
                        :value="item.url"
                        placeholder="…"
                        @update-value="updateSpellUrl(i, $event)"></field>
                </div>
                <button type="button" class="button" @click="deleteSpell(i)">-</button>
            </li>
        </ul>
        <button type="button" class="button" @click="addSpell">+</button>
    </div>
</template>

<script>
import Vue from 'vue';
import Field from './Field';

export default {
    name: 'SpellList',

    props: ['listField'],

    computed: {
        spellItems() {
            return this.$store.state[this.listField].spells;
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

        updateSpellUrl(i, url) {
            this.$store.commit('updateSpellUrl', {
                field: this.listField,
                i: i,
                url: url
            });
        },

        updateSpellPrepared(i, e) {
            console.log(i, e.target.checked)
            this.$store.commit('updateSpellPrepared', {
                field: this.listField,
                i: i,
                prepared: e.target.checked
            });
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
        }
    },
    
    components: {
        'field': Field
    }
}
</script>
