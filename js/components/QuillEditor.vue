<template>
  <div
    :class="computedClasses"
    class="quill-editor ql-bubble has-focus:outline-light-accent dark:has-focus:outline-dark-accent rounded-xs bg-neutral-100 *:text-[16px] has-focus:outline-2 dark:bg-neutral-800"
    v-bind="$attrs"
    @mousedown="onMouseDown"
    @mouseup="onMouseUp"
  ></div>
</template>

<script>
import Quill from 'quill';
import { deltaToHtml } from '../quill-renderer.js';

export default {
  name: 'QuillEditor',

  props: {
    collapsed: {
      type: Boolean,
      default: false,
    },
    initialContents: {},
    lazyLoad: {
      type: Boolean,
      default: true,
    },
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
      editor: null,
      contents: null,
      isStatic: true,
      mouseDownEvent: null,
      refreshListener: null,
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

  watch: {
    // This has to be done manually because Quill adds its own classes to the
    // container and Vue's class syntax will overwrite them.
    collapsed(val) {
      if (this.readOnly) {
        return;
      }

      // this.$el.classList.toggle('collapsed', val);
    },
  },

  methods: {
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

    getFontSetting() {
      var fontSetting = window.localStorage.getItem('setting-textarea-font');

      if (fontSetting === 'sans-serif') {
        this.useSans = true;
      } else if (fontSetting === 'serif') {
        this.useSerif = true;
      }
    },

    /**
     * LAZY LOADING MECHANISM - Step 1: Capture mousedown
     *
     * When the user presses the mouse button on the static editor, we save
     * the coordinates. We need both mousedown AND mouseup to distinguish
     * between clicks (place cursor) and drags (select text).
     */
    onMouseDown(event) {
      // If collapsed, expand the section instead of activating editor
      if (this.collapsed) {
        this.$emit('update-collapsed', false);
        return;
      }

      // If editor is already active (not static), let it handle events normally
      if (!this.isStatic) {
        return;
      }

      // Store the mousedown coordinates for use in onMouseUp
      this.mouseDownEvent = {
        clientX: event.clientX,
        clientY: event.clientY,
        timestamp: Date.now(),
      };
    },

    /**
     * LAZY LOADING MECHANISM - Step 2: Handle mouseup and initialize editor
     *
     * When the user releases the mouse button, we:
     * 1. Check if they selected text (click-drag) or just clicked
     * 2. Calculate selection positions BEFORE initializing Quill (critical!)
     * 3. Initialize the full Quill editor
     * 4. Restore their cursor position or text selection
     */
    onMouseUp() {
      // Don't activate if collapsed, already active, or no mousedown recorded
      if (this.collapsed || !this.isStatic || !this.mouseDownEvent) {
        return;
      }

      // Save click coordinates for cursor positioning
      const { clientX, clientY } = this.mouseDownEvent;

      // Check if user selected text (returns null if just a click)
      // IMPORTANT: Must calculate BEFORE initQuill() destroys the static DOM
      const selectionData = this.captureSelection();

      // Initialize the full Quill editor (destroys static HTML)
      this.initQuill();

      // Wait for Quill to finish rendering, then restore cursor/selection
      this.$nextTick(() => {
        this.editor.focus();
        this.$nextTick(() => {
          // Restore text selection OR position cursor at click point
          selectionData
            ? this.restoreTextSelection(selectionData)
            : this.positionCursor(clientX, clientY);
        });
      });

      // Clean up
      this.mouseDownEvent = null;
    },

    /**
     * LAZY LOADING MECHANISM - Step 3a: Capture text selection
     *
     * If the user click-dragged to select text, calculate the character
     * positions of their selection. This must happen BEFORE Quill initializes
     * because Quill destroys the static DOM.
     *
     * @returns {Object|null} Selection data { text, start, end } or null if no selection
     */
    captureSelection() {
      const selection = window.getSelection();
      const hasSelection = selection?.rangeCount > 0 && !selection.isCollapsed;

      if (!hasSelection) return null;

      const range = selection.getRangeAt(0);
      const editorElement = this.$el.querySelector('.ql-editor');

      if (!editorElement) return null;

      // Return selection data: the actual text + character positions
      // Character positions match Quill's text model (plain text with \n for blocks)
      return {
        text: range.toString(), // Used for verification/correction
        start: this.getTextOffset(
          editorElement,
          range.startContainer,
          range.startOffset,
        ),
        end: this.getTextOffset(
          editorElement,
          range.endContainer,
          range.endOffset,
        ),
      };
    },

    /**
     * LAZY LOADING MECHANISM - Step 3b: Restore text selection in Quill
     *
     * After Quill initializes, restore the user's text selection using the
     * character positions we calculated. Includes self-correction logic in
     * case our calculation is slightly off.
     *
     * @param {Object} data - Selection data { text, start, end }
     */
    restoreTextSelection({ text, start, end }) {
      const length = end - start;
      this.editor.setSelection(start, length);

      // VERIFICATION: Check if we selected the right text
      const selectedText = this.editor.getText(start, length);
      if (selectedText !== text) {
        // Our calculation was off - search nearby for the correct text
        const fullText = this.editor.getText();
        const correctIndex = fullText.indexOf(text, Math.max(0, start - 5));
        if (correctIndex !== -1) {
          // Found it! Correct the selection
          this.editor.setSelection(correctIndex, text.length);
        }
      }
    },

    /**
     * LAZY LOADING MECHANISM - Step 3c: Position cursor at click point
     *
     * For single clicks (no text selection), position the cursor at the
     * exact pixel coordinates where the user clicked.
     *
     * @param {number} x - Click X coordinate
     * @param {number} y - Click Y coordinate
     */
    positionCursor(x, y) {
      const range = this.getCaretRange(x, y);
      if (!range) return;

      // First, set the browser's native selection at the click point
      const selection = window.getSelection();
      selection.removeAllRanges();
      selection.addRange(range);

      // Then have Quill recognize and adopt that selection
      const quillSelection = this.editor.getSelection();
      if (quillSelection) {
        this.editor.setSelection(quillSelection.index, 0);
      }
    },

    /**
     * HELPER: Get a DOM Range at specific pixel coordinates
     *
     * Uses browser APIs to determine where in the text the user clicked.
     * Handles browser compatibility (Firefox vs Chrome/Safari).
     *
     * @param {number} x - X coordinate
     * @param {number} y - Y coordinate
     * @returns {Range|null} DOM Range at that position, or null
     */
    getCaretRange(x, y) {
      // Firefox API
      if (document.caretPositionFromPoint) {
        const position = document.caretPositionFromPoint(x, y);
        if (position) {
          const range = document.createRange();
          range.setStart(position.offsetNode, position.offset);
          range.collapse(true);
          return range;
        }
      }
      // Chrome/Safari API
      else if (document.caretRangeFromPoint) {
        return document.caretRangeFromPoint(x, y);
      }
      return null;
    },

    /**
     * HELPER: Calculate character position in Quill's text model
     *
     * Quill represents rich text as plain text with newlines between paragraphs.
     * For example, this HTML:
     *   <p>Hello</p><p>World</p>
     * Becomes this in Quill:
     *   "Hello\nWorld"
     *
     * This function walks the DOM tree and counts characters, matching how
     * Quill will represent the text after it initializes.
     *
     * @param {Element} root - The .ql-editor element to start from
     * @param {Node} targetNode - The DOM node containing our target position
     * @param {number} targetOffset - Character offset within targetNode
     * @returns {number} Character position in Quill's text model
     */
    getTextOffset(root, targetNode, targetOffset) {
      let textOffset = 0;
      let found = false;

      const walk = (node) => {
        // Already found our target? Stop counting
        if (found) return true;

        // Found the target node! Add its offset and stop
        if (node === targetNode) {
          textOffset += targetOffset;
          found = true;
          return true;
        }

        // TEXT NODE: Add the length of all text content
        if (node.nodeType === Node.TEXT_NODE) {
          textOffset += node.textContent.length;
          return false;
        }

        // ELEMENT NODE: Handle special cases and recurse into children
        if (node.nodeType === Node.ELEMENT_NODE) {
          const tag = node.tagName.toLowerCase();

          // BR tags represent newlines in Quill
          if (tag === 'br') {
            textOffset += 1;
            return false;
          }

          // Process all children first (depth-first traversal)
          for (let child of node.childNodes) {
            if (walk(child)) return true;
          }

          // BLOCK ELEMENTS: Add newline after each block (except the last one)
          // Example: <p>First</p><p>Second</p> becomes "First\nSecond"
          if (node !== root && node.parentNode === root) {
            const isLastChild = node === root.lastElementChild;
            if (!isLastChild) {
              textOffset += 1; // Add newline between blocks
            }
          }
        }

        return false;
      };

      walk(root);
      return textOffset;
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
    if (!this.lazyLoad) {
      this.initQuill();
      return;
    }

    // Otherwise, render static HTML content
    this.renderStaticContents();

    // save the refresh listener for later removal
    this.refreshListener = () => {
      // If editor is still static, just re-render the static content
      if (this.isStatic) {
        this.renderStaticContents();
      } else {
        // Editor has been activated, use Quill's API
        this.editor.setContents(this.initialContents);
      }
    };

    // For read-only editors, listen for refresh events on the event bus and
    // update static DOM
    if (this.readOnly) {
      window.sheetEvent.$on('quill-refresh', this.refreshListener);
    }
  },

  beforeDestroy() {
    if (this.refreshListener) {
      window.sheetEvent.$off('quill-refresh', this.refreshListener);
    }
  },
};
</script>
