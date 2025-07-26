<template>
    <div id="sheet" class="sheet">
        
        <tabs :view="view" :save-status="saveStatus" @update-view="view = $event" @manual-save="manualSave"></tabs>

        <bio></bio>

        <div class="page" v-show="view === 'main'">
            <proficiency></proficiency>
            
            <abilities></abilities>
            
            <skills></skills>

            <attacks></attacks>

            <text-section title="Features & Traits" field="featuresText" :read-only="readOnly"></text-section>

            <equipment></equipment>

            <text-section title="Other Proficiencies & Languages" field="proficienciesText" :read-only="readOnly"></text-section>
        </div>

        <div class="page" v-show="view === 'spells'">
            <spells></spells>
        </div>

        <div class="page" v-show="view === 'details'">
            <text-section title="Traits, Ideals, Bonds, & Flaws" field="personalityText" :read-only="readOnly" v-if="!is_2024"></text-section>

            <text-section title="Appearance & Backstory" field="backstoryText" :read-only="readOnly"></text-section>
    
            <text-section title="Treasure" field="treasureText" :read-only="readOnly"></text-section>
    
            <text-section title="Allies & Organizations" field="organizationsText" :read-only="readOnly"></text-section>
        </div>

        <div class="page" v-show="view === 'notes'">
            <text-section title="Notes" field="notesText" :read-only="readOnly"></text-section>
        </div>
    </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import Tabs from './Tabs';
import Bio from './Bio';
import Abilities from './Abilities';
import Skills from './Skills';
import Proficiency from './Proficiency';
import Attacks from './Attacks';
import Equipment from './Equipment';
import Spells from './Spells';
import TextSection from './TextSection';

export default {
    name: 'Sheet',

    data() {
        return {
            view: 'main',
            autosaveTimer: null,
            isPublic: false,
            saveStatus: 'saved', // 'unsaved', 'saving', 'saved', 'error'
            hasPendingChanges: false
        };
    },
    
    computed: {
        ...mapState(['is_2024', 'readOnly'])
    },

    watch: {
        // Watch for view changes and update URL hash
        view(newView) {
            // Update the URL hash without triggering a page reload
            const newUrl = `${window.location.pathname}${window.location.search}#${newView}`;
            window.history.pushState(null, '', newUrl);
        }
    },

	methods: {
		autosaveLoop() {
            if(this.isPublic) {
                return;
            }
            
            // trigger a quick autosave upon every store mutation
            this.$store.subscribe((mutation, state) => {
                this.saveStatus = 'unsaved';
                this.hasPendingChanges = true;
                window.sheetEvent.$emit('autosave', 1);
            });
	
			// when this event fires, schedule a save
			window.sheetEvent.$on('autosave', (seconds = 5) => {
				// convert to milliseconds
				var milliseconds = seconds * 1000;
	
				// reset the timer, if running
				if(this.autosaveTimer !== null) {
					clearTimeout(this.autosaveTimer);
					this.autosaveTimer = null;
				}
	
				// run the autosave after the specified delay
				this.autosaveTimer = setTimeout(() => {
					console.log('save the character sheet');
					this.saveSheetState();
				}, milliseconds);
			});
	
			// go ahead and trigger the first autosave
			window.sheetEvent.$emit('autosave');
		},

		saveSheetState() {
            if(this.isPublic) {
                return;
            }
            
            this.saveStatus = 'saving';
            this.hasPendingChanges = false;
            
			this.$store.dispatch('getJSON').then(jsonState => {
                var sheetSlug = document.querySelector('#sheet-slug').value;
                var csrf = document.querySelector('#csrf').value;
                var formBody = new URLSearchParams();
                
                formBody.set('name', this.$store.state.characterName);
                formBody.set('data', jsonState);
                formBody = formBody.toString();

                fetch(`/sheet/${sheetSlug}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-AJAX-CSRF': csrf
                    },
                    body: formBody
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        document.querySelector('#csrf').value = data.csrf;
                        this.saveStatus = 'saved';
                        return;
                    }

                    if(!data.success && data.reason === 'unauthorized') {
                        window.location.href = window.location.href;
                    }

                    if(!data.success) {
                        this.saveStatus = 'error';
                        this.hasPendingChanges = true;
                    }
                }).catch((reason) => {
                    this.saveStatus = 'error';
                    this.hasPendingChanges = true;
                    if (reason.message === 'unauthorized') {
                        window.location.href = '/login';
                    }
                });
			}).catch((reason) => {
                this.saveStatus = 'error';
                this.hasPendingChanges = true;
                console.error(reason);
            });
		},
        
        refreshLoop() {
            var sheetSlug = document.querySelector('#sheet-slug').value;
            
            fetch(`/sheet-data/${sheetSlug}`)
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    this.$store.dispatch('updateState', { sheet: data.sheet })
                    .catch(reason => console.log(reason));
                }
            })
            .catch(reason => console.error(reason));
        },

        // Get the current view from URL hash
        getViewFromHash() {
            const hash = window.location.hash.substring(1); // Remove the # symbol
            const validViews = ['main', 'spells', 'details', 'notes'];
            return validViews.includes(hash) ? hash : 'main';
        },

        // Handle browser navigation (back/forward buttons)
        handleHashChange() {
            const newView = this.getViewFromHash();
            if (newView !== this.view) {
                this.view = newView;
            }
        },

        manualSave() {
            if(this.isPublic) {
                return;
            }
            
            // Clear any pending autosave timer
            if(this.autosaveTimer !== null) {
                clearTimeout(this.autosaveTimer);
                this.autosaveTimer = null;
            }
            
            this.saveSheetState();
        }
	},

    components: {
        'tabs': Tabs,
        'bio': Bio,
        'abilities': Abilities,
        'skills': Skills,
        'proficiency': Proficiency,
        'attacks': Attacks,
        'equipment': Equipment,
        'spells': Spells,
        'text-section': TextSection
    },

	mounted() {
        const parsedSheet = JSON.parse(sheet);
        if(parsedSheet.is_public && parsedSheet.email === null) {
            this.isPublic = true;
            setInterval(() => this.refreshLoop(), 5000);
        }
        
        if(!this.isPublic) {
    		this.autosaveLoop();
        }

        // Initialize view from URL hash
        this.view = this.getViewFromHash();
        window.addEventListener('hashchange', this.handleHashChange);
    },
    
    created() {
        // initialize state with the "sheet" global
        this.$store.dispatch('initializeState', { sheet: window.sheet })
        .catch(reason => console.log(reason));
    },

    beforeDestroy() {
        window.removeEventListener('hashchange', this.handleHashChange);
    }
}
</script>
