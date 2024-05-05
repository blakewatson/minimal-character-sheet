<template>
    <section class="row row-spaced row-vert-centered">
        <div class="vert-center">
            <span class="label label-inline">Initiative</span>
            <field classNames="huge block padded" :value="initiative" :read-only="readOnly" @update-value="updateInitiative($event)"></field>
        </div>
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
import Field from './Field';

export default {
    name: 'Proficiency',

    computed: {
        ...mapGetters(['proficiencyBonus']),
        ...mapState(['inspiration', 'readOnly', 'initiative'])
    },

    methods: {
        updateInitiative(val) {
            val = val ? parseInt(val) : 0;
            this.$store.commit('updateInitiative', val);
        },

        updateInspiration(val) {
            this.$store.commit('updateInspiration', val.target.checked);
        }
    },

    components: {
        'field': Field
    }
}
</script>
