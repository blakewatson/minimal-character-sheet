<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground mb-4 border-t"
  >
    <summary class="section-label">{{ $t(title || 'Text Section') }}</summary>
    <quill-editor
      :initial-contents="textField"
      :read-only="readOnly"
      @quill-text-change="updateTextField"
    ></quill-editor>
  </details>
</template>

<script>
import { state } from '../store';
import QuillEditor from './QuillEditor.vue';

/** @typedef {import('../store').AppState} AppState */

/**
 * @typedef {(
 * 'equipmentText' |
 * 'proficienciesText' |
 * 'featuresText' |
 * 'personalityText' |
 * 'backstoryText' |
 * 'treasureText' |
 * 'organizationsText' |
 * 'notesText'
 * )} TextField
 */

export default {
  name: 'TextSection',

  props: {
    title: String,
    field: /** @type {import('vue').PropType<TextField>} */ (String),
    readOnly: Boolean,
  },

  computed: {
    textField() {
      if (!this.field) {
        return '';
      }
      return state[this.field] || '';
    },
  },

  methods: {
    /** @param {object | null} val */
    updateTextField(val) {
      if (!this.field) {
        return;
      }

      state[this.field] = val;
    },
  },

  components: {
    'quill-editor': QuillEditor,
  },
};
</script>
