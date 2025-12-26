<template>
  <dialog
    class="bg-light-background dark:bg-dark-background fixed top-1/2 left-1/2 h-[calc(100%-0.5rem)] max-h-none w-160 max-w-[calc(100%-0.5rem)] -translate-1/2 rounded border border-neutral-300 shadow-lg sm:h-[calc(100%-2rem)] sm:w-162.5 sm:max-w-none dark:border-neutral-600 dark:backdrop:bg-[rgba(0,0,0,0.5)]"
    ref="addContentDialog"
  >
    <div class="absolute top-2 right-2 z-10">
      <button
        @click="closeDialog"
        class="button-icon bg-reverse! text-reverse!"
        title="Close dialog"
        type="button"
      >
        <i class="fa-sharp fa-regular fa-xmark h-full" role="presentation"></i>
        <div class="sr-only">Close dialog</div>
      </button>
    </div>

    <form @submit.prevent="handleSearch" class="h-full overflow-auto p-4">
      <p>
        <strong>Search for content</strong>
      </p>

      <p>
        Search to find spells, backgrounds, and more (coming soon) from the
        Open5e API.
      </p>

      <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-end">
        <div>
          <label class="block" for="endpoints">Category</label>

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
          <label class="block" for="spell-search">Search</label>
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

        <button
          :disabled="isSearching || !isSupportedEndpoint"
          class="button-primary gap-1"
          style="flex-shrink: 0"
          type="submit"
        >
          <i class="fa-sharp fa-magnifying-glass" v-show="!isSearching"></i>
          <i class="fa-sharp fa-spinner-third fa-spin" v-show="isSearching"></i>
          Search
        </button>
      </div>

      <p class="my-2" v-if="!isSupportedEndpoint">
        Support for {{ capitalize(selectedEndpoint) }} coming soon!
      </p>

      <div
        class="content-results mt-2"
        v-if="searchResults && searchResults.results.length > 0"
      >
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

          <spell-details
            :spell="result"
            @close="closeDialog"
            v-if="selectedEndpoint === 'spells'"
          ></spell-details>
        </div>
      </div>

      <p class="mt-2" v-else-if="noResultsFound">No results found.</p>

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
          Previous
        </button>
        <button
          :disabled="!urlNext || isSearching"
          @click="fetchPage(urlNext)"
          class="button gap-1"
          type="button"
        >
          Next
          <i class="fa-sharp fa-chevron-right"></i>
        </button>
      </div>

      <div class="mt-8" style="border-top: 1px solid var(--divider-color)">
        <button
          type="button"
          @click="closeDialog"
          class="button button-secondary my-none"
        >
          Close
        </button>
      </div>
    </form>
  </dialog>
</template>

<script>
import Field from './Field.vue';
import BackgroundDetails from './SearchResults/BackgroundDetails.vue';
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
      isSearching: false,
      noResultsFound: false,
      searchQuery: '',
      searchResults: null,
      selectedEndpoint: 'spells',
      supportedEndpoints: ['backgrounds', 'spells'],
    };
  },

  computed: {
    endpointLabels() {
      const labels = {};

      this.sortedEndpoints.forEach((key) => {
        if (key === 'magicitems') {
          labels[key] = 'Magic items';
          return;
        }

        labels[key] = this.capitalize(key);
      });

      return labels;
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

  methods: {
    openDialog() {
      this.$refs.addContentDialog.showModal();
    },

    closeDialog() {
      this.$refs.addContentDialog.close();
      this.searchQuery = '';
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
      if (!this.searchQuery.trim()) {
        return;
      }

      let url = this.endpoints[this.selectedEndpoint];

      try {
        this.isSearching = true;
        const resp = await fetch(
          `${url}/?name__icontains=${encodeURIComponent(this.searchQuery)}`,
        );
        const data = await resp.json();

        if (data.results.length === 0) {
          console.log('no results');
          this.noResultsFound = true;
        } else {
          this.noResultsFound = false;
        }

        this.searchResults = data;
      } catch (error) {
        console.error('Error fetching search results:', error);
      }

      this.isSearching = false;
    },

    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
  },

  mounted() {},

  components: {
    'background-details': BackgroundDetails,
    field: Field,
    'spell-details': SpellDetails,
  },
};
</script>
