<template>
        <section>
            <p class="title"><field align="left" :value="characterName" placeholder="Name" @update-value="updateBio('characterName', $event);"></field></p>
            <p class="meta vert-after">
                <field align="left" :value="race" placeholder="Race" @update-value="updateBio('race', $event);"></field>
                <span class="sep">&middot;</span>
                <field align="left" :value="className" placeholder="Class" @update-value="updateBio('className', $event)"></field>
                <field :value="level" type="number" placeholder="Level" @update-value="updateLevel"></field>
                <span class="sep">&middot;</span>
                <field :value="xp" type="number" placeholder="XP" @update-value="updateBio('xp', $event)"></field> XP
                <span class="sep">&middot;</span>
                <field align="left" :value="alignment" placeholder="Alignment" @update-value="updateBio('alignment', $event)"></field>
            </p>
            
            <vitals></vitals>
        </section>
</template>

<script>
import { mapState } from 'vuex';
import Field from './Field';
import Vitals from './Vitals';

export default {
    name: 'Bio',

    computed: {
        ...mapState(['level','characterName', 'className', 'race', 'alignment', 'xp'])
    },

    methods: {
        updateLevel(level) {
            this.$store.commit('updateLevel', { level: level });
        },

        updateBio(field, val) {
            this.$store.commit('updateBio', { field, val });
        },
    },

    components: {
        'field': Field,
        'vitals': Vitals,
    }
}
</script>
