<template>
    <section>
        <div class="row vert-after">
            <ability v-for="(a, i) in abilities" :ability="a" :modifier="modifiers[i].val" :key="a.name"></ability>
        </div>
        <div class="">
            <div class="row">
                <saving-throw v-for="(s, i) in savingThrows" :savingThrow="s" :modifier="modifiers[i].val" :key="s.name"></saving-throw>
            </div>
            <span class="centered label">Saving throws</span>
        </div>
    </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Ability from './Ability';
import SavingThrow from './SavingThrow';

export default {
    name: 'Abilities',

    computed: {
        ...mapState(['abilities', 'savingThrows']),
        ...mapGetters(['modifiers'])
    },

    components: {
        'ability': Ability,
        'saving-throw': SavingThrow
    }
}
</script>
