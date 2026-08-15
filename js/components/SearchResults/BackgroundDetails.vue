<template>
  <details @toggle="isOpen = !isOpen" v-if="background">
    <summary class="flex cursor-pointer flex-col font-normal">
      <p class="my-0 font-bold">
        {{ background.name }}
      </p>
      <small
        class="text-light-muted-foreground dark:text-dark-muted-foreground"
        v-if="background.document"
        >{{ background.document.name }}</small
      >
    </summary>

    <div class="mt-2 text-sm" v-if="renderedDesc" v-html="renderedDesc"></div>

    <ul class="mt-2" v-if="renderedBenefits.length">
      <li class="text-sm" v-for="benefit in renderedBenefits">
        <strong>{{ benefit.name }}</strong
        ><br />
        <div v-html="benefit.desc"></div>
      </li>
    </ul>

    <copy-content-button
      :build-copyable-delta="buildCopyableDelta"
      :build-copyable-html="buildCopyableHtml"
      :build-copyable-text="buildCopyableText"
      @close="$emit('close')"
      class="mt-4"
    ></copy-content-button>
  </details>
</template>

<script>
import { Delta } from 'quill';
import {
  deltaAddBoldedLine,
  deltaAddHeader,
  deltaAddItalicizedLine,
  deltaAddMarkdown,
  renderMarkdown,
} from '../../utils.js';
import CopyContentButton from '../CopyContentButton.vue';

export default {
  name: 'BackgroundDetails',

  props: {
    background: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      isOpen: false,
      renderedBenefits: [],
      renderedDesc: '',
    };
  },

  watch: {
    isOpen(newVal) {
      if (!newVal) {
        return;
      }

      this.$nextTick(() => {
        if (this.background.desc) {
          this.renderedDesc = renderMarkdown(this.background.desc || '');
        }

        if (this.background.benefits) {
          this.renderedBenefits = this.background.benefits.map((benefit) => {
            return {
              ...benefit,
              desc: renderMarkdown(benefit.desc || ''),
            };
          });
        }
      });
    },
  },

  methods: {
    buildCopyableDelta() {
      let delta = new Delta();

      delta = deltaAddHeader(delta, this.background.name);

      if (this.background.document) {
        delta = deltaAddItalicizedLine(delta, this.background.document.name);
      }

      delta.insert('\n');

      if (this.background.desc) {
        delta = deltaAddMarkdown(delta, this.background.desc);
        delta.insert('\n');
      }

      if (this.background.benefits && this.background.benefits.length) {
        this.background.benefits.forEach((benefit) => {
          delta = deltaAddBoldedLine(delta, benefit.name);
          delta = deltaAddMarkdown(delta, benefit.desc);
          delta.insert('\n');
        });
      }

      return delta;
    },

    buildCopyableHtml() {
      let html = `<h2>${this.background.name}</h2>`;

      if (this.background.document) {
        html += `<p><em>${this.background.document.name}</em></p>`;
      }

      if (this.background.desc) {
        html += `${renderMarkdown(this.background.desc)}`;
      }

      if (this.background.benefits && this.background.benefits.length) {
        this.background.benefits.forEach((benefit) => {
          html += `<p><strong>${benefit.name}</strong><br/>${renderMarkdown(
            benefit.desc,
          )}</p>`;
        });
      }

      return html;
    },

    buildCopyableText() {
      if (!this.background) {
        return '';
      }

      let text = `${this.background.name}\n`;

      if (this.background.document) {
        text += `${this.background.document.name}\n`;
      }

      if (this.background.desc) {
        text += `\n${this.background.desc}\n`;
      }

      if (this.background.benefits && this.background.benefits.length) {
        this.background.benefits.forEach((benefit) => {
          text += `\n${benefit.name}\n${benefit.desc}\n`;
        });
      }

      return text + '\n';
    },
  },

  components: {
    'copy-content-button': CopyContentButton,
  },
};
</script>
