<div class="terms-levels clearfix bg-light">
  <?php
    $has_active_sub_levels = count($levels) > 0 ? count(array_filter(array_slice($levels, 1), function($item) {
      return $item['active'];
    })) > 0 : false;
  ?>
  <?php foreach ($levels as $level => $item): ?>
    <?php if ($level == 1): ?>
      <div id="terms-collapse" class="collapse <?= $has_active_sub_levels ? 'in show' : ''; ?>">
    <?php endif; ?>
    <h5 class="px-3 py-4">Level <?= $level; ?></h5>
    <ul class="terms-menu nav">
      <?php foreach ($item['terms'] as $term): ?>
        <li
          class="terms-item nav-item <?= $term['hidden'] ? 'd-none' : ''; ?> <?= $term['active'] ? 'is-active' : ''; ?>"
        >
          <a class="terms-link nav-link" href="<?= $term['link']; ?>">
            <?= $term['name']; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if ($level > 1 && $level == count($levels) - 1): ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>
  <a data-toggle="collapse" data-target="#terms-collapse" class="px-3 py-4 <?= $has_active_sub_levels ? '' : 'collapsed'; ?> float-right">
    <i class="fas fa-angle-up"></i>
  </a>
</div>
