<template>
  <div>
    <button
      :title="$t('Copy this now')"
      @click="copyToClipboard"
      class="button rounded-sm border p-1"
      type="button"
    >
      <i class="fa-sharp fa-copy"></i>
      <span class="sr-only">{{ $t('Copy this now') }}</span>
    </button>

    <div
      v-if="showCopySuccess"
      class="bg-dark-background dark:bg-light-background dark:text-light-foreground absolute top-1/2 right-full -translate-x-1 -translate-y-1/2 rounded-xs px-2 text-sm text-white"
    >
      {{ $t('Copied!') }}
    </div>

    <div
      v-if="showCopyError"
      class="absolute top-1/2 right-full w-max -translate-x-1 -translate-y-1/2 rounded-xs bg-red-700 px-2 text-sm text-white"
    >
      {{ $t('Failed to copy.') }}
    </div>
  </div>
</template>

<script>
import { MCS_QUILL_DELTA_PREFIX } from '../utils.js';

export default {
  name: 'CopyNowButton',

  props: {
    buildCopyableDelta: {
      type: Function,
      required: true,
    },
    title: {
      type: String,
      default: 'Copy now',
    },
  },

  data() {
    return {
      showCopySuccess: false,
      showCopyError: false,
    };
  },

  methods: {
    async copyToClipboard() {
      try {
        const delta = this.buildCopyableDelta();

        const jsonDelta = JSON.stringify(delta);

        const textBlob = new Blob([MCS_QUILL_DELTA_PREFIX + jsonDelta], {
          type: 'text/plain',
        });

        const item = new ClipboardItem({
          'text/plain': textBlob,
        });

        await navigator.clipboard.write([item]);

        this.showCopySuccess = true;
        this.showCopyError = false;

        setTimeout(() => {
          this.showCopySuccess = false;
        }, 2000);
      } catch (error) {
        this.showCopySuccess = false;
        this.showCopyError = true;

        setTimeout(() => {
          this.showCopyError = false;
        }, 2000);
      }
    },
  },
};
</script>
