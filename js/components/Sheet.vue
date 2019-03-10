<template>
    <div id="sheet" class="sheet">
        <tabs @update-view="view = $event"></tabs>

        <bio></bio>

        <div class="page" v-show="view === 'main'">
            <proficiency></proficiency>
            
            <abilities></abilities>
            
            <skills></skills>

            <attacks></attacks>

            <equipment></equipment>

            <text-section title="Other Proficiencies & Languages" field="proficienciesText"></text-section>

            <text-section title="Feature & Traits" field="featuresText"></text-section>
        </div>

        <div class="page" v-show="view === 'spells'">
            <spells></spells>
        </div>

        <div class="page" v-show="view === 'details'">
            <text-section title="Character Backstory" field="backstoryText"></text-section>
    
            <text-section title="Treasure" field="treasureText"></text-section>
    
            <text-section title="Allies & Organizations" field="organizationsText"></text-section>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Tabs from './Tabs';
import Bio from './Bio';
import Abilities from './Abilities';
import Skills from './Skills';
import Proficiency from './Proficiency';
import Attacks from './Attacks';
import Equipment from './Equipment';
import Spells from './Spells';
import TextSection from './TextSection';

export default {
    name: 'Sheet',

    data() {
        return {
            'view': 'main'
        };
    },

    components: {
        'tabs': Tabs,
        'bio': Bio,
        'abilities': Abilities,
        'skills': Skills,
        'proficiency': Proficiency,
        'attacks': Attacks,
        'equipment': Equipment,
        'spells': Spells,
        'text-section': TextSection
    }
}
</script>
