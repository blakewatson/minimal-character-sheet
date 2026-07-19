<template>
  <dialog
    @toggle="isOpen = $event.target.open"
    class="bg-light-background dark:bg-dark-background fixed top-1/2 left-1/2 h-[calc(100%-0.5rem)] max-h-none w-160 max-w-[calc(100%-0.5rem)] -translate-1/2 rounded border border-neutral-300 shadow-lg sm:h-[calc(100%-2rem)] sm:w-162.5 sm:max-w-none dark:border-neutral-600 dark:backdrop:bg-[rgba(0,0,0,0.5)]"
    ref="addContentDialog"
  >
    <div class="absolute top-2 right-2 z-10">
      <button
        @click="closeDialog"
        class="button-icon bg-reverse! text-reverse!"
        :title="$t('Close dialog')"
        type="button"
      >
        <i class="fa-sharp fa-regular fa-xmark h-full" role="presentation"></i>
        <div class="sr-only">{{ $t('Close dialog') }}</div>
      </button>
    </div>

    <div class="h-full overflow-auto p-4" ref="content_form">
      <form @submit.prevent="handleSearch">
        <p>
          <strong>{{ $t('Search for content') }}</strong>
        </p>

        <p>
          {{ $t('Search description') }}
        </p>

        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">
          <div>
            <label class="block" for="endpoints">{{ $t('Category') }}</label>

            <select
              id="endpoints"
              v-model="selectedEndpoint"
              class="text-light-foreground hover:text-light-accent focus:text-light-accent dark:text-dark-foreground dark:hover:text-dark-accent dark:focus:text-dark-accent outline-light-accent dark:outline-dark-accent border-light-muted-foreground dark:border-dark-muted-foreground h-[32.5px] w-full max-w-full rounded-xs border px-2 py-1 text-[16px] hover:bg-neutral-100 focus:bg-neutral-100 focus:outline-2 sm:text-[15px] dark:bg-black dark:hover:bg-black dark:focus:bg-black"
            >
              <option :value="endpoint" v-for="endpoint in supportedEndpoints">
                {{ endpointLabels[endpoint] }}
              </option>
            </select>
          </div>

          <div class="grow">
            <label class="block" for="spell-search">{{ $t('Search') }}</label>
            <field
              :auto-size="false"
              :value="searchQuery"
              @update-value="searchQuery = $event"
              autofocus
              class="border-light-muted-foreground! dark:border-dark-muted-foreground! w-full border! bg-neutral-100 px-2! py-1! dark:bg-black"
              id="spell-search"
              type="text"
            ></field>
          </div>

          <app-button
            :disabled="
              isSearching ||
              !isSupportedEndpoint ||
              !includeGameSystemKeys.length
            "
            class="gap-1"
            primary
            style="flex-shrink: 0"
            type="submit"
          >
            <i class="fa-sharp fa-magnifying-glass" v-show="!isSearching"></i>
            <i
              class="fa-sharp fa-spinner-third fa-spin"
              v-show="isSearching"
            ></i>
            {{ $t('Search') }}
          </app-button>
        </div>

        <p class="my-2" v-if="!isSupportedEndpoint">
          {{
            $t('Support coming soon', {
              category: endpointLabels[selectedEndpoint],
            })
          }}
        </p>

        <div class="mt-2 flex items-center gap-4 text-sm">
          <label>
            <input type="checkbox" v-model="include2014" />
            {{ $t('5e 2014') }}
          </label>
          <label>
            <input type="checkbox" v-model="include2024" />
            {{ $t('5e 2024') }}
          </label>
          <label>
            <input type="checkbox" v-model="includeAdvanced5e" />
            {{ $t('Advanced 5e') }}
          </label>
        </div>
      </form>

      <app-button
        :disabled="isSearching || !includeGameSystemKeys.length"
        @click="listAllClasses"
        class="mt-2 gap-1 text-sm"
        type="button"
        v-if="selectedEndpoint === 'classes'"
      >
        <i class="fa-sharp fa-axe-battle" v-show="!isSearching"></i>
        <i class="fa-sharp fa-spinner-third fa-spin" v-show="isSearching"></i>
        {{ $t('List all base classes') }}
      </app-button>

      <div
        class="content-results mt-2"
        v-if="searchResults && searchResults.results.length > 0"
      >
        <p>
          <small>{{ searchResults.count }} {{ $t('result(s)') }}</small>
        </p>
        <div
          :key="result.key"
          class="*:border-light-muted-foreground *:dark:border-dark-muted-foreground *:border-t *:py-3"
          v-for="result in searchResults.results"
        >
          <background-details
            :background="result"
            @close="closeDialog"
            v-if="selectedEndpoint === 'backgrounds'"
          ></background-details>

          <class-details
            :character-class="result"
            :endpoint="endpoints['classes']"
            @close="closeDialog"
            v-if="selectedEndpoint === 'classes'"
          ></class-details>

          <feat-details
            :feat="result"
            @close="closeDialog"
            v-if="selectedEndpoint === 'feats'"
          ></feat-details>

          <spell-details
            :spell="result"
            @close="closeDialog"
            v-if="selectedEndpoint === 'spells'"
          ></spell-details>
        </div>
      </div>

      <p class="mt-2" v-else-if="noResultsFound">
        {{ $t('No results found.') }}
      </p>

      <div
        class="my-4 flex items-center justify-center gap-4"
        v-if="urlPrev || urlNext"
      >
        <button
          :disabled="!urlPrev || isSearching"
          @click="fetchPage(urlPrev)"
          class="button gap-1"
          type="button"
        >
          <i class="fa-sharp fa-chevron-left"></i>
          {{ $t('Previous') }}
        </button>
        <button
          :disabled="!urlNext || isSearching"
          @click="fetchPage(urlNext)"
          class="button gap-1"
          type="button"
        >
          {{ $t('Next') }}
          <i class="fa-sharp fa-chevron-right"></i>
        </button>
      </div>

      <div class="mt-8" style="border-top: 1px solid var(--divider-color)">
        <button
          @click="closeDialog"
          class="button button-secondary my-none"
          type="button"
        >
          {{ $t('Close') }}
        </button>
      </div>
    </div>
  </dialog>
