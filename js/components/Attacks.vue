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
                    <td><field class="size-full text-left" :value="a.name" @update-value="updateAttacks(i, 'name', $event)"></field></td>
                    <td><field type="number" :value="a.attackBonus" @update-value="updateAttacks(i, 'attackBonus', $event)"></field></td>
                    <td><field class="size-full text-left" :value="a.damage" @update-value="updateAttacks(i, 'damage', $event)"></field></td>
                    <td><button type="button" class="button button-delete" @click="deleteAttack(i)">-</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="button" @click="$store.commit('addAttack')">+</button>
    </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';

export default {
    name: 'Attacks',

    computed: {
        ...mapState(['attacks']),
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
