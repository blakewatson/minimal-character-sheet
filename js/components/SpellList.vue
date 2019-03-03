<template>
    <ul>
        <li v-for="(item, i) in spellItems" class="spell-item row">
            <input type="checkbox" :value="item.prepared" @change="updateSpellPrepared(i, $event)">
            <field
                class="size-full text-left"
                :class="{ 'field-focus': item.name === '' }"
                :value="item.name"
                placeholder="…"
                @update-value="updateSpellName(i, $event)"></field>
        </li>
    </ul>
</template>

<script>
import Vue from 'vue';
import Field from './Field';

export default {
    name: 'SpellList',

    data() {
        return {
            spellItems: [{ name: '', prepared: false }]
        };
    },

    methods: {
        updateSpellName(i, value) {
            Vue.set(this.spellItems[i], 'name', value);

            var itemsLength = this.spellItems.length;
            if(this.spellItems[itemsLength - 1].name !== '') {
                this.spellItems.push({ name: '', prepared: false });
            }
        },

        updateSpellPrepared(i, e) {
            Vue.set(this.spellItems[i], 'prepared', e.target.checked);
        }
    },
    
    components: {
        'field': Field
    }
}
</script>
