<template>
    <section class="row row-spaced row-vert-centered">
        <div class="vert-center">
            <span class="label label-inline">Proficiency bonus</span>
            <span class="huge padded">{{ proficiencyBonus | signedNumString }}</span>
        </div>
        <div class="vert-center">
            <label class="label label-inline">
                Inspiration
                <input type="checkbox" :checked="inspiration" :disabled="readOnly" @input="updateInspiration">
            </label>
        </div>
    </section>
</template>

<script>
import { mapGetters, mapState } from 'vuex';

export default {
    name: 'Proficiency',
    computed: {
        ...mapGetters(['proficiencyBonus']),
        ...mapState(['inspiration', 'readOnly'])
    },
    methods: {
        updateInspiration(val) {
            this.$store.commit('updateInspiration', val.target.checked);
        }
    }
}
</script>
