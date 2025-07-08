<template>
    <section>
        <div class="abilities row">
            <ability v-for="(a, i) in abilities" :ability="a" :modifier="modifiers[i].val" :key="a.name"></ability>
        </div>
    </section>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Ability from './Ability';

export default {
    name: 'Abilities',

    computed: {
        ...mapState(['abilities']),
        ...mapGetters(['modifiers'])
    },

    components: {
        'ability': Ability
    }
}
</script>
