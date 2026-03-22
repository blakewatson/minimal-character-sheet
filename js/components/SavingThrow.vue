<template>
    <div class="saving-throw">
        <label :for="inputId" class="centered huge padded block">
            {{ $signedNumString(saveBonus) }}
            <input v-if="savingThrow" type="checkbox" :id="inputId" :checked="savingThrow.proficient" :disabled="readOnly" @change="toggleProficiency" />
        </label>
    </div>
</template>

<script>
import { state, proficiencyBonus as storeProficiencyBonus, updateSavingThrow } from '../store';

export default {
    name: 'SavingThrow',
    props: ['savingThrow', 'modifier'],

    computed: {
        proficiencyBonus() { return storeProficiencyBonus.value; },
        readOnly() { return state.readOnly; },

        saveBonus() {
            if(!this.savingThrow) return 0;
            if(this.savingThrow.proficient) return this.modifier + this.proficiencyBonus;
            return this.modifier;
        },

        inputId() {
            return `${this.savingThrow.name}-saving-throw`;
        }
    },

    methods: {
        toggleProficiency() {
            if(!this.savingThrow) return;
            updateSavingThrow({
                name: this.savingThrow.name,
                proficient: !this.savingThrow.proficient
            });
        }
    }
}
</script>
