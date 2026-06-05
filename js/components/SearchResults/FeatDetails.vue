<template>
  <details @toggle="isOpen = !isOpen" v-if="feat">
    <summary class="flex cursor-pointer flex-col font-normal">
      <p class="my-0 font-bold">
        {{ feat.name }}
      </p>
      <small
        class="text-light-muted-foreground dark:text-dark-muted-foreground"
        v-if="feat.document"
        >{{ feat.document.name }}</small
      >
    </summary>

    <div class="mt-4 text-sm" v-if="feat.has_prerequisite">
      <strong>{{ $t('Prerequisite') }}:</strong> {{ feat.prerequisite }}
    </div>

    <div class="mt-4 text-sm" v-if="renderedDesc" v-html="renderedDesc"></div>

    <ul class="mt-2" v-if="renderedBenefits.length">
      <li
        :key="benefit.desc"
        class="text-sm"
        v-for="benefit in renderedBenefits"
      >
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
  deltaAddHeader,
  deltaAddItalicizedLine,
  deltaAddMarkdown,
  deltaAddProperty,
} from '../../utils.js';
import CopyContentButton from '../CopyContentButton.vue';

export default {
  name: 'FeatDetails',

  props: {
    feat: {
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
        if (this.feat.desc) {
          this.renderedDesc = renderMarkdown(this.feat.desc || '');
        }

        if (this.feat.benefits) {
          this.renderedBenefits = this.feat.benefits.map((benefit) => {
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

      delta = deltaAddHeader(delta, this.feat.name, 2);

      if (this.feat.document) {
        delta = deltaAddItalicizedLine(delta, this.feat.document.name);
      }

      delta.insert('\n');

      if (this.feat.has_prerequisite) {
        delta = deltaAddProperty(
          delta,
          this.$t('Prerequisite'),
          this.feat.prerequisite,
        );
        delta.insert('\n');
      }

      if (this.feat.desc) {
        delta = deltaAddMarkdown(delta, this.feat.desc);
        delta.insert('\n');
      }

      if (this.feat.benefits && this.feat.benefits.length) {
        this.feat.benefits.forEach((benefit) => {
          delta = deltaAddMarkdown(delta, benefit.desc);
          delta.insert('\n');
        });
      }

      return delta;
    },

    buildCopyableHtml() {
      let html = `<h2>${this.feat.name}</h2>`;

      if (this.feat.document) {
        html += `<p><em>${this.feat.document.name}</em></p>`;
      }

      if (this.feat.has_prerequisite) {
        html += `<p><strong>${this.$t('Prerequisite')}:</strong> ${this.feat.prerequisite}</p>`;
      }

      if (this.feat.desc) {
        html += `${renderMarkdown(this.feat.desc)}`;
      }

      if (this.feat.benefits && this.feat.benefits.length) {
        this.feat.benefits.forEach((benefit) => {
          html += `${renderMarkdown(benefit.desc)}`;
        });
      }

      return html;
    },

    buildCopyableText() {
      if (!this.feat) {
        return '';
      }

      let text = `${this.feat.name}\n`;

      if (this.feat.document) {
        text += `${this.feat.document.name}\n`;
      }

      if (this.feat.has_prerequisite) {
        text += `\n${this.$t('Prerequisite')}: ${this.feat.prerequisite}\n`;
      }

      if (this.feat.desc) {
        text += `\n${this.feat.desc}\n`;
      }

      if (this.feat.benefits && this.feat.benefits.length) {
        this.feat.benefits.forEach((benefit) => {
          text += `\n${benefit.desc}\n`;
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
