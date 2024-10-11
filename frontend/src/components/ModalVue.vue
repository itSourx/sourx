<template>
  <Teleport to="body">
    <transition enter-active-class="transition ease-out duration-200 transform" enter-from-class="opacity-0"
      enter-to-class="opacity-100" leave-active-class="transition ease-in duration-200 transform"
      leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-show="isOpen" class="modal" @click="handleOutsideClick">
        <div :class="['modal-content', `w-${maxWidth}`]" class="bg-white rounded-lg shadow-lg overflow-hidden">
          <div class="flex justify-between items-center p-4 border-b border-gray-light">
            <h1 class="text-xl text-night">{{ title }}</h1>
            <button @click="closeModal" class="text-gray-600 hover:text-gray-medium transition duration-300">
              <CircleX class="mr-2 w-4 h-4" />
            </button>
          </div>
          <div class="p-4">
            <slot></slot>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
    required: true
  },
  title: {
    type: String,
    default: '',
    required: true
  },
  maxWidth: {
    type: String,
    default: 'md'
  }
})

const emits = defineEmits(['close'])

const closeModal = () => {
  emits('close')
}

const handleOutsideClick = (event) => {
  if (event.target.classList.contains('modal')) {
    closeModal()
  }
}
</script>

<style scoped>
.modal {
  z-index: 9999;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background-color: rgba(0, 0, 0, 0.568);
  overflow-y: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background-color: #fff;
  /* width: 90%; */
  padding: 20px;
  border-radius: 10px;
  overflow-y: scroll;
}

@media screen and (min-width: 768px) {
  .modal-content {
    width: 70%;
  }
}

@media screen and (min-width: 1024px) {
  .modal-content {
    width: 60%;
  }
}
</style>
