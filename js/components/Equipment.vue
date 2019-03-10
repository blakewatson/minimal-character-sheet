<template>
    <section>
        <p class="label centered">Equipment</p>

        <div class="row vert-after">
            <div class="box box-lite" v-for="(c, i) in coins">
                <span class="label centered">{{ c.name }}</span>
                <field class="centered huge padded" :value="c.amount" @update-value="updateAmount(i, $event)"></field>
            </div>
        </div>

        <quill-editor :initial-contents="equipmentText" @quill-text-change="updateEquipment"></quill-editor>
    </section>
</template>

<script>
import Vue from 'vue';
import { mapState } from 'vuex';
import QuillEditor from './QuillEditor';
import Field from './Field';

export default {
    name: 'Equipment',

    computed: {
        ...mapState(['coins', 'equipmentText'])
    },

    methods: {
        updateAmount(i, val) {
            this.$store.commit('updateCoins', { i, amount: val });
        },

        updateEquipment(val) {
            this.$store.commit('updateEquipment', { val });
        }
    },

    components: {
        'quill-editor': QuillEditor,
        'field': Field
    }
}
</script>
