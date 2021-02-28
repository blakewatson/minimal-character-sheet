<template>
    <div class="saving-throw">
        <label :for="inputId" class="centered huge padded block">
            {{ saveBonus | signedNumString }}
            <input v-if="savingThrow" type="checkbox" :id="inputId" :checked="savingThrow.proficient" :disabled="readOnly" @change="toggleProficiency" />
        </label>
    </div>
</template>

<script>
import { mapGetters, mapState } from 'vuex';

export default {
    name: 'SavingThrow',
    props: ['savingThrow', 'modifier'],

    computed: {
        ...mapGetters(['proficiencyBonus']),
        ...mapState(['readOnly']),

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
            this.$store.commit('updateSavingThrow', {
                name: this.savingThrow.name,
                proficient: !this.savingThrow.proficient
            });
        }
    },

    created() {
        console.log(this.savingThrow)
    }
}
</script>
