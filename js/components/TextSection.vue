<template>
    <section>
        <p class="label centered">{{ title }}</p>
        <quill-editor></quill-editor>
    </section>
</template>

<script>
import QuillEditor from './QuillEditor';

export default {
    name: 'TextSection',

    props: ['title'],

    components: {
        'quill-editor': QuillEditor
    }
}
</script>
