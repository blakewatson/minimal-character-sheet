<template>
    <div class="quill-editor"></div>
</template>

<script>
export default {
    name: 'QuillEditor',
    
    props: ['initialContents'],

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

        if(this.initialContents) {
            this.editor.setContents(this.initialContents);
        }

        this.editor.on('text-change', () => {
            this.contents = this.editor.getContents();
            this.$emit('quill-text-change', this.contents);
        });
    }
}
</script>
