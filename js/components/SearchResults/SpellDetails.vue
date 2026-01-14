<template>
  <details @toggle="isOpen = !isOpen" v-if="spell">
    <summary class="flex cursor-pointer flex-col font-normal">
      <p class="my-0 font-bold">
        {{ spell.name }}
      </p>
      <small
        class="text-light-muted-foreground dark:text-dark-muted-foreground"
        v-if="spell.document"
        >{{ spell.document.name }}</small
      >
    </summary>

    <ul class="spell-info my-2 text-sm">
      <li>
        <strong>Level:</strong>
        {{ spell.level === 0 ? 'Cantrip' : spell.level }}
      </li>
      <li v-if="spell.school">
        <strong>School:</strong> {{ spell.school.name }}
      </li>
      <li>
        <strong>Casting Time:</strong>
        {{ replaceUnderscores(capitalize(spell.casting_time)) }}
        <span v-if="spell.reaction_condition"
          >- {{ spell.reaction_condition }}</span
        >
      </li>
      <li v-if="spell.duration">
        <strong>Duration:</strong> {{ spell.duration }}
      </li>
      <li v-if="spell.range">
        <strong>Range:</strong> {{ spell.range }}
        <span v-if="spell.range_unit">{{ spell.range_unit }}</span>
      </li>
      <li v-else-if="spell.range === 0"><strong>Range:</strong> Self</li>
      <li v-if="spell.target_type">
        <strong>Target:</strong>
        <span v-if="spell.target_count">{{ spell.target_count }}</span>
        {{ spell.target_type }}
      </li>
      <li v-if="spell.shape_type">
        <strong>Area of Effect:</strong>
        {{ capitalize(spell.shape_type) }}
        <span v-if="spell.shape_size"
          >({{ spell.shape_size }} {{ spell.shape_size_unit || '' }})</span
        >
      </li>
      <li v-if="spell.attack_roll">
        <strong>Spell Attack</strong>
      </li>
      <li v-if="spell.saving_throw_ability">
        <strong>Saving Throw:</strong>
        {{ capitalize(spell.saving_throw_ability) }}
      </li>
      <li v-if="spell.damage_roll">
        <strong>Damage:</strong> {{ spell.damage_roll }}
        <span v-if="spell.damage_types">
          {{ spell.damage_types.map((type) => capitalize(type)).join(', ') }}
        </span>
      </li>
      <li v-if="spell.concentration">
        <strong>Requires Concentration</strong>
      </li>
      <li v-if="spell.somatic || spell.verbal || spell.material">
        <strong>Components:</strong>
        <span v-if="spell.verbal">V</span>
        <span v-if="spell.somatic">S</span>
        <span v-if="spell.material"
          >M<span v-if="spell.material_specified">
            - {{ spell.material_specified }}
            <span v-if="spell.material_consumed"> (Consumed)</span>
            <span v-if="spell.material_cost">
              (Cost: {{ spell.material_cost }})</span
            >
          </span>
        </span>
      </li>
      <li v-if="spell.ritual"><strong>Ritual</strong></li>
    </ul>

    <div
      class="spell-desc my-2 text-sm"
      v-if="renderedDesc"
      v-html="renderedDesc"
    ></div>

    <div class="spell-higher-level my-2 text-sm" v-if="renderedHigherLevel">
      <p class="mb-2">
        <strong>At higher levels:</strong>
      </p>
      <div v-html="renderedHigherLevel"></div>
    </div>

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
  name: 'SpellDetails',
  props: {
    spell: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isOpen: false,
      renderedDesc: '',
      renderedHigherLevel: '',
    };
  },

  computed: {
    copyableText() {
      let text = `${this.spell.name}\n`;

      if (this.spell.document?.name) {
        text += `${this.spell.document.name}\n`;
      }

      text += `\nLevel: ${this.spell.level === 0 ? 'Cantrip' : this.spell.level}\n`;
      if (this.spell.school) {
        text += `School: ${this.spell.school.name}\n`;
      }
      text += `Casting Time: ${this.replaceUnderscores(
        this.capitalize(this.spell.casting_time),
      )}\n`;
      if (this.spell.duration) {
        text += `Duration: ${this.spell.duration}\n`;
      }
      if (this.spell.reaction_condition) {
        text += `- ${this.spell.reaction_condition}\n`;
      }
      if (this.spell.range) {
        text += `Range: ${this.spell.range} ${this.spell.range_unit || ''}\n`;
      }
      if (this.spell.target_type) {
        text += `Target: ${
          (this.spell.target_count ? this.spell.target_count + ' ' : '') +
          this.spell.target_type
        }\n`;
      }
      if (this.spell.shape_type) {
        text += `Area of Effect: ${this.capitalize(this.spell.shape_type)} ${
          this.spell.shape_size
            ? `(${this.spell.shape_size} ${this.spell.shape_size_unit || ''})`
            : ''
        }\n`;
      }
      if (this.spell.attack_roll) {
        text += `Spell Attack\n`;
      }
      if (this.spell.saving_throw_ability) {
        text += `Saving Throw: ${this.capitalize(this.spell.saving_throw_ability)}\n`;
      }
      if (this.spell.damage_roll) {
        text += `Damage: ${this.spell.damage_roll} ${this.spell.damage_types
          .map((type) => this.capitalize(type))
          .join(', ')}\n`;
      }
      if (this.spell.concentration) {
        text += `Requires Concentration\n`;
      }
      if (this.spell.somatic || this.spell.verbal || this.spell.material) {
        text += `Components: ${
          this.spell.verbal ? 'V ' : ''
        }${this.spell.somatic ? 'S ' : ''}${this.spell.material ? 'M' : ''}\n`;

        if (this.spell.material_specified) {
          text += `- ${this.spell.material_specified} ${this.spell.material_consumed ? '(Consumed) ' : ''}${
            this.spell.material_cost
              ? `(Cost: ${this.spell.material_cost})`
              : ''
          }\n`;
        }
      }
      if (this.spell.ritual) {
        text += `Ritual\n`;
      }
      if (this.spell.desc) {
        text += `\n${this.spell.desc}\n`;
      }
      if (this.spell.higher_level) {
        text += `\nAt higher levels:\n${this.spell.higher_level}\n`;
      }
      return text;
    },
  },

  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.$nextTick(() => {
          if (this.spell.desc) {
            this.renderedDesc = window.md.render(this.spell.desc || '');
          }

          if (this.spell.higher_level) {
            this.renderedHigherLevel = window.md.render(
              this.spell.higher_level || '',
            );
          }
        });
      }
    },
  },

  methods: {
    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },

    getCopyableHtml() {
      const self = this;

      return () => {
        let html = `<h2>${self.spell.name}</h2>`;

        if (self.spell.document?.name) {
          html += `<p><em>${self.spell.document.name}</em></p>`;
        }

        // get the html of info, desc, and higher level text and put it into the
        // html variable
        const infoEl = self.$el.querySelector('.spell-info');
        const descEl = self.$el.querySelector('.spell-desc');
        const higherLevelEl = self.$el.querySelector('.spell-higher-level');

        if (infoEl) {
          html += '<br>' + infoEl.outerHTML;
        }
        if (descEl) {
          // add line breaks before each paragraph so that quill will put them in
          // the copied html
          html += self.renderedDesc
            .replaceAll('<p>', '<br><p>')
            .replaceAll('<ul>', '<br><ul>')
            .replaceAll('<ol>', '<br><ol>')
            .replaceAll('<br><br>', '<br>');
        }
        if (higherLevelEl) {
          html +=
            '<br>' +
            '<strong>At Higher Levels</strong><br>' +
            self.renderedHigherLevel;
        }

        // remove vue comments from the html
        html = html.replace(/<!---->/g, '');

        return html;
      };
    },

    replaceUnderscores(str) {
      return str.replace(/_/g, ' ');
    },
  },

  components: {
    'copy-content-button': CopyContentButton,
  },
};
</script>
