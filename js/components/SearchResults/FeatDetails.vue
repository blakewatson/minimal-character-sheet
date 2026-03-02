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

    <div class="my-4 text-sm" v-if="feat.has_prerequisite">
      <strong>Prerequisite:</strong> {{ feat.prerequisite }}
    </div>

    <div class="text-sm" v-if="renderedDesc" v-html="renderedDesc"></div>

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
      :get-copyable-html="getCopyableHtml"
      :copyable-text="copyableText"
      @close="$emit('close')"
      class="mt-4"
    ></copy-content-button>
  </details>
</template>

<script>
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

  computed: {
    copyableText() {
      if (!this.feat) {
        return '';
      }

      let text = '';

      if (this.feat.has_prerequisite) {
        text += `\nPrerequisite: ${this.feat.prerequisite}\n\n`;
      }

      if (this.feat.desc) {
        text += `${this.feat.desc}\n`;
      }

      if (this.feat.benefits && this.feat.benefits.length) {
        this.feat.benefits.forEach((benefit) => {
          text += `\n${benefit.desc}\n`;
        });
      }

      return text + '\n';
    },
  },

  watch: {
    isOpen(newVal) {
      if (!newVal) {
        return;
      }

      this.$nextTick(() => {
        if (this.feat.desc) {
          this.renderedDesc = window.md.render(this.feat.desc || '');
        }

        if (this.feat.benefits) {
          this.renderedBenefits = this.feat.benefits.map((benefit) => {
            return {
              ...benefit,
              desc: window.md.render(benefit.desc || ''),
            };
          });
        }
      });
    },
  },

  methods: {
    getCopyableHtml() {
      const self = this;

      return () => {
        let html = `<h2>${self.feat.name}</h2>`;

        if (self.feat.has_prerequisite) {
          html += `<p><strong>Prerequisite:</strong> ${self.feat.prerequisite}</p>`;
        }

        if (self.feat.desc) {
          html += `${window.md.render(self.feat.desc)}`;
        }

        if (self.feat.benefits && self.feat.benefits.length) {
          self.feat.benefits.forEach((benefit) => {
            html += `${window.md.render(benefit.desc)}`;
          });

          // add line breaks before each paragraph so that quill will put them in
          // the copied html
          html = html
            .replaceAll('<p>', '<br><p>')
            .replaceAll('<ul>', '<br><ul>')
            .replaceAll('<ol>', '<br><ol>')
            .replaceAll('<br><br>', '<br>');
        }

        return html;
      };
    },
  },

  components: {
    'copy-content-button': CopyContentButton,
  },
};
</script>
