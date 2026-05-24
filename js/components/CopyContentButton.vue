<template>
  <div
    class="flex items-center gap-2 max-[430px]:flex-col max-[430px]:items-stretch"
  >
    <button @click="copyAndClose" class="button-primary text-xs">
      {{ $t('Copy and close') }}
    </button>

    <button @click="copyOnly" class="button text-xs">
      {{ $t('Copy') }}
    </button>

    <button
      @click="copyForOtherApps"
      class="button text-xs min-[431px]:ml-auto"
    >
      {{ $t('Copy for other apps') }}
    </button>
  </div>

  <div v-if="showCopySuccess" class="mt-2 text-green-700 dark:text-green-300">
    {{ $t('Copied to clipboard!') }}
  </div>

  <div v-if="showCopyError" class="mt-2 text-red-700 dark:text-red-300">
    {{ $t('Failed to copy.') }}
  </div>
</template>

<script>
import { Notyf } from 'notyf';
import { copyHtmlToClipboard, MCS_QUILL_DELTA_PREFIX } from '../utils.js';

export default {
  name: 'CopyContentButton',

  props: {
    buildCopyableDelta: {
      type: Function,
      required: true,
    },
    buildCopyableHtml: {
      type: Function,
      required: true,
    },
    buildCopyableText: {
      type: Function,
      required: true,
    },
    copyAndClose: Boolean,
  },

  data() {
    return {
      showCopyError: false,
      showCopySuccess: false,
    };
  },

  methods: {
    async copyForOtherApps() {
      try {
        const html = this.buildCopyableHtml();
        const text = this.buildCopyableText();
        await copyHtmlToClipboard(html, text);
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

    async copyToClipboard() {
      const delta = this.buildCopyableDelta();
      console.log('Built delta ops for copying:', delta);

      const jsonDelta = JSON.stringify(delta);

      const textBlob = new Blob([MCS_QUILL_DELTA_PREFIX + jsonDelta], {
        type: 'text/plain',
      });

      const item = new ClipboardItem({
        'text/plain': textBlob,
      });

      await navigator.clipboard.write([item]);
    },

    async copyAndClose() {
      try {
        await this.copyToClipboard();

        const notyf = new Notyf({ duration: 2000 });
        notyf.success(this.$t('Copied to clipboard!'));

        this.$emit('close');
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        this.showCopyError = true;
        return;
      }
    },

    async copyOnly() {
      try {
        await this.copyToClipboard();

        const notyf = new Notyf({ duration: 2000 });
        notyf.success(this.$t('Copied to clipboard!'));
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        this.showCopyError = true;
        return;
      }
    },
  },
};
</script>
