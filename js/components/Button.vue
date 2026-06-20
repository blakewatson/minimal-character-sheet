<template>
  <button
    :class="{
      'button-primary': primary,
      button: !primary,
      relative:
        Boolean(tooltip) &&
        ($attrs.class.every?.((cls) => cls !== 'absolute') || true),
    }"
    class="group"
    v-bind="$attrs"
  >
    <slot></slot>

    <span
      :class="{
        // color
        'text-light-background bg-light-foreground dark:text-dark-background dark:bg-dark-foreground':
          tooltipType === 'default',
        'text-light-background dark:text-dark-background bg-red-600 dark:bg-red-700':
          tooltipType === 'danger',
        'text-light-background dark:text-dark-background bg-green-600 dark:bg-green-700':
          tooltipType === 'success',
        // placement
        'placement-top bottom-[calc(100%+0.5rem)]': tooltipPlacement === 'top',
        'placement-bottom top-[calc(100%+0.5rem)]':
          tooltipPlacement === 'bottom',
        'placement-left right-[calc(100%+0.5rem)]': tooltipPlacement === 'left',
        'placement-right left-[calc(100%+0.5rem)]':
          tooltipPlacement === 'right',
        // alignment
        'align-center': !tooltipAlign || tooltipAlign === 'center',
        'align-start left-0':
          (tooltipPlacement === 'top' || tooltipPlacement === 'bottom') &&
          tooltipAlign === 'start',
        'align-end right-0':
          (tooltipPlacement === 'top' || tooltipPlacement === 'bottom') &&
          tooltipAlign === 'end',
        'align-start top-0':
          (tooltipPlacement === 'left' || tooltipPlacement === 'right') &&
          tooltipAlign === 'start',
        'align-end bottom-0':
          (tooltipPlacement === 'left' || tooltipPlacement === 'right') &&
          tooltipAlign === 'end',
      }"
      class="tooltip pointer-events-none absolute rounded-xs px-1 py-0.5 text-xs leading-tight text-nowrap opacity-0 shadow transition-opacity delay-300 duration-200 group-hover:opacity-100"
      v-if="tooltip"
      >{{ tooltip }}</span
    >
  </button>
</template>

<script>
export default {
  name: 'Button',

  props: {
    tooltipAlign: {
      type: String,
      default: 'center', // start, center, end
    },
    tooltipPlacement: {
      type: String,
      default: 'top', // top, bottom, left, right
    },
    tooltipType: {
      type: String,
      default: 'default', // default, danger, success
    },
    primary: {
      type: Boolean,
      default: false,
    },
    tooltip: {
      type: String,
      default: '',
    },
  },

  data() {
    return {
      isTooltipVisible: false,
    };
  },
};
</script>

<style scoped>
.tooltip {
  /* triangle */
  &:before {
    content: '';
    position: absolute;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0.5rem;
    border-color: transparent;
  }

  &.placement-top {
    &:before {
      top: 100%;
      border-bottom-color: transparent;
      border-top-color: light-dark(
        var(--color-light-foreground),
        var(--color-dark-foreground)
      );
    }
  }

  &.placement-bottom {
    &:before {
      bottom: 100%;
      border-top-color: transparent;
      border-bottom-color: light-dark(
        var(--color-light-foreground),
        var(--color-dark-foreground)
      );
    }
  }

  &.placement-top,
  &.placement-bottom {
    &.align-center {
      &:before {
        left: 50%;
        transform: translateX(-50%);
      }
    }

    &.align-start {
      &:before {
        left: 0.25rem;
        transform: none;
      }
    }
    &.align-end {
      &:before {
        right: 0.25rem;
        transform: none;
      }
    }
  }

  &.placement-left {
    &:before {
      left: 100%;
      border-left-color: light-dark(
        var(--color-light-foreground),
        var(--color-dark-foreground)
      );
      border-right-color: transparent;
    }
  }

  &.placement-right {
    &:before {
      right: 100%;
      border-right-color: light-dark(
        var(--color-light-foreground),
        var(--color-dark-foreground)
      );
      border-left-color: transparent;
    }
  }

  &.placement-left,
  &.placement-right {
    &:before {
      top: 50%;
      transform: translateY(-50%);
    }
    /* &.align-center {
    }

    &.align-start {
      &:before {
        top: 0.25rem;
        transform: none;
      }
    }
    &.align-end {
      &:before {
        bottom: 0.25rem;
        transform: none;
      }
    } */
  }
}
</style>
