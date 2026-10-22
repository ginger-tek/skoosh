export default {
  template: `<div class="align-center text-center">
    <i class="bi bi-question-circle" style="font-size:5rem"></i>
    <h1>Hmm...</h1>
    <h4><slot>That page doesn't exist</slot></h4>
    <router-link :to="backLink" role="button" class="btn btn-primary">
      <i class="bi bi-arrow-left-circle"></i> Back
    </router-link>
  </div>`,
  props: {
    backLink: {
      type: String,
      default: '/',
    },
  },
};