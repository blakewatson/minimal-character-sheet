<template>
    <div id="sheet" class="sheet">
        <section>
            <p class="title">{{ characterName }}</p>
            <p class="meta vert-after">
                <field align="left" :value="race" placeholder="Race" @update-value="race = $event"></field>
                <span class="sep">&middot;</span>
                <field align="left" :value="className" placeholder="Class" @update-value="className = $event"></field>
                <field :value="level" type="number" placeholder="Level" @update-value="updateLevel"></field>
                <span class="sep">&middot;</span>
                <field :value="xp" type="number" placeholder="XP" @update-value="xp = $event"></field> XP
                <span class="sep">&middot;</span>
                <field align="left" :value="alignment" placeholder="Alignment" @update-value="alignment = $event"></field>
            </p>
            
            <vitals></vitals>
        </section>

        <proficiency></proficiency>
        
        <abilities></abilities>
        
        <skills></skills>

        <attacks></attacks>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';
import Vitals from './Vitals';
import Abilities from './Abilities';
import Skills from './Skills';
import Proficiency from './Proficiency';
import Attacks from './Attacks';

export default {
    name: 'Sheet',

    data() {
        return {
            characterName: 'Constantine',
            race: 'Half-elf',
            className: 'Druid',
            xp: 11000,
            alignment: 'LG'
        };
    },

    computed: {
        ...mapState(['level'])
    },

    methods: {
        updateLevel(level) {
            this.$store.commit('updateLevel', { level: level });
        }
    },

    components: {
        'field': Field,
        'vitals': Vitals,
        'abilities': Abilities,
        'skills': Skills,
        'proficiency': Proficiency,
        'attacks': Attacks
    },

    mounted() {
        window.addEventListener('keydown', event => {
            if(event.key !== 'Enter') return true;
            if(!event.target.hasAttribute('contenteditable')) return true;
            event.preventDefault();
            return false;
        });
    }
}
</script>
