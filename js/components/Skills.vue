<template>
    <section>
        <p class="label centered">Skills</p>
        <ul class="col-2">
            <li v-for="(skill, i) in skills">
                <label>
                    <input type="checkbox" :checked="skill.proficient" :disabled="readOnly" @change="setProficiency(i)">
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


            if(skill.proficient) return mod + this.proficiencyBonus;
            return mod;
        },

        setProficiency(i) {
            var proficient = !this.skills[i].proficient;
            this.$store.commit('updateSkillProficiency', { i, proficient });
        }
    }
}
</script>
