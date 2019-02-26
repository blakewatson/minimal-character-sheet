<template>
    <nav>
        <ul>
            <li><button @click="updateView('main')">Main</button></li>
            <li><button @click="updateView('spells')">Spells</button></li>
            <li><button @click="updateView('details')">Details</button></li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: 'Tabs',

    methods: {
        updateView(view) {
            this.$emit('update-view', view);
        }
    }
}
</script>
