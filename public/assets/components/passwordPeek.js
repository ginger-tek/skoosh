export default {
  template: `<div role="group">
    <input @input="$emit('update:modelValue', $event.target.value)" :value="modelValue" :="$attrs" :type="peeked ? 'text' : 'password'">
    <button class="btn btn-primary" type="button" @click="peeked = !peeked" @touchstart.prevent="peeked = !peeked">
      <i :class="['bi', peeked ? 'bi-eye-slash' : 'bi-eye']"></i>
    </button>
  </div>`,
  props: ['modelValue'],
  setup() {
    const peeked = Vue.ref(false);
    return { peeked };
  }
}
