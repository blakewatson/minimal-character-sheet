<template>
    <div class="saving-throw">
        <span class="centered huge padded block">{{ saveBonus | signedNumString }}</span>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'SavingThrow',
    props: ['savingThrow', 'modifier'],

    computed: {
        ...mapGetters(['proficiencyBonus']),
        saveBonus() {
            if(this.savingThrow.proficient) return this.modifier + this.proficiencyBonus;
            return this.modifier;
        }
    }
}
</script>
