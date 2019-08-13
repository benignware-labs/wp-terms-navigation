<ul class="terms">
<?php foreach ($terms as $index => $term): ?>
  <li
    class="terms-item <?= $term['active'] ? 'is-active' : ''; ?> <?= count($term['children']) > 0 ? 'has-children' : ''; ?>"
  >
    <i data-toggle="terms" class="terms-caret"></i>
    <a href="<?= $term['link']; ?>">
      <?= $term['name']; ?>
    </a>
    <?php if (count($term['children']) > 0): ?>
      <?= get_terms_menu($term['children'], $template, $format); ?>
    <?php endif; ?>
  </li>
<?php endforeach; ?>
</ul>
