<template>
    <section class="row row-spaced">
        <div class="vert-center">
            <span class="label label-inline">Proficiency bonus</span>
            <span class="huge padded">{{ proficiencyBonus | signedNumString }}</span>
        </div>
        <div class="vert-center">
            <label class="label label-inline">
                Inspiration
                <input type="checkbox" :checked="inspiration">
            </label>
        </div>
    </section>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Proficiency',
    data() {
        return {
            inspiration: false
        };
    },
    computed: {
        ...mapGetters(['proficiencyBonus'])
    }
}
</script>
