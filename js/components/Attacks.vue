<template>
    <section>
        <p class="label centered">Attacks & Weapons</p>
        <table v-show="attacks.length > 0">
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-left">Atk Bonus</th>
                    <th class="text-left">Damage</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(a, i) in attacks" :key="a.id" class="attack deletable">
                    <td><field class="size-full text-left" :value="a.name" :read-only="readOnly" @update-value="updateAttacks(i, 'name', $event)"></field></td>
                    <td class="text-center"><field :value="a.attackBonus" :read-only="readOnly" @update-value="updateAttacks(i, 'attackBonus', $event)"></field></td>
                    <td><field class="size-full text-left" :value="a.damage" :read-only="readOnly" @update-value="updateAttacks(i, 'damage', $event)"></field></td>
                    <td>
                        <button v-if="!readOnly" type="button" class="button button-delete" :disabled="readOnly" @click="deleteAttack(i)">
                            <span class="sr-only">Delete attack</span>
                            <span role="presentation">&times;</span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="text-center" v-if="!readOnly">
            <button type="button" class="button button-add" :disabled="readOnly" @click="$store.commit('addAttack')">
                <span class="sr-only">Add an attack</span>
                <span role="presentation">+</span>
            </button>
        </p>
    </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';

export default {
    name: 'Attacks',

    computed: {
        ...mapState(['attacks', 'readOnly']),
        ...mapGetters(['modifiers'])
    },

    methods: {
        updateAttacks(i, field, val) {
            this.$store.commit('updateAttacks', { i, field, val });
        },

        deleteAttack(i) {
            this.$store.commit('deleteAttack', { i });
        }
    },

    components: {
        'field': Field
    }
}
</script>
