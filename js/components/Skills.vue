<template>
    <section>
        <p class="label centered">Skills</p>
        <ul class="col-2">
            <li v-for="(skill, i) in skills">
                <label>
                    <input type="checkbox" :checked="skill.proficient" @change="setProficiency(i)">
                    <strong class="skill-modifier">{{ getSkillModifier(skill) | signedNumString }}</strong>
                    {{ skill.name }} <span class="small muted">({{ skill.ability }})</span>
                </label>
            </li>
        </ul>
        <p class="centered">
            <br>
            <strong class="skill-modifier">{{ getSkillModifier({ability:'WIS'}) | signedNumString }}</strong>
            Passive Wisdom <span class="small muted">(WIS)</span>
        </p>
    </section>
</template>

<script>
import Vue from 'vue';
import { mapState, mapGetters } from 'vuex';

export default {
    name: 'Skills',

    data() {
        return {
            skills: [
                { name: 'Acrobatics', ability: 'DEX', proficient: false },
                { name: 'Animal Handling', ability: 'WIS', proficient: true },
                { name: 'Arcana', ability: 'INT', proficient: false },
                { name: 'Athletics', ability: 'STR', proficient: false },
                { name: 'Deception', ability: 'CHA', proficient: false },
                { name: 'History', ability: 'INT', proficient: false },
                { name: 'Insight', ability: 'WIS', proficient: false },
                { name: 'Intimidation', ability: 'CHA', proficient: false },
                { name: 'Investigation', ability: 'INT', proficient: false },
                { name: 'Medicine', ability: 'WIS', proficient: false },
                { name: 'Nature', ability: 'INT', proficient: false },
                { name: 'Perception', ability: 'WIS', proficient: false },
                { name: 'Performance', ability: 'CHA', proficient: false },
                { name: 'Persuasion', ability: 'CHA', proficient: false },
                { name: 'Religion', ability: 'INT', proficient: false },
                { name: 'Sleight of Hand', ability: 'DEX', proficient: false },
                { name: 'Stealth', ability: 'DEX', proficient: false },
                { name: 'Survival', ability: 'WIS', proficient: false }
            ]
        };
    },

    computed: {
        ...mapGetters(['modifiers', 'proficiencyBonus'])
    },

    methods: {
        getSkillModifier(skill) {
            var mod = this.modifiers.reduce((acc, m) => {
                if(m.ability === skill.ability) return acc + m.val;
                return acc;
            }, 0);


            if(skill.proficient) return mod + this.proficiencyBonus;
            return mod;
        },

        setProficiency(i) {
            Vue.set(this.skills[i], 'proficient', !this.skills[i].proficient);
        }
    }
}
</script>
