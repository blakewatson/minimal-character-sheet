<template>
    <nav>
        <ul>
            <li :class="{ active: view === 'main' }"><button @click="updateView('main')">Main</button></li>
            <li :class="{ active: view === 'spells' }"><button @click="updateView('spells')">Spells</button></li>
            <li :class="{ active: view === 'details' }"><button @click="updateView('details')">Details</button></li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: 'Tabs',

    props: ['view'],

    methods: {
        updateView(view) {
            this.$emit('update-view', view);
        }
    }
}
</script>
