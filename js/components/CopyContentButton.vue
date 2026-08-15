<template>
  <div
    class="flex items-center gap-2 max-[430px]:flex-col max-[430px]:items-stretch"
    v-bind="$attrs"
  >
    <app-button
      :disabled="disabled"
      :id="`${id}-copy-and-close`"
      :tooltip="tooltipCopyAndClose"
      @click="copyAndClose"
      class="text-xs"
      primary
      tooltip-align="start"
    >
      {{ $t('Copy and close') }}
    </app-button>

    <app-button
      :disabled="disabled"
      :id="`${id}-copy-only`"
      :tooltip="tooltipCopyOnly"
      :tooltip-type="showCopyError ? 'danger' : 'default'"
      @click="copyOnly"
      class="button text-xs"
    >
      {{ $t('Copy') }}
    </app-button>

    <app-button
      @click="copyForOtherApps"
      :disabled="disabled"
      :id="`${id}-copy-for-other-apps`"
      :tooltip="tooltipCopyForOtherApps"
      :tooltip-type="showCopyForOtherAppsError ? 'danger' : 'default'"
      class="button text-xs min-[431px]:ml-auto"
      tooltip-align="end"
      v-if="buildCopyableHtml && buildCopyableText"
    >
      {{ $t('Copy for other apps') }}
    </app-button>
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
    disabled: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      copyTimer: null,
      id: crypto.randomUUID(),
      showCopyForOtherAppsSuccess: false,
      showCopyForOtherAppsError: false,
      showCopySuccess: false,
      showCopyError: false,
    };
  },

  computed: {
    tooltipCopyAndClose() {
      return this.disabled
        ? this.$t('No content selected')
        : this.$t('Copy all selected content and close the dialog');
    },

    tooltipCopyOnly() {
      return this.showCopyError
        ? 'Failed to copy'
        : this.showCopySuccess
          ? 'Copied'
          : this.disabled
            ? this.$t('No content selected')
            : this.$t('Copy all selected content');
    },

    tooltipCopyForOtherApps() {
      return this.showCopyForOtherAppsError
        ? 'Failed to copy'
        : this.showCopyForOtherAppsSuccess
          ? 'Copied'
          : this.disabled
            ? this.$t('No content selected')
            : this.$t('Copy content for pasting in other apps');
    },
  },

  methods: {
    async copyForOtherApps() {
      try {
        const html = this.buildCopyableHtml();
        const text = this.buildCopyableText();
        await copyHtmlToClipboard(html, text);

        if (Math.round(Math.random()) > 0.5) {
          throw new Error('testing');
        }
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        return;
      } finally {
        clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showCopySuccess = false;
          this.showCopyError = false;
          this.showCopyForOtherAppsSuccess = false;
          this.showCopyForOtherAppsError = false;
        }, 2000);
      }
    },

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
      } catch (error) {
        console.error('Failed to copy spell to clipboard:', error);
        this.showCopySuccess = false;
        this.showCopyError = true;
      } finally {
        clearTimeout(this.copyTimer);
        this.copyTimer = setTimeout(() => {
          this.showCopySuccess = false;
          this.showCopyError = false;
          this.showCopyForOtherAppsSuccess = false;
          this.showCopyForOtherAppsError = false;
        }, 2000);
      }
    },

    async copyAndClose() {
      try {
        await this.copyToClipboard();

        // const notyf = new Notyf({ duration: 2000 });
        // notyf.success(this.$t('Copied to clipboard!'));

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
        notyf.open({
          message: this.$t('Copied to clipboard!'),
        });
      } catch (err) {
        console.error('Failed to copy spell to clipboard:', err);
        this.showCopyError = true;
        return;
      }
    },
  },
};
</script>
