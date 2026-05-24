<template>
  <div
    :class="computedClasses"
    class="quill-editor ql-bubble ql-container has-focus:outline-light-accent dark:has-focus:outline-dark-accent rounded-xs bg-neutral-100 *:text-[16px] has-focus:outline-2 dark:bg-neutral-800"
    v-bind="$attrs"
    @click="onEditorClick"
  ></div>
</template>

<script>
import Quill from 'quill';
import { deltaToHtml } from '../quill-renderer.js';
import { MCS_QUILL_DELTA_PREFIX } from '../utils.js';

const Delta = Quill.import('delta');

export default {
  name: 'QuillEditor',

  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
    initialContents: {},
    readOnly: {
      type: Boolean,
      default: false,
    },
    toolbarOptions: {
      type: Array,
      default: () => [
        'bold',
        'italic',
        'strike',
        'link',
        { header: 1 },
        { header: 2 },
        'blockquote',
        { list: 'bullet' },
      ],
    },
  },

  data() {
    return {
      contents: null,
      isStatic: true,
      mouseDownEvent: null,
      useSans: false,
      useSerif: false,
    };
  },

  computed: {
    computedClasses() {
      const classes = this.useSans
        ? ' font-sans *:font-sans sm:*:text-[15px]'
        : this.useSerif
          ? ' font-serif *:font-serif sm:*:text-[15px]'
          : ' font-mono *:font-mono sm:*:text-[13px]';

      if (this.collapsed) {
        return classes + ' collapsed';
      }

      return classes;
    },
  },

  methods: {
    getFontSetting() {
      var fontSetting = window.localStorage.getItem('setting-textarea-font');

      if (fontSetting === 'sans-serif') {
        this.useSans = true;
      } else if (fontSetting === 'serif') {
        this.useSerif = true;
      }
    },

    initQuill() {
      this.isStatic = false;

      this.editor = new Quill(this.$el, {
        theme: 'bubble',
        modules: {
          toolbar: this.toolbarOptions,
        },
        formats: [
          'bold',
          'italic',
          'strike',
          'link',
          'header',
          'blockquote',
          'list',
          'align',
          'indent',
          'image',
        ],
      });

      if (this.initialContents) {
        this.editor.setContents(this.initialContents);
      }

      if (this.readOnly) {
        this.editor.disable();
      }

      if (!this.readOnly) {
        this.editor.root.addEventListener('paste', this.onEditorPaste, true);

        this.editor.on('text-change', () => {
          this.contents = this.editor.getContents();
          this.$emit('quill-text-change', this.contents);
        });

        this.$nextTick(() => {
          this.$el.classList.toggle('collapsed', this.collapsed);
        });
      }

      this.$el.addEventListener('click', (event) => {
        if (
          event.target.nodeName === 'A' &&
          !event.target.closest('.ql-tooltip')
        ) {
          window.open(event.target.href, '_blank');
        }
      });
    },

    onEditorPaste(event) {
      const pastedText = event.clipboardData?.getData('text/plain') || '';

      if (!pastedText.startsWith(MCS_QUILL_DELTA_PREFIX)) {
        return;
      }

      event.preventDefault();
      event.stopImmediatePropagation();

      try {
        const pastedDelta = JSON.parse(
          pastedText.slice(MCS_QUILL_DELTA_PREFIX.length),
        );

        if (!Array.isArray(pastedDelta.ops)) {
          throw new Error('Invalid MCS Quill delta payload.');
        }

        const range = this.editor.getSelection(true);
        const insertDelta = new Delta(pastedDelta.ops);
        const pasteChange = new Delta()
          .retain(range.index)
          .delete(range.length)
          .concat(insertDelta);

        this.editor.updateContents(pasteChange, 'user');
        this.editor.setSelection(range.index + insertDelta.length(), 0);
        this.$emit('mcs-quill-delta-paste', pastedDelta);
      } catch (err) {
        console.error('Failed to parse pasted MCS Quill delta:', err);
      }
    },

    onEditorClick() {
      if (this.readOnly) {
        return;
      }

      if (this.isStatic) {
        this.initQuill();
      }

      if (this.collapsed) {
        this.$emit('update-collapsed', false);
      }
    },

    renderStaticContents() {
      if (!this.initialContents) {
        // Empty content case, ensure height is preserved
        this.$el.innerHTML = '<div class="ql-editor"><p><br></p></div>';
        return;
      }

      const html = deltaToHtml(this.initialContents);

      this.$el.innerHTML = `<div class="ql-editor">${html}</div>`;

      // Initialize collapsed state
      this.$el.classList.toggle('collapsed', this.collapsed);
    },
  },

  mounted() {
    this.getFontSetting();

    // If not lazy loading, initialize Quill immediately
    if (!this.collapsed && !this.readOnly) {
      this.initQuill();
      return;
    }

    // Otherwise, render static HTML content
    this.renderStaticContents();

    // save the refresh listener for later removal
    this.refreshListener = () => {
      // If editor is still static, just re-render the static content
      if (this.isStatic) {
        this.$nextTick(() => {
          this.renderStaticContents();
        });
      } else {
        this.$nextTick(() => {
          // Editor has been activated, use Quill's API
          this.editor.setContents(this.initialContents);
        });
      }
    };

    // For read-only editors, listen for refresh events on the event bus and
    // update static DOM
    if (this.readOnly) {
      window.sheetEvent.on('quill-refresh', this.refreshListener);
    }
  },

  beforeUnmount() {
    if (this.editor) {
      this.editor.root.removeEventListener('paste', this.onEditorPaste, true);
    }

    if (this.refreshListener) {
      window.sheetEvent.off('quill-refresh', this.refreshListener);
    }
  },
};
</script>
