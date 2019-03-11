<template>
    <div class="box ability">
		<span class="centered label reverse">{{ ability.name }}</span>
		<span class="centered huge block padded">{{ modifier | signedNumString }}</span>
		<field class="centered strong block" :value="ability.score" @update-value="updateScore($event)"></field>
	</div>
</template>

<script>
import Field from './Field';

export default {
    name: 'Ability',

    props: ['ability', 'modifier'],
    
	methods: {
		updateScore(val) {
			var score = parseInt(val);
			this.$store.commit('updateAbilityScore', {
                name: this.ability.name,
                score: score
            });
		}
	},

	components: {
		'field': Field
	}
}
</script>
