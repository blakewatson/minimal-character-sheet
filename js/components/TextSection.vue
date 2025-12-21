<template>
  <details open class="mb-4 border-t border-neutral-300">
    <summary class="section-label">{{ title }}</summary>
    <quill-editor
      :initial-contents="textField"
      :read-only="readOnly"
      @quill-text-change="updateTextField"
    ></quill-editor>
  </details>
</template>

<script>
import { mapState } from 'vuex';
import QuillEditor from './QuillEditor';

export default {
  name: 'TextSection',

  props: ['title', 'field', 'readOnly'],

  computed: {
    ...mapState([
      'equipmentText',
      'proficienciesText',
      'featuresText',
      'personalityText',
      'backstoryText',
      'treasureText',
      'notesText',
      'organizationsText',
    ]),

    textField() {
      if (!this[this.field]) return '';
      return this[this.field];
    },
  },

  methods: {
    updateTextField(val) {
      this.$store.commit('updateTextField', {
        field: this.field,
        val: val,
      });
    },
  },

  components: {
    'quill-editor': QuillEditor,
  },
};
</script>
