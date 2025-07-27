<template>
    <nav class="sheet-nav">
        <ul>
            <li class="back-button">
                <a href="/dashboard">
                    <img src="/images/house.svg" alt="Dashboard">
                </a>
            </li>
            <li :class="{ active: view === 'main' }"><button @click="updateView('main')">Main</button></li>
            <li :class="{ active: view === 'spells' }"><button @click="updateView('spells')">Spells</button></li>
            <li :class="{ active: view === 'details' }"><button @click="updateView('details')">Details</button></li>
            <li :class="{ active: view === 'notes' }"><button @click="updateView('notes')">Notes</button></li>
            <li class="save-indicator" v-if="!readOnly">
                <button @click="manualSave" :class="saveIndicatorClass" :disabled="saveStatus === 'saving'" :title="computedSaveButtonTitle">
                    <span v-if="saveStatus === 'unsaved'">
                        <img role="presentation" src="/images/disk-pen.svg" alt="Unsaved changes">
                        <span class="sr-only">Unsaved changes</span>
                    </span>
                    <span v-else-if="saveStatus === 'saving'">
                        <img role="presentation" src="/images/spinner.svg" alt="Saving...">
                        <span class="sr-only">Saving...</span>
                    </span>
                    <span v-else-if="saveStatus === 'saved'">
                        <img role="presentation" src="/images/disk.svg">
                        <span class="sr-only">All changes saved</span>
                    </span>
                    <span v-else-if="saveStatus === 'error'">
                        <img role="presentation" src="/images/disk-error.svg" alt="Save failed - click to retry">
                        <span class="sr-only">Save failed - click to retry</span>
                    </span>
                </button>
                
                <div class="retry-bubble" v-if="isRetrying">
                    <div class="retry-arrow"></div>
                    <div class="retry-text">Retry {{ retryCount }}/{{ maxRetries }}</div>
                </div>
            </li>
            <li class="delete-character-button" v-if="!readOnly">
                <button @click="deleteCharacter">
                    <img src="/images/trash-alt.svg" alt="Delete character">
                </button>
            </li>
        </ul>
    </nav>
</template>

<script>
import { mapState } from 'vuex';

export default {
    name: 'Tabs',

    props: ['view', 'saveStatus', 'isRetrying', 'retryCount', 'maxRetries'],

    computed: {
        ...mapState(['readOnly']),
        
        sheetSlug() {
            return this.$store.state.slug;
        },
        
        saveIndicatorClass() {
            return {
                'save-unsaved': this.saveStatus === 'unsaved',
                'save-saving': this.saveStatus === 'saving',
                'save-saved': this.saveStatus === 'saved',
                'save-error': this.saveStatus === 'error'
            };
        },

        computedSaveButtonTitle() {
            if(this.saveStatus === 'unsaved') return 'Unsaved changes';
            if(this.saveStatus === 'saving') return 'Saving...';
            if(this.saveStatus === 'saved') return 'All changes saved';
            if(this.saveStatus === 'error') return 'Save failed - click to retry';
        }
    },

    mounted() {
        const backLink = this.$el.querySelector('.back-button a');
        if (backLink) {
            backLink.addEventListener('click', (e) => {
                if (this.saveStatus === 'error' || this.saveStatus === 'unsaved') {
                    e.preventDefault();
                    const proceed = confirm('You have unsaved changes. Click OK to stay and try saving by clicking the save indicator, or Cancel to proceed to the dashboard.');
                    if (!proceed) {
                        window.location.href = '/dashboard';
                    }
                }
            });
        }
    },

    methods: {
        updateView(view) {
            this.$emit('update-view', view);
        },
        
        manualSave() {
            this.$emit('manual-save');
        },

        deleteCharacter() {
            var csrf = document.querySelector('#csrf').value;
            var areYouSure = confirm('Are you sure you want to *permanantly* delete this character sheet?');
            if(!areYouSure) return;

            fetch(`/sheet/${this.sheetSlug}`, {
                method: 'delete',
                headers: {
                    'X-Ajax-Csrf': csrf
                }
            })
            .then(r => r.json())
            .then(r => {
                window.location = '/dashboard';
            });
        }
    }
}
</script>
