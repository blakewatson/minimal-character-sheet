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
        <strong>{{ $t('Level') }}:</strong>
        {{ spell.level === 0 ? $t('Cantrip') : spell.level }}
      </li>
      <li v-if="spell.school">
        <strong>{{ $t('School') }}:</strong> {{ spell.school.name }}
      </li>
      <li>
        <strong>{{ $t('Casting Time') }}:</strong>
        {{ replaceUnderscores(capitalize(spell.casting_time)) }}
        <span v-if="spell.reaction_condition"
          >- {{ spell.reaction_condition }}</span
        >
      </li>
      <li v-if="spell.duration">
        <strong>{{ $t('Duration') }}:</strong> {{ spell.duration }}
      </li>
      <li v-if="spell.range">
        <strong>{{ $t('Range') }}:</strong> {{ spell.range }}
        <span v-if="spell.range_unit">{{ spell.range_unit }}</span>
      </li>
      <li v-else-if="spell.range === 0">
        <strong>{{ $t('Range') }}:</strong> {{ $t('Self') }}
      </li>
      <li v-if="spell.target_type">
        <strong>{{ $t('Target') }}:</strong>
        {{ spell.target_count || '' }}
        {{ spell.target_type || '' }}
      </li>
      <li v-if="spell.shape_type">
        <strong>{{ $t('Area of Effect') }}:</strong>
        {{ capitalize(spell.shape_type) }}
        <span v-if="spell.shape_size"
          >({{ spell.shape_size }} {{ spell.shape_size_unit || '' }})</span
        >
      </li>
      <li v-if="spell.attack_roll">
        <strong>{{ $t('Spell Attack') }}</strong>
      </li>
      <li v-if="spell.saving_throw_ability">
        <strong>{{ $t('Saving Throw') }}:</strong>
        {{ capitalize(spell.saving_throw_ability) }}
      </li>
      <li v-if="spell.damage_roll && spell.damage_types.length">
        <strong>{{ $t('Damage') }}:</strong> {{ spell.damage_roll }}
        {{ spell.damage_types.map((type) => capitalize(type)).join(', ') }}
      </li>
      <li v-if="spell.concentration">
        <strong>{{ $t('Requires Concentration') }}</strong>
      </li>
      <li v-if="spell.somatic || spell.verbal || spell.material">
        <strong>{{ $t('Components') }}:</strong> {{ buildComponentsString() }}
      </li>
      <li v-if="spell.ritual">
        <strong>{{ $t('Ritual') }}</strong>
      </li>
    </ul>

    <div
      class="spell-desc my-2 text-sm"
      v-if="renderedDesc"
      v-html="renderedDesc"
    ></div>

    <div class="spell-higher-level my-2 text-sm" v-if="renderedHigherLevel">
      <p class="mb-2">
        <strong>{{ $t('At higher levels') }}:</strong>
      </p>
      <div v-html="renderedHigherLevel"></div>
    </div>

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
  capitalize,
  deltaAddBoldedLine,
  deltaAddHeader,
  deltaAddItalicizedLine,
  deltaAddMarkdown,
  deltaAddProperty,
  renderMarkdown,
  replaceUnderscores,
} from '../../utils.js';
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

  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.$nextTick(() => {
          if (this.spell.desc) {
            this.renderedDesc = renderMarkdown(this.spell.desc || '');
          }

          if (this.spell.higher_level) {
            this.renderedHigherLevel = renderMarkdown(
              this.spell.higher_level || '',
            );
          }
        });
      }
    },
  },

  methods: {
    capitalize,
    replaceUnderscores,

    buildComponentsString() {
      const components = [];
      if (this.spell.verbal) components.push('V');
      if (this.spell.somatic) components.push('S');
      if (this.spell.material) components.push('M');

      if (this.spell.material_specified) {
        components.push(`- ${this.spell.material_specified}`);
      }

      if (this.spell.material_consumed) {
        components.push(`(${this.$t('Consumed')})`);
      }

      if (this.spell.material_cost) {
        components.push(`(${this.$t('Cost')}: ${this.spell.material_cost})`);
      }

      return components.join(' ');
    },

    buildCopyableDelta() {
      let delta = new Delta();

      // spell header
      delta = deltaAddHeader(delta, this.spell.name, 2);

      // rulebook source
      if (this.spell.document?.name) {
        delta = deltaAddItalicizedLine(delta, this.spell.document.name);
      }

      delta = delta.insert('\n');

      /* SECTION spell attributes --------------------------- */

      delta = deltaAddProperty(
        delta,
        this.$t('Level'),
        this.spell.level === 0 ? this.$t('Cantrip') : this.spell.level,
        'bullet',
      );

      if (this.spell.school) {
        delta = deltaAddProperty(
          delta,
          this.$t('School'),
          this.spell.school.name,
          'bullet',
        );
      }

      // optional reaction condition for casting the spell
      let castingTimeValue = replaceUnderscores(
        capitalize(this.spell.casting_time),
      );
      if (this.spell.reaction_condition) {
        castingTimeValue += ` - ${this.spell.reaction_condition}`;
      }

      delta = deltaAddProperty(
        delta,
        this.$t('Casting Time'),
        castingTimeValue,
        'bullet',
      );

      if (this.spell.duration) {
        delta = deltaAddProperty(
          delta,
          this.$t('Duration'),
          this.spell.duration,
          'bullet',
        );
      }

      if ('range' in this.spell) {
        let rangeValue =
          this.spell.range === 0
            ? this.$t('Self')
            : `${this.spell.range} ${this.spell.range_unit || ''}`;
        delta = deltaAddProperty(delta, this.$t('Range'), rangeValue, 'bullet');
      }

      if (this.spell.target_type) {
        const targetValue = `${
          this.spell.target_count ? this.spell.target_count + ' ' : ''
        }${this.spell.target_type}`;
        delta = deltaAddProperty(
          delta,
          this.$t('Target'),
          targetValue,
          'bullet',
        );
      }

      if (this.spell.shape_type) {
        let shapeValue = capitalize(this.spell.shape_type);
        if (this.spell.shape_size) {
          shapeValue += ` (${this.spell.shape_size} ${this.spell.shape_size_unit || ''})`;
        }
        delta = deltaAddProperty(
          delta,
          this.$t('Area of Effect'),
          shapeValue,
          'bullet',
        );
      }

      if (this.spell.attack_roll) {
        delta = deltaAddProperty(delta, this.$t('Spell Attack'), '', 'bullet');
      }

      if (this.spell.saving_throw_ability) {
        delta = deltaAddProperty(
          delta,
          this.$t('Saving Throw'),
          capitalize(this.spell.saving_throw_ability),
          'bullet',
        );
      }

      if (this.spell.damage_roll && this.spell.damage_types.length) {
        let damageValue = this.spell.damage_roll;

        damageValue += ` ${this.spell.damage_types
          .map((type) => capitalize(type))
          .join(', ')}`;

        delta = deltaAddProperty(
          delta,
          this.$t('Damage'),
          damageValue,
          'bullet',
        );
      }

      if (this.spell.concentration) {
        delta = deltaAddProperty(
          delta,
          this.$t('Requires Concentration'),
          '',
          'bullet',
        );
      }

      if (this.spell.somatic || this.spell.verbal || this.spell.material) {
        const componentsValue = this.buildComponentsString();
        delta = deltaAddProperty(
          delta,
          this.$t('Components'),
          componentsValue,
          'bullet',
        );
      }

      if (this.spell.ritual) {
        delta = deltaAddProperty(delta, this.$t('Ritual'), '', 'bullet');
      }

      /* !SECTION ------------------------------------------- */

      delta = delta.insert('\n');

      if (this.spell.desc) {
        delta = deltaAddMarkdown(delta, this.spell.desc);
      }

      if (this.spell.higher_level) {
        delta = delta.insert('\n');
        delta = deltaAddBoldedLine(delta, this.$t('At higher levels'), 3);
        delta = deltaAddMarkdown(delta, this.spell.higher_level);
      }

      return delta;
    },

    buildCopyableHtml() {
      let html = `<h2>${this.spell.name}</h2>`;

      if (this.spell.document?.name) {
        html += `<p><em>${this.spell.document.name}</em></p>`;
      }

      // get the html of info, desc, and higher level text and put it into the
      // html variable
      const infoEl = this.$el.querySelector('.spell-info');
      const descEl = this.$el.querySelector('.spell-desc');
      const higherLevelEl = this.$el.querySelector('.spell-higher-level');

      if (infoEl) {
        html += infoEl.outerHTML;
      }

      if (descEl) {
        // add line breaks before each paragraph so that quill will put them in
        // the copied html
        html += this.renderedDesc;
      }

      if (higherLevelEl) {
        html +=
          '<p>' +
          `<strong>${this.$t('At higher levels')}</strong><br>` +
          this.renderedHigherLevel +
          '</p>';
      }

      // remove vue comments from the html
      html = html.replace(/<!---->/g, '');

      return html;
    },

    buildCopyableText() {
      if (!this.spell) {
        console.log('No spell provided');
        return '';
      }

      let text = `${this.spell.name}\n`;

      if (this.spell.document?.name) {
        text += `${this.spell.document.name}\n`;
      }

      text += `\n${this.$t('Level')}: ${this.spell.level === 0 ? this.$t('Cantrip') : this.spell.level}\n`;
      if (this.spell.school) {
        text += `${this.$t('School')}: ${this.spell.school.name}\n`;
      }
      text += `${this.$t('Casting Time')}: ${replaceUnderscores(
        capitalize(this.spell.casting_time),
      )}\n`;
      if (this.spell.duration) {
        text += `${this.$t('Duration')}: ${this.spell.duration}\n`;
      }
      if (this.spell.reaction_condition) {
        text += `- ${this.spell.reaction_condition}\n`;
      }
      if (this.spell.range) {
        text += `${this.$t('Range')}: ${this.spell.range} ${this.spell.range_unit || ''}\n`;
      }
      if (this.spell.target_type) {
        text += `${this.$t('Target')}: ${
          (this.spell.target_count ? this.spell.target_count + ' ' : '') +
          this.spell.target_type
        }\n`;
      }
      if (this.spell.shape_type) {
        text += `${this.$t('Area of Effect')}: ${capitalize(this.spell.shape_type)} ${
          this.spell.shape_size
            ? `(${this.spell.shape_size} ${this.spell.shape_size_unit || ''})`
            : ''
        }\n`;
      }
      if (this.spell.attack_roll) {
        text += `${this.$t('Spell Attack')}\n`;
      }
      if (this.spell.saving_throw_ability) {
        text += `${this.$t('Saving Throw')}: ${capitalize(this.spell.saving_throw_ability)}\n`;
      }
      if (this.spell.damage_roll && this.spell.damage_types.length) {
        text += `${this.$t('Damage')}: ${this.spell.damage_roll} ${this.spell.damage_types
          .map((type) => capitalize(type))
          .join(', ')}\n`;
      }
      if (this.spell.concentration) {
        text += `${this.$t('Requires Concentration')}\n`;
      }
      if (this.spell.somatic || this.spell.verbal || this.spell.material) {
        text += `${this.$t('Components')}: ${
          this.spell.verbal ? 'V ' : ''
        }${this.spell.somatic ? 'S ' : ''}${this.spell.material ? 'M' : ''}\n`;

        if (this.spell.material_specified) {
          text += `- ${this.spell.material_specified} ${this.spell.material_consumed ? `(${this.$t('Consumed')}) ` : ''}${
            this.spell.material_cost
              ? `(${this.$t('Cost')}: ${this.spell.material_cost})`
              : ''
          }\n`;
        }
      }
      if (this.spell.ritual) {
        text += `${this.$t('Ritual')}\n`;
      }
      if (this.spell.desc) {
        text += `\n${this.spell.desc}\n`;
      }
      if (this.spell.higher_level) {
        text += `\n${this.$t('At higher levels')}:\n${this.spell.higher_level}\n`;
      }

      return text;
    },
  },

  components: {
    'copy-content-button': CopyContentButton,
  },
};
</script>
