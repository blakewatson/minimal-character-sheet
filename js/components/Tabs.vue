<template>
  <nav
    class="dark:bg-dark-background dark:text-dark-foreground bg-light-foreground fixed top-0 left-1/2 z-10 w-full -translate-x-1/2 rounded-b-xs text-neutral-50 sm:max-w-162.5 dark:border-b dark:border-neutral-300"
  >
    <ul class="flex items-center justify-center gap-1 text-[13px] sm:gap-2">
      <li class="absolute top-1/2 left-0 h-full -translate-y-1/2">
        <a
          class="hover:bg-light-accent flex h-full items-center px-2 text-xl text-inherit no-underline hover:text-white"
          href="/dashboard"
        >
          <i class="fa-sharp fa-regular fa-house" role="presentation"></i>
          <span class="sr-only">{{ $t('Dashboard') }}</span>
        </a>
      </li>
      <li
        :class="{
          'bg-light-accent text-white hover:text-white!': view === 'main',
          'hover:text-dark-accent': view !== 'main',
        }"
      >
        <button class="px-2 py-1.5 sm:text-base" @click="updateView('main')">
          {{ $t('Main') }}
        </button>
      </li>
      <li
        :class="{
          'bg-light-accent text-white hover:text-white!': view === 'spells',
          'hover:text-dark-accent': view !== 'spells',
        }"
      >
        <button class="px-2 py-1.5 sm:text-base" @click="updateView('spells')">
          {{ $t('Spells') }}
        </button>
      </li>
      <li
        :class="{
          'bg-light-accent text-white hover:text-white!': view === 'details',
          'hover:text-dark-accent': view !== 'details',
        }"
      >
        <button class="px-2 py-1.5 sm:text-base" @click="updateView('details')">
          {{ $t('Details') }}
        </button>
      </li>

      <li>
        <button
          :title="$t('Search Open5e content')"
          @click="openSearchDialog"
          class="hover:text-dark-accent h-full cursor-pointer py-1.5 text-xl leading-4"
          type="button"
        >
          <span class="sr-only">{{ $t('Search Open5e content') }}</span>
          <i
            class="fa-sharp fa-regular fa-book-sparkles"
            role="presentation"
          ></i>
        </button>
      </li>

      <li
        class="absolute top-1/2 right-0 h-full -translate-y-1/2"
        v-if="!readOnly"
      >
        <button
          :class="saveIndicatorClass"
          :disabled="saveStatus === 'saving'"
          :title="computedSaveButtonTitle"
          @click="manualSave"
          class="h-full cursor-pointer px-1 text-xl disabled:cursor-not-allowed"
        >
          <span v-if="saveStatus === 'unsaved'">
            <i
              class="fa-sharp fa-regular fa-floppy-disk-pen"
              role="presentation"
            ></i>
            <span class="sr-only">{{ $t('Unsaved changes') }}</span>
          </span>
          <span v-else-if="saveStatus === 'saving'">
            <i
              class="fa-sharp fa-regular fa-spinner-third fa-spin"
              role="presentation"
            ></i>
            <span class="sr-only">{{ $t('Saving...') }}</span>
          </span>
          <span v-else-if="saveStatus === 'saved'">
            <i
              class="fa-sharp fa-regular fa-floppy-disk"
              role="presentation"
            ></i>
            <span class="sr-only">{{ $t('All changes saved') }}</span>
          </span>
          <span v-else-if="saveStatus === 'error'">
            <i
              class="fa-sharp fa-regular fa-floppy-disk-circle-xmark"
              role="presentation"
            ></i>
            <span class="sr-only">{{
              $t('Save failed - click to retry')
            }}</span>
          </span>
        </button>

        <div class="retry-bubble" v-if="isRetrying">
          <div class="retry-arrow"></div>
          <div class="retry-text">
            {{ $t('Retry') }} {{ retryCount }}/{{ retryMax }}
          </div>
        </div>
      </li>
      <!-- <li class="delete-character-button" v-if="!readOnly">
        <button @click="deleteCharacter">
          <img src="/images/trash-alt.svg" alt="Delete character" />
        </button>
      </li> -->
    </ul>

    <add-content-dialog ref="addContentDialog"></add-content-dialog>
  </nav>
</template>

<script>
import { mapState } from 'vuex';
import AddContentDialog from './AddContentDialog.vue';

export default {
  name: 'Tabs',

  props: [
    'hasUnsavedChanges',
    'isError',
    'isRetrying',
    'isSaving',
    'retryMax',
    'retryCount',
    'view',
  ],

  data() {
    return {};
  },

  computed: {
    ...mapState(['readOnly']),

    sheetSlug() {
      return this.$store.state.slug;
    },

    saveStatus() {
      if (this.isSaving) {
        return 'saving';
      }

      if (this.isRetrying) {
        return 'saving';
      }

      if (this.isError) {
        return 'error';
      }

      if (this.hasUnsavedChanges) {
        return 'unsaved';
      }

      return 'saved';
    },

    saveIndicatorClass() {
      return {
        'hover:bg-neutral-500': this.saveStatus === 'unsaved',
        '': this.saveStatus === 'saving',
        'hover:bg-green-600 hover:text-white': this.saveStatus === 'saved',
        'text-amber-300 hover:bg-neutral-500 hover:text-white':
          this.saveStatus === 'error',
      };
    },

    computedSaveButtonTitle() {
      if (this.saveStatus === 'unsaved') return this.$t('Unsaved changes');
      if (this.saveStatus === 'saving') return this.$t('Saving...');
      if (this.saveStatus === 'saved') return this.$t('All changes saved');
      if (this.saveStatus === 'error')
        return this.$t('Save failed - click to retry');
    },
  },

  mounted() {
    const backLink = this.$el.querySelector('.back-button a');
    if (backLink) {
      backLink.addEventListener('click', (e) => {
        if (this.saveStatus === 'error' || this.saveStatus === 'unsaved') {
          e.preventDefault();
          const proceed = confirm(this.$t('Unsaved changes warning'));
          if (!proceed) {
            window.location.href = '/dashboard';
          }
        }
      });
    }
  },

  methods: {
    openSearchDialog() {
      this.$refs.addContentDialog.openDialog();
    },

    updateView(view) {
      this.$emit('update-view', view);
    },

    manualSave() {
      this.$emit('manual-save');
    },

    // deleteCharacter() {
    //   var csrf = document.querySelector('#csrf').value;
    //   var areYouSure = confirm(
    //     'Are you sure you want to *permanantly* delete this character sheet?',
    //   );
    //   if (!areYouSure) return;

    //   fetch(`/sheet/${this.sheetSlug}`, {
    //     method: 'delete',
    //     headers: {
    //       'X-Ajax-Csrf': csrf,
    //     },
    //   })
    //     .then((r) => r.json())
    //     .then((r) => {
    //       window.location = '/dashboard';
    //     });
    // },
  },

  watch: {
    isRetrying(newVal) {
      console.log('isRetrying', newVal);
    },
  },

  mounted() {
    // catch super-k shortcut to open the add content dialog
    window.addEventListener('keydown', (e) => {
      if (e.key.toLowerCase() === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        this.openSearchDialog();
      }
    });
  },

  components: {
    'add-content-dialog': AddContentDialog,
  },
};
</script>
