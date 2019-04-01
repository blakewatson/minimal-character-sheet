<template>
    <section>
        <p class="label centered">{{ title }}</p>
        <quill-editor :initial-contents="textField" @quill-text-change="updateTextField"></quill-editor>
    </section>
</template>

<script>
import { mapState } from 'vuex';
import QuillEditor from './QuillEditor';

export default {
    name: 'TextSection',

    props: ['title', 'field'],

    computed: {
        ...mapState([
            'equipmentText',
            'proficienciesText',
            'featuresText',
            'backstoryText',
            'treasureText',
            'organizationsText'
        ]),

        textField() {
            if(!this[this.field]) return '';
            return this[this.field];
        }
    },

    methods: {
        updateTextField(val) {
            this.$store.commit('updateTextField', {
                field: this.field,
                val: val
            })
        }
    },

    components: {
        'quill-editor': QuillEditor
    }
}
</script>
