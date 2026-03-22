<template>
  <details
    open
    class="border-light-foreground dark:border-dark-foreground mb-4 border-t"
  >
    <summary class="section-label">{{ $t(title) }}</summary>
    <quill-editor
      :initial-contents="textField"
      :read-only="readOnly"
      @quill-text-change="updateTextField"
    ></quill-editor>
  </details>
</template>

<script>
import { state, updateTextField as storeUpdateTextField } from '../store';
import QuillEditor from './QuillEditor.vue';

export default {
  name: 'TextSection',

  props: ['title', 'field', 'readOnly'],

  computed: {
    textField() {
      return state[this.field] || '';
    },
  },

  methods: {
    updateTextField(val) {
      storeUpdateTextField({
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
