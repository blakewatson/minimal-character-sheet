<template>
    <div class="quill-editor"></div>
</template>

<script>
import Quill from 'quill';

export default {
    name: 'QuillEditor',
    
    props: {
        initialContents: {},
        readOnly: {},
        toolbarOptions: {
            type: Array,
            default: () => ['bold', 'italic', 'strike', 'link', { header: 1 }, { header: 2 }, 'blockquote']
        }
    },

    data() {
        return {
            editor: null,
            contents: null
        };
    },

    mounted() {
        this.editor = new Quill(this.$el, {
            theme: 'bubble',
            modules: {
                toolbar: this.toolbarOptions
            }
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
            if(event.target.nodeName === 'A' && !event.target.closest('.ql-tooltip')) {
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
