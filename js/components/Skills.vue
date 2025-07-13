<template>
    <details open class="section">
        <summary class="label centered">Skills</summary>
        <ul class="skills-list">
            <li v-for="(skill, i) in skills">
                <label class="sr-only" :for="`double-prof-${i}`">Double proficiency</label>

                <input 
                    type="checkbox"
                    title="Toggle double proficiency"
                    :id="`double-prof-${i}`"
                    :style="{
                        opacity: skill.proficient ? 1 : 0,
                        pointerEvents: skill.proficient ? 'auto' : 'none'
                    }"
                    :checked="skill.doubleProficient" 
                    :disabled="readOnly"
                    @change="setProficiency(i, 'doubleProficient')"
                ><label title="Toggle proficiency">
                    <input
                        type="checkbox"
                        :checked="skill.proficient"
                        :disabled="readOnly || skill.doubleProficient"
                        @change="setProficiency(i, 'proficient')"
                    >
                    <strong class="skill-modifier">{{ getSkillModifier(skill) | signedNumString }}</strong>
                    {{ skill.name }} <span class="small muted">({{ skill.ability }})</span>
                </label>
            </li>
        </ul>
        <p class="centered">
            <br>
            <strong class="skill-modifier">{{ getPassivePerception() }}</strong>
            Passive Perception <span class="small muted">(WIS)</span>
        </p>
    </details>
</template>

<script>
import { mapState, mapGetters } from 'vuex';

export default {
    name: 'Skills',

    computed: {
        ...mapState(['skills', 'readOnly']),
        ...mapGetters(['modifiers', 'proficiencyBonus'])
    },

    methods: {
        getSkillModifier(skill) {
            var mod = this.modifiers.reduce((acc, m) => {
                if(m.ability === skill.ability) return acc + m.val;
                return acc;
            }, 0);

            if(skill.doubleProficient) {
                return mod + (this.proficiencyBonus * 2);
            }

            if(skill.proficient) {
                return mod + this.proficiencyBonus;
            }

            return mod;
        },

        setProficiency(i, prop) {
            var proficient = this.skills[i].proficient;
            var doubleProficient = this.skills[i].doubleProficient;

            if (prop === 'proficient') {
                proficient = !proficient;
            } else if (prop === 'doubleProficient') {
                doubleProficient = !doubleProficient;
            }

            this.$store.commit('updateSkillProficiency', { i, proficient, doubleProficient });
        },

        getPassivePerception() {
            // Find the Perception skill
            const perceptionSkill = this.skills.find(skill => skill.name === 'Perception');
            if (perceptionSkill) {
                return 10 + this.getSkillModifier(perceptionSkill);
            }
            // Fallback to just Wisdom modifier if Perception skill not found
            return 10 + this.getSkillModifier({ability:'WIS'});
        }
    }
}
</script>
