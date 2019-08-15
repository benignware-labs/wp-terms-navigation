<div class="terms-levels">
  <?php if ($level > 0): ?>
    <i data-toggle="terms" data-target="#terms-menu-<?= $level; ?>" class="terms-caret"></i>
  <?php endif; ?>
  <span><b>Level <?= $level; ?></b></span>
  <ul id="terms-menu-<?= $level; ?>" class="terms-menu <?= $level > 0 ? 'terms-collapse' : ''; ?>">
  <?php foreach ($current as $index => $term): ?>
    <li
      class="terms-item <?= $term['active'] ? 'is-active' : ''; ?> <?= count($term['children']) > 0 ? 'has-children' : ''; ?>"
    >
      <a class="terms-link" href="<?= $term['link']; ?>">
        <?= $term['name']; ?>
      </a>
    </li>
  <?php endforeach; ?>
  </ul>
  <?php if (isset($levels[$level + 1])): ?>
    <?= get_terms_menu($terms, $template, $format, $level + 1); ?>
  <?php endif; ?>
</div>
