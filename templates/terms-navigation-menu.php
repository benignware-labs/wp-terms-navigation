<ul class="terms-menu">
<?php foreach ($terms as $index => $term): ?>
  <li
    class="terms-item <?= ($term['active'] || $term['active_parent']) ? 'is-open' : ''; ?> <?= $term['active'] ? 'is-active' : ''; ?> <?= count($term['children']) > 0 ? 'has-children' : ''; ?>"
  >
    <i data-toggle="terms" data-target="#terms-collapse-<?= $term['term_id']; ?>" class="terms-caret"></i>
    <a class="terms-link" href="<?= $term['link']; ?>">
      <?= $term['name']; ?>
    </a>
    <div id="terms-collapse-<?= $term['term_id']; ?>" class="terms-collapse">
      <?php if (count($term['children']) > 0): ?>
        <?= get_terms_menu($term['children'], $template, $format); ?>
      <?php endif; ?>
    </div>
  </li>
<?php endforeach; ?>
</ul>
