<template>
  <details @toggle="isOpen = $event.target.open">
    <summary class="flex cursor-pointer flex-col font-normal">
      <p class="my-0 font-bold">
        {{ characterClass.name }}
      </p>
      <small
        class="text-light-muted-foreground dark:text-dark-muted-foreground"
        v-if="characterClass.document"
        >{{ characterClass.document.name }}</small
      >
    </summary>

    <div class="mt-4" v-if="isFetchingDetails">
      <i class="fa-sharp fa-spinner-third fa-spin mr-2"></i>
      {{ $t('Fetching class details...') }}
    </div>

    <div class="mt-2" v-else-if="classDetails">
      <copy-content-button
        :build-copyable-delta="buildCopyableDelta"
        :disabled="shouldDisableCopyButton"
        @close="$emit('close')"
        class="my-2"
        v-if="!isFetchingDetails"
      ></copy-content-button>

      <div class="mb-2">
        <label class="text-sm">
          <input type="checkbox" v-model="includeTitleInCopy" />
          {{ $t('Include title in copy') }}
        </label>
      </div>

      <!-- 5e 2014 - hit points -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent': isSelectedHitPoints,
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !isSelectedHitPoints,
        }"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-if="classDetails.hit_points"
      >
        <p class="mb-1 text-base">
          <strong>{{ $t('Hit points') }}</strong>
        </p>

        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="isSelectedHitPoints" />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildHitPointsDelta"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <ul>
          <li v-if="classDetails.hit_points.hit_dice_name">
            <strong>{{ $t('Hit dice') }}:</strong>
            {{ classDetails.hit_points.hit_dice_name }}
          </li>
          <li v-if="classDetails.hit_points.hit_points_at_1st_level">
            <strong>{{ $t('Hit points at 1st level') }}:</strong>
            {{ classDetails.hit_points.hit_points_at_1st_level }}
          </li>
          <li v-if="classDetails.hit_points.hit_points_at_higher_levels">
            <strong>{{ $t('Hit points at higher levels') }}:</strong>
            {{ classDetails.hit_points.hit_points_at_higher_levels }}
          </li>
        </ul>
      </div>

      <!-- 5e 2014 proficiencies -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent':
            isSelectedProficiencies,
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !isSelectedProficiencies,
        }"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-if="proficienciesDescription"
      >
        <p class="mb-1 text-base">
          <strong>{{ $t('Proficiencies') }}</strong>
        </p>

        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="isSelectedProficiencies" />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildProficienciesDelta"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <div
          class="*:last:mb-0"
          v-html="renderMarkdown(proficienciesDescription)"
        ></div>
      </div>

      <!-- 5e 2014 starting equipment -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent':
            isSelectedStartingEquipment,
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !isSelectedStartingEquipment,
        }"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-if="startingEquipmentDescription"
      >
        <p class="mb-1 text-base">
          <strong>{{ $t('Starting equipment') }}</strong>
        </p>

        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="isSelectedStartingEquipment" />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildStartingEquipmentDelta"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <div v-html="renderMarkdown(startingEquipmentDescription)"></div>
      </div>

      <!-- 5e 2024 - core traits table -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent': isSelectedCoreTraits,
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !isSelectedCoreTraits,
        }"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-if="coreTraitsTable"
      >
        <h2 class="mb-1">
          {{ $t('Core traits') }}
        </h2>

        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="isSelectedCoreTraits" />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildCoreTraitsTableDelta.bind(this)"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <table class="mb-0 w-full border-collapse text-sm">
          <tbody>
            <tr
              v-for="(row, index) in coreTraitsTable"
              :key="index"
              class="border-t"
            >
              <td
                v-for="(cell, cellIndex) in row"
                :key="cellIndex"
                class="border px-2 py-1 align-top"
              >
                <div v-html="cell"></div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 5e 2024 - class features -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent':
            selectedClassFeatures.includes(feature.key),
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !selectedClassFeatures.includes(feature.key),
        }"
        :key="feature.key"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-for="feature in classFeatures"
      >
        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input
            :value="feature.key"
            type="checkbox"
            v-model="selectedClassFeatures"
          />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildClassFeatureDelta.bind(this, feature.key)"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <details>
          <summary class="text-base font-bold">
            <template v-if="feature.gained_at.length > 0"
              >{{ $t('Level') }} {{ feature.gained_at[0].level }}:
            </template>
            {{ feature.name }}
          </summary>

          <div class="mt-3 *:last:mb-0" v-html="feature.rendered_desc"></div>
        </details>
      </div>

      <!-- feature options -->
      <div
        :class="{
          'border-light-accent dark:border-dark-accent':
            selectedClassFeatureOptions.includes(list.key),
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !selectedClassFeatureOptions.includes(list.key),
        }"
        :key="list.key"
        class="mb-4 text-sm"
        v-for="list in classFeatureOptions"
      >
        <details>
          <summary
            :class="{
              'text-light-accent dark:text-dark-accent':
                selectedClassFeatureOptions.length > 0,
            }"
            class="text-base font-bold"
          >
            {{ list.name }}
          </summary>

          <div class="mt-3" v-html="list.rendered_intro_text"></div>

          <div
            :class="{
              'border-light-accent dark:border-dark-accent':
                selectedClassFeatureOptions.includes(
                  option._featureKey + '|' + option.name,
                ),
              'border-light-muted-foreground dark:border-dark-muted-foreground':
                !selectedClassFeatureOptions.includes(
                  option._featureKey + '|' + option.name,
                ),
            }"
            :key="option._featureKey + '|' + option.name"
            class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
            v-for="option in list.options"
          >
            <h3>{{ option.name }}</h3>

            <label
              class="absolute top-2 left-2 flex items-center gap-2 text-sm"
            >
              <input
                :key="option._featureKey + '|' + option.name"
                :value="option._featureKey + '|' + option.name"
                type="checkbox"
                v-model="selectedClassFeatureOptions"
              />
              {{ $t('Include in copy') }}
            </label>

            <copy-now-button
              :build-copyable-delta="
                buildClassFeatureOptionDelta.bind(
                  this,
                  option._featureKey,
                  option.name,
                )
              "
              class="absolute top-1 right-1"
            ></copy-now-button>

            <div class="*:last:mb-0" v-html="option.rendered_desc"></div>
          </div>
        </details>
      </div>
    </div>

    <div class="my-4" v-if="isFetchingSubclasses && !isFetchingDetails">
      <i class="fa-sharp fa-spinner-third fa-spin mr-2"></i>
      {{ $t('Fetching subclass details...') }}
    </div>

    <template
      v-if="subClasses.length && !isFetchingSubclasses && !isFetchingDetails"
    >
      <label for="subclass" class="mb-2 block text-sm font-bold">{{
        $t('Subclass')
      }}</label>

      <select
        class="mb-4"
        id="subclass"
        name="subclass"
        v-if="subClasses.length"
        v-model="selectedSubclassKey"
      >
        <option :value="null">{{ $t('Select a subclass') }}</option>
        <option
          v-for="subClass in subClasses"
          :key="subClass.key"
          :value="subClass.key"
        >
          {{ subClass.name }}
        </option>
      </select>

      <div
        :class="{
          'border-light-accent dark:border-dark-accent':
            selectedSubclassFeatures.includes(feature.key),
          'border-light-muted-foreground dark:border-dark-muted-foreground':
            !selectedSubclassFeatures.includes(feature.key),
        }"
        :key="feature.key"
        class="relative mb-4 rounded-sm border px-2 pt-8 pb-2 text-sm"
        v-for="feature in subclassFeatures"
      >
        <label class="absolute top-2 left-2 flex items-center gap-2 text-sm">
          <input
            :value="feature.key"
            type="checkbox"
            v-model="selectedSubclassFeatures"
          />
          {{ $t('Include in copy') }}
        </label>

        <copy-now-button
          :build-copyable-delta="buildClassFeatureDelta.bind(this, feature.key)"
          class="absolute top-1 right-1"
        ></copy-now-button>

        <details>
          <summary class="text-base font-bold">
            <template v-if="feature.gained_at.length > 0"
              >{{ $t('Level') }} {{ feature.gained_at[0].level }}:
            </template>
            {{ feature.name }}
          </summary>

          <div class="mt-3 *:last:mb-0" v-html="feature.rendered_desc"></div>
        </details>
      </div>
    </template>

    <div class="my-3 flex gap-2" v-if="classDetails">
      <button @click="selectAllForCopy" class="button text-sm">
        {{ $t('Select all') }}
      </button>
      <button @click="deselectAllForCopy" class="button text-sm">
        {{ $t('Deselect all') }}
      </button>
    </div>

    <copy-content-button
      :build-copyable-delta="buildCopyableDelta"
      :disabled="shouldDisableCopyButton"
      @close="$emit('close')"
      class="mt-4"
      v-if="!isFetchingDetails"
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
  parseMarkdownTable,
  renderMarkdown,
} from '../../utils.js';
import CopyContentButton from '../CopyContentButton.vue';
import CopyNowButton from '../CopyNowButton.vue';

