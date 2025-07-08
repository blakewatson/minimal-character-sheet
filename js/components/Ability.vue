<template>
	<div class="box ability">
		<span class="centered label reverse">{{ ability.name }}</span>
		<span class="centered huge block padded py-none">{{ modifier | signedNumString }}</span>
		<field class="centered strong block" :value="ability.score" :read-only="readOnly" @update-value="updateScore($event)" type="number"></field>
		<div class="save-section mt-sm">
			<label :for="inputId" class="centered label" style="display: flex; align-items: center; gap: 0.125em;">
				Save
				<input v-if="savingThrow" type="checkbox" :id="inputId" :checked="savingThrow.proficient" :disabled="readOnly" @change="toggleProficiency" />
			</label>
			<div class="centered normal">{{ saveBonus | signedNumString }}</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Field from './Field';

export default {
    name: 'Ability',

    props: ['ability', 'modifier'],
    
    computed: {
        ...mapState(['readOnly', 'savingThrows']),
        ...mapGetters(['proficiencyBonus']),
        
        savingThrow() {
            return this.savingThrows.find(st => st.name === this.ability.name);
        },
        
        saveBonus() {
            if(!this.savingThrow) return 0;
            if(this.savingThrow.proficient) return this.modifier + this.proficiencyBonus;
            return this.modifier;
        },
        
        inputId() {
            return `${this.ability.name}-saving-throw`;
        }
    },
    
	methods: {
		updateScore(val) {
			var score = parseInt(val);
			this.$store.commit('updateAbilityScore', {
					name: this.ability.name,
					score: score
			});
		},
		
		toggleProficiency() {
			if(!this.savingThrow) return;
			this.$store.commit('updateSavingThrow', {
				name: this.savingThrow.name,
				proficient: !this.savingThrow.proficient
			});
		}
	},

	components: {
		'field': Field
	}
}
</script>
