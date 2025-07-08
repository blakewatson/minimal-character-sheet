<template>
    <details open class="section">
        <summary class="label centered">Attacks & Weapons</summary>
        
        <!-- Desktop Table Layout -->
        <table v-show="attacks.length > 0 && !isMobile" class="attacks-table">
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-left">Atk Bonus</th>
                    <th class="text-left">Damage</th>
                    <th class="text-left">Notes</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(a, i) in attacks" :key="a.id" :style="{ 'z-index': attacks.length - i }" class="attack deletable">
                    <td><field class="size-full small text-left" :value="a.name" :read-only="readOnly" @update-value="updateAttacks(i, 'name', $event)"></field></td>
                    <td class="text-center small"><field :value="a.attackBonus" :read-only="readOnly" @update-value="updateAttacks(i, 'attackBonus', $event)"></field></td>
                    <td><field class="size-full small text-left" :value="a.damage" :read-only="readOnly" @update-value="updateAttacks(i, 'damage', $event)"></field></td>
                    <td style="width: 200px;">
                        <quill-editor 
                            :initial-contents="a.weaponNotes" 
                            :read-only="readOnly" 
                            :toolbar-options="['bold', 'italic', 'strike', 'link']"
                            @quill-text-change="updateAttacks(i, 'weaponNotes', $event)"
                            style="width: 100%;"
                        ></quill-editor>
                    </td>
                    <td>
                        <button v-if="!readOnly" type="button" class="button button-delete" :disabled="readOnly" @click="deleteAttack(i)">
                            <span class="sr-only">Delete attack</span>
                            <span role="presentation">&times;</span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Mobile Card Layout -->
        <div v-show="attacks.length > 0 && isMobile" class="attacks-mobile">
            <div v-for="(a, i) in attacks" :key="a.id" class="attack-card">
                <div class="attack-header">
                    <field class="attack-name" :value="a.name" :read-only="readOnly" @update-value="updateAttacks(i, 'name', $event)" placeholder="Weapon name"></field>
                    <button v-if="!readOnly" type="button" class="button button-delete" :disabled="readOnly" @click="deleteAttack(i)">
                        <span class="sr-only">Delete attack</span>
                        <span role="presentation">&times;</span>
                    </button>
                </div>
                <div class="attack-stats">
                    <div class="stat-group">
                        <label class="label" :for="`attack-bonus-${a.id}`">Attack Bonus</label>
                        <field :id="`attack-bonus-${a.id}`" class="stat-value" :value="a.attackBonus" :read-only="readOnly" @update-value="updateAttacks(i, 'attackBonus', $event)"></field>
                    </div>
                    <div class="stat-group">
                        <label class="label" :for="`attack-damage-${a.id}`">Damage</label>
                        <field :id="`attack-damage-${a.id}`" class="stat-value" :value="a.damage" :read-only="readOnly" @update-value="updateAttacks(i, 'damage', $event)"></field>
                    </div>
                </div>
                <div class="attack-notes">
                    <label class="label">Notes</label>
                    <quill-editor 
                        :initial-contents="a.weaponNotes" 
                        :read-only="readOnly" 
                        :toolbar-options="['bold', 'italic', 'strike', 'link']"
                        @quill-text-change="updateAttacks(i, 'weaponNotes', $event)"
                    ></quill-editor>
                </div>
            </div>
        </div>

        <p class="text-center" v-if="!readOnly">
            <button type="button" class="button button-add" :disabled="readOnly" @click="$store.commit('addAttack')">
                <span class="sr-only">Add an attack</span>
                <span role="presentation">+</span>
            </button>
        </p>
    </details>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';
import QuillEditor from './QuillEditor';

export default {
    name: 'Attacks',

    data() {
        return {
            isMobile: false,
            mediaQuery: null
        };
    },

    computed: {
        ...mapState(['attacks', 'readOnly']),
        ...mapGetters(['modifiers'])
    },

    mounted() {
        this.setupMediaQuery();
    },

    beforeDestroy() {
        if (this.mediaQuery) {
            this.mediaQuery.removeListener(this.handleMediaQueryChange);
        }
    },

    methods: {
        setupMediaQuery() {
            this.mediaQuery = window.matchMedia('(max-width: 675px)');
            this.isMobile = this.mediaQuery.matches;
            this.mediaQuery.addListener(this.handleMediaQueryChange);
        },

        handleMediaQueryChange(event) {
            this.isMobile = event.matches;
        },

        updateAttacks(i, field, val) {
            this.$store.commit('updateAttacks', { i, field, val });
        },

        deleteAttack(i) {
            this.$store.commit('deleteAttack', { i });
        }
    },

    components: {
        'field': Field,
        'quill-editor': QuillEditor
    }
}
</script>
