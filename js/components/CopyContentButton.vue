<template>
  <div class="flex items-center gap-4">
    <button @click="copyToClipboardAndClose" class="button-primary">
      {{ $t('Copy and close') }}
    </button>

    <button @click="copyToClipboard" class="button">{{ $t('Copy') }}</button>
    <span v-if="showCopySuccess" class="text-green-700 dark:text-green-300">
      {{ $t('Copied to clipboard!') }}
    </span>
    <span v-if="showCopyError" class="text-red-700 dark:text-red-300">
      {{ $t('Failed to copy.') }}
    </span>
  </div>
</template>

<script>
import { Notyf } from 'notyf';
import { copyHtmlToClipboard } from '../utils.js';

export default {
  name: 'CopyContentButton',

  props: {
    copyableText: {
      type: String,
      required: true,
    },
    copyAndClose: Boolean,
    getCopyableHtml: {
      type: Function,
      required: true,
    },
  },

  data() {
    return {
      showCopyError: false,
      showCopySuccess: false,
    };
  },

  methods: {
    async copyToClipboard() {
      try {
        const html = this.getCopyableHtml()();
        await copyHtmlToClipboard(html, this.copyableText);
        this.showCopySuccess = true;
        this.showCopyError = false;
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        this.showCopyError = true;
        this.showCopySuccess = false;
        return;
      }

      setTimeout(() => {
        this.showCopySuccess = false;
        this.showCopyError = false;
      }, 2000);
    },

    async copyToClipboardAndClose() {
      try {
        const html = this.getCopyableHtml()();
        await copyHtmlToClipboard(html, this.copyableText);

        const notyf = new Notyf({ duration: 2000 });
        notyf.success(this.$t('Copied to clipboard!'));

        this.$emit('close');
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        this.showCopyError = true;
        return;
      }
    },
  },
};
</script>
