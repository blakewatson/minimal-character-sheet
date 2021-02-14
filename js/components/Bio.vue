<template>
        <section class="bio">
            <p class="title"><field align="left" :value="characterName" placeholder="Name" @update-value="updateBio('characterName', $event);"></field></p>
            <p class="meta vert-after">
                <label for="characterRace" class="small muted sr-only">Race</label>
                <field id="characterRace" align="left" :value="race" placeholder="Race" @update-value="updateBio('race', $event);"></field>
                <span class="sep">&middot;</span>
                <label for="characterClass" class="small muted sr-only">Class</label>
                <field id="characterClass" align="left" :value="className" placeholder="Class" @update-value="updateBio('className', $event)"></field>
                <br>
                <label for="characterLevel" class="small muted">Level</label>
                <field id="characterLevel" :value="level" type="number" min="1" @update-value="updateLevel"></field>
                <span class="sep">&middot;</span>
                <label for="characterXp" class="small muted">XP</label>
                <field id="characterXp" :value="xp" type="number" @update-value="updateBio('xp', $event)"></field>
                <span class="sep">&middot;</span>
                <label for="characterAlignment" class="small muted sr-only">Alignment</label>
                <field id="characterAlignment" align="left" :value="alignment" placeholder="Alignment" @update-value="updateBio('alignment', $event)"></field>
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
            this.$store.commit('updateLevel', { level: parseInt(level) });
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
