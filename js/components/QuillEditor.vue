<template>
    <div class="quill-editor"></div>
</template>

<script>
export default {
    name: 'QuillEditor',

    data() {
        return {
            editor: null,
            contents: null
        };
    },

    mounted() {
        this.editor = new Quill(this.$el, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ]
            }
        });

        this.editor.on('text-change', () => {
            this.contents = this.editor.getContents();
        });
    }
}
</script>
