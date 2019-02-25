<template>
    <div id="sheet" class="sheet">
        <bio></bio>

        <proficiency></proficiency>
        
        <abilities></abilities>
        
        <skills></skills>

        <attacks></attacks>

        <text-section title="Equipment"></text-section>

        <text-section title="Other Proficiencies & Languages"></text-section>

        <text-section title="Feature & Traits"></text-section>

        <text-section title="Character Backstory"></text-section>

        <text-section title="Treasure"></text-section>

        <text-section title="Alies & Organizations"></text-section>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Bio from './Bio';
import Abilities from './Abilities';
import Skills from './Skills';
import Proficiency from './Proficiency';
import Attacks from './Attacks';
import TextSection from './TextSection';

export default {
    name: 'Sheet',

    components: {
        'bio': Bio,
        'abilities': Abilities,
        'skills': Skills,
        'proficiency': Proficiency,
        'attacks': Attacks,
        'text-section': TextSection
    }
}
</script>
