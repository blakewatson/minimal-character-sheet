<template>
    <section>
        <p class="label centered">Attacks & Weapons</p>
        <table>
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-left">Atk Bonus</th>
                    <th class="text-left">Damage</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="a in attacks">
                    <td><field :value="a.name"></field></td>
                    <td><field type="number" :value="a.attackBonus"></field></td>
                    <td><field :value="a.damage"></field></td>
                </tr>
            </tbody>
        </table>
    </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';

export default {
    name: 'Attacks',

    data() {
        return {
            attacks: [
                { name: 'Quarterstaff', attackBonus: 4, damage: '1d8 + 1 bludgeoning' },
                { name: 'Dagger', attackBonus: 3, damage: '1d4 slashing' },
                { name: 'Shortbow', attackBonus: 3, damage: '1d6 piercing' }
            ]
        };
    },

    computed: {
        ...mapGetters(['modifiers'])
    },

    components: {
        'field': Field
    }
}
</script>
