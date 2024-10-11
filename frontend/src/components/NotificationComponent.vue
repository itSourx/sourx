<template>
  <div class="attention-animation p-4 my-8 bg-oxford-blue text-yellow rounded">
    <slot>⚠️ Vous avez des demandes en attente de validation !</slot>
  </div>
</template>

<script setup></script>

<style scoped>
@keyframes attention-blink {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.5;
  }
}

.attention-animation {
  @apply animate-[attention-blink_1s_infinite];
}
</style>
