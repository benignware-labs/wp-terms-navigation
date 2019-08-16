<div class="terms-menu nav nav-pills flex-column">
<?php foreach ($terms as $index => $term): ?>
  <div class="d-flex align-items-center">
    <span data-toggle="collapse" data-target="#terms-collapse-<?= $term['slug']; ?>"  class="ml-1 mr-1 <?= count($term['children']) > 0 ? '' : 'invisible'; ?> <?= ($term['active'] || $term['active_parent']) ? '' : 'collapsed'; ?>">
      <i class="fas fa-angle-right"></i>
    </span>
    <a data-term-id="<?= $term['term_id']; ?>" class="terms-link nav-link <?= $term['active'] ? 'active' : ''; ?>" href="<?= $term['link']; ?>">
      <?= $term['name']; ?>
    </a>
  </div>
  <div id="terms-collapse-<?= $term['slug']; ?>" class="ml-3 collapse <?= ($term['active'] || $term['active_parent']) ? 'in show' : ''; ?>">
    <?php if (count($term['children']) > 0): ?>
      <?= get_terms_menu($term['children'], $options); ?>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