</template>

<script>
import { state } from '../store.js';
import Button from './Button.vue';
import Field from './Field.vue';
import BackgroundDetails from './SearchResults/BackgroundDetails.vue';
import ClassDetails from './SearchResults/ClassDetails.vue';
import FeatDetails from './SearchResults/FeatDetails.vue';
import SpellDetails from './SearchResults/SpellDetails.vue';

export default {
  name: 'AddContentDialog',

  data() {
    return {
      endpoints: {
        items: 'https://api.open5e.com/v2/items',
        magicitems: 'https://api.open5e.com/v2/magicitems',
        weapons: 'https://api.open5e.com/v2/weapons',
        armor: 'https://api.open5e.com/v2/armor',
        backgrounds: 'https://api.open5e.com/v2/backgrounds',
        feats: 'https://api.open5e.com/v2/feats',
        species: 'https://api.open5e.com/v2/species',
        creatures: 'https://api.open5e.com/v2/creatures',
        conditions: 'https://api.open5e.com/v2/conditions',
        spells: 'https://api.open5e.com/v2/spells',
        classes: 'https://api.open5e.com/v2/classes',
        environments: 'https://api.open5e.com/v2/environments',
        abilities: 'https://api.open5e.com/v2/abilities',
        skills: 'https://api.open5e.com/v2/skills',
        services: 'https://api.open5e.com/v2/services',
      },
      gameSystemKeys: ['5e-2014', '5e-2024', 'a5e'],
      include2014: false,
      include2024: false,
      includeAdvanced5e: false,
      isOpen: false,
      isSearching: false,
      lastScrollPosition: 0,
      noResultsFound: false,
      scrollPosition: 0,
      searchQuery: 'warlock',
      searchResults: null,
      selectedEndpoint: 'classes',
      supportedEndpoints: ['backgrounds', 'classes', 'feats', 'spells'],
    };
  },

  computed: {
    endpointLabels() {
      const labelMap = {
        abilities: this.$t('Abilities'),
        armor: this.$t('Armor'),
        backgrounds: this.$t('Backgrounds'),
        classes: this.$t('Classes'),
        conditions: this.$t('Conditions'),
        creatures: this.$t('Creatures'),
        environments: this.$t('Environments'),
        feats: this.$t('Feats'),
        items: this.$t('Items'),
        magicitems: this.$t('Magic items'),
        services: this.$t('Services'),
        skills: this.$t('Skills'),
        species: this.$t('Species'),
        spells: this.$t('Spells'),
        weapons: this.$t('Weapons'),
      };

      const labels = {};

      this.sortedEndpoints.forEach((key) => {
        labels[key] = labelMap[key] || this.capitalize(key);
      });

      return labels;
    },

    includeGameSystemKeys() {
      const keys = [];

      if (this.include2014) {
        keys.push('5e-2014');
      }

      if (this.include2024) {
        keys.push('5e-2024');
      }

      if (this.includeAdvanced5e) {
        keys.push('a5e');
      }

      return keys;
    },

    is2024() {
      return state.is_2024;
    },

    isSupportedEndpoint() {
      return this.supportedEndpoints.includes(this.selectedEndpoint);
    },

    sortedEndpoints() {
      return Object.keys(this.endpoints).sort();
    },

    urlPrev() {
      return this.searchResults?.previous;
    },

    urlNext() {
      return this.searchResults?.next;
    },
  },

  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.restoreScrollPosition();
        return;
      }
      this.lastScrollPosition = this.scrollPosition;
    },

    selectedEndpoint(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.searchResults = null;
        this.noResultsFound = false;
      }
    },
  },

  methods: {
    openDialog() {
      this.$refs.addContentDialog.showModal();
    },

    closeDialog() {
      this.$refs.addContentDialog.close();
    },

    async fetchPage(url) {
      if (!url) {
        return;
      }

      try {
        this.isSearching = true;
        const resp = await fetch(url);
        const data = await resp.json();
        this.searchResults = data;
        this.$nextTick(() => {
          this.$el.querySelector('form').scrollTop = 0;
        });
      } catch (error) {
        console.error('Error fetching search results:', error);
      }

      this.isSearching = false;
    },

    async handleSearch() {
      if (!this.searchQuery.trim() || !this.includeGameSystemKeys.length) {
        return;
      }

      // For some reason the classes import doesn't take name__icontains
      const nameContainsParam =
        this.selectedEndpoint === 'classes'
          ? 'name__contains'
          : 'name__icontains';

      const url = new URL(this.endpoints[this.selectedEndpoint]);
      url.searchParams.set(nameContainsParam, this.searchQuery.trim());
      url.searchParams.set(
        'document__gamesystem__key__in',
        this.includeGameSystemKeys.join(','),
      );
      url.searchParams.set('ordering', 'name');

      try {
        this.isSearching = true;

        const resp = await fetch(url);
        const data = await resp.json();

        if (data.results.length === 0) {
          this.noResultsFound = true;
        } else {
          this.noResultsFound = false;
        }

        this.searchResults = data;
      } catch (error) {
        console.error('Error fetching search results:', error);
      } finally {
        this.isSearching = false;
      }
    },

    async listAllClasses() {
      if (!this.includeGameSystemKeys.length) {
        return;
      }

      const url = new URL(this.endpoints['classes']);
      // only base classes
      url.searchParams.set('is_subclass', 'false');
      // only name and document fields for now
      url.searchParams.set('fields', 'key,name,document');
      // only from the game systems the user selected
      url.searchParams.set(
        'document__gamesystem__key__in',
        this.includeGameSystemKeys.join(','),
      );
      url.searchParams.set('ordering', 'name');

      try {
        this.isSearching = true;
        const resp = await fetch(url);
        const data = await resp.json();

        if (data.results.length === 0) {
          this.noResultsFound = true;
        } else {
          this.noResultsFound = false;
        }

        this.searchResults = data;
      } catch (error) {
        console.error('Error fetching classes:', error);
      } finally {
        this.isSearching = false;
      }
    },

    saveScrollPosition() {
      this.scrollPosition = this.$refs.content_form.scrollTop;
    },

    async restoreScrollPosition() {
      await this.$nextTick();

      // Trying to restore the scroll position right off the bat.
      this.$refs.content_form.scrollTop = this.lastScrollPosition;

      // If the focused search input triggers a scroll ignore that and restore the scroll position.
      this.$refs.content_form.addEventListener(
        'scroll',
        () => {
          this.$refs.content_form.scrollTop = this.lastScrollPosition;
        },
        { once: true },
      );
    },

    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
  },

  created() {
    this.include2014 = !state.is_2024;
    this.include2024 = state.is_2024;
  },

  mounted() {
    this.$refs.content_form.addEventListener('scroll', this.saveScrollPosition);
  },

  beforeUnmount() {
    this.$refs.content_form.removeEventListener(
      'scroll',
      this.saveScrollPosition,
    );
  },

  components: {
    'app-button': Button,
    'background-details': BackgroundDetails,
    'class-details': ClassDetails,
    'feat-details': FeatDetails,
    field: Field,
    'spell-details': SpellDetails,
  },
};
</script>
