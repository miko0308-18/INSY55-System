// ==========================================================================
// Prism — syntax highlighting for the element showcase pages
// ==========================================================================
// The Prism library lives in js/vendor/prism.js and its theme in
// css/prism-tomorrow.css (linked from the elements*.html pages).
// Prism does not auto-highlight here, so call highlightAll() explicitly.
import Prism from '../vendor/prism.js';

/** Highlight every `<pre><code class="language-*">` block currently in the DOM. */
export function highlightAll(root = document) {
  Prism.highlightAllUnder(root);
}

export default Prism;