export default {
  name: 'ClassDetails',

  props: {
    characterClass: {
      type: Object,
      required: true,
    },
    endpoint: {
      type: String,
      required: true,
    },
  },

  data() {
    return {
      classDetails: null,
      includeTitleInCopy: false,
      isFetchingDetails: false,
      isFetchingSubclasses: false,
      isOpen: false,
      isSelectedCoreTraits: false,
      isSelectedHitPoints: false,
      isSelectedProficiencies: false,
      isSelectedStartingEquipment: false,
      selectedClassFeatures: [],
      selectedClassFeatureOptions: [],
      selectedSubclassKey: null,
      selectedSubclassFeatures: [],
      subClasses: [],
    };
  },

  computed: {
    classFeatures() {
      return (
        this.classDetails?.features
          .filter(
            (feature) =>
              feature.feature_type === 'CLASS_LEVEL_FEATURE' &&
              feature.desc !== '[Column data]' &&
              !feature.name.endsWith('Spell List'),
          )
          .map((feature) => {
            const featureCopy = { ...feature };
            featureCopy.rendered_desc = renderMarkdown(
              featureCopy.desc.replaceAll('###', '### '), //TODO - fix markdown rendering so that this hack isn't necessary
            );
            featureCopy.gained_at = featureCopy.gained_at.toSorted(
              (a, b) => a.level - b.level,
            );
            return featureCopy;
          })
          .toSorted(
            (a, b) =>
              (a.gained_at[0]?.level || 0) - (b.gained_at[0]?.level || 0),
          ) || []
      );
    },

    classFeatureOptions() {
      return (
        this.classDetails?.features
          .filter(
            (feature) => feature.feature_type === 'CLASS_FEATURE_OPTION_LIST',
          )
          .map((list) => {
            const listCopy = { ...list };

            const lines = listCopy.desc.split('\n');
            let introText = '';
            const options = [];
            let currentOption = null;

            for (let line of lines) {
              // This is a bit of a hack to parse the options out of the description
              // since the API doesn't return them in a structured way. The options
              // are denoted by lines that start with '###'.
              if (line.startsWith('###')) {
                // If we were already parsing an option when we hit this line then
                // we need to push the current option to the options array before
                // starting to parse the new option.
                if (currentOption) {
                  options.push(currentOption);
                }

                // Start parsing the new option.
                currentOption = {
                  name: line.replace('###', '').trim(),
                  lines: [],
                };
              } else if (currentOption) {
                // If we're currently parsing an option then this line gets added to it
                currentOption.lines.push(line);
              } else {
                // If this line isn't part of an option then it gets added to the intro text.
                introText += line + '\n';
              }
            }

            listCopy.intro_text = introText.trim();
            listCopy.rendered_intro_text = renderMarkdown(listCopy.intro_text);

            listCopy.options = options.map((option) => {
              const desc = option.lines.join('\n');
              return {
                _featureKey: list.key,
                name: option.name,
                lines: option.lines,
                desc,
                rendered_desc: renderMarkdown(desc),
              };
            });

            return listCopy;
          }) || []
      );
    },

    // 5e 2024 classes
    coreTraitsTable() {
      const feature = this.classDetails?.features.find(
        (f) => f.feature_type === 'CORE_TRAITS_TABLE',
      );

      if (!feature) {
        return null;
      }

      const table = parseMarkdownTable(feature.desc);

      if (!table.rows.length) {
        return null;
      }

      return table.rows;
    },

    proficienciesDescription() {
      const proficiencies = this.classDetails?.features.find(
        (f) => f.feature_type === 'PROFICIENCIES',
      );

      if (!proficiencies) {
        return null;
      }

      return proficiencies.desc;
    },

    selectedSubclass() {
      return this.subClasses.find(
        (subclass) => subclass.key === this.selectedSubclassKey,
      );
    },

    shouldDisableCopyButton() {
      return (
        !this.includeTitleInCopy &&
        !this.isSelectedCoreTraits &&
        !this.isSelectedHitPoints &&
        !this.isSelectedProficiencies &&
        !this.isSelectedStartingEquipment &&
        !this.selectedClassFeatureOptions.length &&
        this.selectedClassFeatures.length === 0 &&
        this.selectedSubclassFeatures.length === 0
      );
    },

    startingEquipmentDescription() {
      const startingEquipment = this.classDetails?.features.find(
        (f) => f.feature_type === 'STARTING_EQUIPMENT',
      );

      if (!startingEquipment) {
        return null;
      }

      return startingEquipment.desc;
    },

    subclassFeatures() {
      return (
        this.selectedSubclass?.features
          .filter(
            (feature) =>
              feature.feature_type === 'CLASS_LEVEL_FEATURE' &&
              feature.desc !== '[Column data]' &&
              !feature.name.endsWith('Spell List'),
          )
          .map((feature) => {
            const featureCopy = { ...feature };
            featureCopy.rendered_desc = renderMarkdown(
              featureCopy.desc.replaceAll('###', '### '), //TODO - fix markdown rendering so that this hack isn't necessary
            );
            featureCopy.gained_at = featureCopy.gained_at.toSorted(
              (a, b) => a.level - b.level,
            );
            return featureCopy;
          })
          .toSorted(
            (a, b) =>
              (a.gained_at[0]?.level || 0) - (b.gained_at[0]?.level || 0),
          ) || []
      );
    },
  },

  watch: {
    isOpen(open) {
      if (!open) {
        return;
      }

      // If this is an incomplete character class we need to fetch the details.
      if (Object.keys(this.characterClass).length <= 3) {
        this.fetchClassDetails();
        return;
      }

      // If the character class already has details we can just search for subclasses.
      if (Object.keys(this.characterClass).length > 3) {
        this.classDetails = { ...this.characterClass };
        this.fetchSubClasses();
        return;
      }
    },

    selectedSubclassKey(newKey, oldKey) {
      if (newKey === oldKey) {
        return;
      }

      this.selectedSubclassFeatures = [];
    },
  },

  methods: {
    buildCopyableDelta() {
      let delta = new Delta();

      if (this.includeTitleInCopy) {
        delta = deltaAddHeader(delta, this.characterClass.name, 1);

        if (this.characterClass.document) {
          delta = deltaAddItalicizedLine(
            delta,
            this.characterClass.document.name,
          );
        }

        delta.insert('\n');
      }

      if (this.isSelectedCoreTraits) {
        delta = this.buildCoreTraitsTableDelta(delta);
        delta.insert('\n');
      }

      if (this.isSelectedHitPoints) {
        delta = this.buildHitPointsDelta(delta);
        delta.insert('\n');
      }

      if (this.isSelectedProficiencies) {
        delta = this.buildProficienciesDelta(delta);
        delta.insert('\n');
      }

      if (this.isSelectedStartingEquipment) {
        delta = this.buildStartingEquipmentDelta(delta);
        delta.insert('\n');
      }

      this.selectedClassFeatures.forEach((key) => {
        if (!this.selectedClassFeatures.includes(key)) {
          return;
        }

        delta = this.buildClassFeatureDelta(key, delta);
        delta.insert('\n');
      });

      if (this.selectedClassFeatureOptions.length > 0) {
        const selectedOptions = [
          ...this.selectedClassFeatureOptions,
        ].toSorted();

        const lists = new Set();

        for (const option of selectedOptions) {
          const [featureKey] = option.split('|');
          lists.add(featureKey);
        }

        for (const featureKey of lists) {
          delta = this.buildClassFeatureOptionListDelta(featureKey, delta);
          delta.insert('\n');
        }
      }

      if (this.selectedSubclass && this.selectedSubclassFeatures.length) {
        delta = deltaAddHeader(
          delta,
          `Subclass: ${this.selectedSubclass.name}`,
          1,
        );

        delta.insert('\n');

        this.selectedSubclassFeatures.forEach((key) => {
          if (!this.selectedSubclassFeatures.includes(key)) {
            return;
          }

          delta = this.buildSubclassFeatureDelta(key, delta);
          delta.insert('\n');
        });
      }

      return delta;
    },

    // Not implementing HTML and plain text copy for class features at this time.
    // buildCopyableHtml() {
    //   let html = `<h1>${this.characterClass.name}</h1>`;

    //   if (this.characterClass.document) {
    //     html += `<p><em class="text-light-muted-foreground dark:text-dark-muted-foreground">${this.characterClass.document.name}</em></p>`;
    //   }

    //   return html;
    // },

    // buildCopyableText() {
    //   let text = `${this.characterClass.name}\n`;

    //   if (this.characterClass.document) {
    //     text += `${this.characterClass.document.name}\n`;
    //   }

    //   return text;
    // },

    //SECTION Block-level deltas
    buildClassFeatureDelta(featureKey, existingDelta = null) {
      let delta = existingDelta || new Delta();

      const feature = this.classDetails.features.find(
        (f) => f.key === featureKey,
      );

      const featureLevel = feature.gained_at[0]?.level || null;

      const featureName = featureLevel
        ? `Level ${featureLevel}: ${feature.name}`
        : feature.name;

      delta = deltaAddHeader(delta, featureName, 1);
      delta = deltaAddMarkdown(delta, feature.desc);

      return delta;
    },

    buildClassFeatureOptionDelta(featureKey, optionName, existingDelta = null) {
      let delta = existingDelta || new Delta();

      const featureOptionList = this.classFeatureOptions.find(
        (f) => f.key === featureKey,
      );

      if (!featureOptionList) {
        console.error(
          'Could not find feature option list with key:',
          featureKey,
        );
        return delta;
      }

      const option = featureOptionList.options.find(
        (o) => o.name === optionName,
      );

      if (!option) {
        console.error('Could not find option with name:', optionName);
        return delta;
      }

      delta = deltaAddHeader(delta, option.name, 2);
      delta = deltaAddMarkdown(delta, option.desc);

      return delta;
    },

    buildClassFeatureOptionListDelta(featureKey, existingDelta = null) {
      let delta = existingDelta || new Delta();

      const featureOptionList = this.classFeatureOptions.find(
        (f) => f.key === featureKey,
      );

      if (!featureOptionList) {
        console.error(
          'Could not find feature option list with key:',
          featureKey,
        );
        return delta;
      }

      delta = deltaAddHeader(delta, featureOptionList.name, 1);
      delta = deltaAddMarkdown(delta, featureOptionList.intro_text);
      delta.insert('\n');

      const options = featureOptionList.options.filter((option) =>
        this.selectedClassFeatureOptions.includes(
          option._featureKey + '|' + option.name,
        ),
      );

      options.forEach((option) => {
        delta = this.buildClassFeatureOptionDelta(
          featureKey,
          option.name,
          delta,
        );
      });

      return delta;
    },

    buildCoreTraitsTableDelta(existingDelta = null) {
      let delta = existingDelta || new Delta();

      delta = deltaAddHeader(delta, 'Core Traits', 1);

      if (this.coreTraitsTable) {
        this.coreTraitsTable.forEach((trait) => {
          delta = deltaAddProperty(delta, trait[0], trait[1]);
        });
      }

      return delta;
    },

    buildHitPointsDelta(existingDelta = null) {
      let delta = existingDelta || new Delta();

      delta = deltaAddHeader(delta, 'Hit Points', 1);

      if (this.classDetails.hit_points.hit_dice_name) {
        delta = deltaAddProperty(
          delta,
          this.$t('Hit dice'),
          this.classDetails.hit_points.hit_dice_name,
        );
      }

      if (this.classDetails.hit_points.hit_points_at_1st_level) {
        delta = deltaAddProperty(
          delta,
          this.$t('Hit points at 1st level'),
          this.classDetails.hit_points.hit_points_at_1st_level,
        );
      }

      if (this.classDetails.hit_points.hit_points_at_higher_levels) {
        delta = deltaAddProperty(
          delta,
          this.$t('Hit points at higher levels'),
          this.classDetails.hit_points.hit_points_at_higher_levels,
        );
      }

      return delta;
    },

    buildProficienciesDelta(existingDelta = null) {
      let delta = existingDelta || new Delta();

      if (!this.proficienciesDescription) {
        return delta;
      }

      delta = deltaAddHeader(delta, 'Proficiencies', 1);
      delta = deltaAddMarkdown(delta, this.proficienciesDescription);

      return delta;
    },

    buildStartingEquipmentDelta(existingDelta = null) {
      let delta = existingDelta || new Delta();

      if (!this.startingEquipmentDescription) {
        return delta;
      }

      delta = deltaAddHeader(delta, 'Starting Equipment', 1);
      delta = deltaAddMarkdown(delta, this.startingEquipmentDescription);

      return delta;
    },

    buildSubclassFeatureDelta(featureKey, existingDelta = null) {
      let delta = existingDelta || new Delta();

      const feature = this.selectedSubclass.features.find(
        (f) => f.key === featureKey,
      );

      const featureLevel = feature.gained_at[0]?.level || null;

      const featureName = featureLevel
        ? `Level ${featureLevel}: ${feature.name}`
        : feature.name;

      delta = deltaAddHeader(delta, featureName, 2);
      delta = deltaAddMarkdown(delta, feature.desc);

      return delta;
    },
    //!SECTION

    deselectAllForCopy() {
      this.includeTitleInCopy = false;
      this.isSelectedCoreTraits = false;
      this.isSelectedHitPoints = false;
      this.isSelectedProficiencies = false;
      this.isSelectedStartingEquipment = false;
      this.selectedClassFeatures = [];
      this.selectedClassFeatureOptions = [];
      this.selectedSubclassFeatures = [];
    },

    async fetchClassDetails() {
      try {
        this.isFetchingDetails = true;

        const url = new URL(this.endpoint);
        url.searchParams.set('key', this.characterClass.key);

        // go ahead and initiate the fetch for subclasses while we wait for the class details to come back
        this.fetchSubClasses();

        const classResp = await fetch(url);
        const classDetails = await classResp.json();

        if (classDetails.results.length === 0) {
          console.error(
            'No class details found for key:',
            this.characterClass.key,
          );
          return;
        }

        this.classDetails = classDetails.results[0];
      } catch (error) {
        console.error('Error fetching class details:', error);
      } finally {
        this.isFetchingDetails = false;
      }
    },

    async fetchSubClasses() {
      try {
        this.isFetchingSubclasses = true;

        const url = new URL(this.endpoint);
        url.searchParams.set('subclass_of', this.characterClass.key);
        url.searchParams.set('is_subclass', 'true');

        const resp = await fetch(url);
        const data = await resp.json();
        this.subClasses = data.results;

        if (this.subClasses.length === 1) {
          this.selectedSubclassKey = this.subClasses[0].key;
        }
      } catch (error) {
        console.error('Error fetching subclasses:', error);
      } finally {
        this.isFetchingSubclasses = false;
      }
    },

    renderMarkdown,

    selectAllForCopy() {
      this.includeTitleInCopy = true;
      this.isSelectedCoreTraits = true;
      this.isSelectedHitPoints = true;
      this.isSelectedProficiencies = true;
      this.isSelectedStartingEquipment = true;
      this.selectedClassFeatures = this.classFeatures.map(
        (feature) => feature.key,
      );
      this.selectedClassFeatureOptions = this.classFeatureOptions.flatMap(
        (list) =>
          list.options.map((option) => option._featureKey + '|' + option.name),
      );

      if (this.selectedSubclass) {
        this.selectedSubclassFeatures = this.subclassFeatures.map(
          (feature) => feature.key,
        );
      }
    },
  },

  components: {
    CopyContentButton,
    CopyNowButton,
  },
};
</script>
