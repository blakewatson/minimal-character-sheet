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

    <div class="mt-2" v-if="renderedDesc" v-html="renderedDesc"></div>

    <ul class="mt-2" v-if="renderedBenefits.length">
      <li class="text-sm" v-for="benefit in renderedBenefits">
        <strong>{{ benefit.name }}</strong
        ><br />
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

  computed: {
    copyableText() {
      let text = `${this.background.name}\n\n`;

      if (this.background.desc) {
        text += `${this.background.desc}\n`;
      }

      if (this.background.benefits && this.background.benefits.length) {
        this.background.benefits.forEach((benefit) => {
          text += `\n${benefit.name}\n${benefit.desc}\n`;
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
        if (this.background.desc) {
          this.renderedDesc = window.md.render(this.background.desc || '');
        }

        if (this.background.benefits) {
          this.renderedBenefits = this.background.benefits.map((benefit) => {
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
      return () => {
        let html = `<h2>${this.background.name}</h2><br>`;

        if (this.background.desc) {
          html += `${window.md.render(this.background.desc)}`;
        }

        if (this.background.benefits && this.background.benefits.length) {
          this.background.benefits.forEach((benefit) => {
            html += `<p><strong>${benefit.name}</strong><br/>${window.md.render(
              benefit.desc,
            )}</p>`;
          });
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
