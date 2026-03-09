<template>
  <button
    :title="
      collapsed
        ? expandTitle || $t('Expand content')
        : collapseTitle || $t('Collapse content')
    "
    @click="$emit('click')"
    class="button-icon"
    type="button"
  >
    <i
      :class="
        collapsed
          ? 'fa-sharp fa-regular fa-folder-open'
          : 'fa-sharp fa-regular fa-folder'
      "
      role="presentation"
    ></i>
    <div class="sr-only">
      {{
        collapsed
          ? expandTitle || $t('Expand content')
          : collapseTitle || $t('Collapse content')
      }}
    </div>
  </button>
</template>

<script>
export default {
  name: 'ButtonCollapse',

  props: ['collapsed', 'collapseTitle', 'expandTitle'],
};
</script>
