<template>
    <div class="quill-editor"></div>
</template>

<script>
export default {
    name: 'QuillEditor',
    
    props: ['initialContents', 'readOnly'],

    data() {
        return {
            editor: null,
            contents: null
        };
    },

    mounted() {
        this.editor = new Quill(this.$el, {
            theme: 'bubble'
        });

        if(this.initialContents) {
            this.editor.setContents(this.initialContents);
        }
        
        if(this.readOnly) {
            this.editor.disable();
        }

        if(!this.readOnly) {
            this.editor.on('text-change', () => {
                this.contents = this.editor.getContents();
                this.$emit('quill-text-change', this.contents);
            });
        }

        this.$el.addEventListener('click', event => {
            if(event.target.nodeName === 'A') {
                window.open(event.target.href, '_blank');
            }
        });
        
        if(this.readOnly) {
            window.sheetEvent.$on('quill-refresh', () => {
                this.editor.setContents(this.initialContents);
            });
        }
    }
}
</script>
